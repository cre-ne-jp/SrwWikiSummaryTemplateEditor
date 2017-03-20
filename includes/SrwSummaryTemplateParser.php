<?php

/**
 * @brief 概要テンプレートの解析器。
 */
class SrwSummaryTemplateParser {
  /** @brief テンプレートのソース。 */
  private $source;

  /**
   * @brief コンストラクタ。
   * @param string $source テンプレートのソース。
   */
  public function __construct($source) {
    $this->source = $source;
  }

  /**
   * @brief テンプレートのソースを解析し、テンプレートのインスタンスに変換する。
   * @return SrwSummaryTemplate|null テンプレートのインスタンス。変換に失敗した場合はnull。
   */
  public function parse() {
    if (!$this->source) {
      return null;
    }

    $parser = new Parser(['preprocessorClass' => 'Preprocessor_DOM']);
    $parser->mOptions = new ParserOptions();

    try {
      $dom = $parser->preprocessToDom($this->source, Parser::PTD_FOR_INCLUSION);
      $doc = $dom->node->ownerDocument;
      $xpath = new DOMXPath($doc);

      $summaryTemplateNode = $this->extractSummaryTemplateNode($xpath);
      if ($summaryTemplateNode === null) {
        return null;
      }

      $params = $this->extractTemplateParams($summaryTemplateNode, $xpath);
      $name = $params['name'];
      if (!$name) {
        return null;
      }

      $template = new SrwSummaryTemplate($name);
      $this->addItemsTo($template, $params);

      return $template;
    } catch (Exception $e) {
      return null;
    }
  }

  /**
   * @brief テンプレートのノードを抽出する。
   * @param DOMXPath $xpath XPathオブジェクト。
   * @return DOMNode|null テンプレートのノード。見つからなかった場合はnull。
   */
  private function extractSummaryTemplateNode(DOMXPath $xpath) {
    $templateNodes = $xpath->query('template');
    foreach ($templateNodes as $templateNode) {
      $titleNode = $xpath->query('title', $templateNode)->item(0);
      if ($titleNode !== null) {
        if (trim($titleNode->textContent) === 'Infobox') {
          return $templateNode;
        }
      }
    }

    return null;
  }

  /**
   * @brief テンプレートのパラメータを抽出する。
   * @param DOMNode $templateNode テンプレートのノード。
   * @param DOMXPath $xpath XPathオブジェクト。
   * @return array テンプレートのパラメータの連想配列。
   */
  private function extractTemplateParams(DOMNode $templateNode, DOMXPath $xpath) {
    $params = [];

    $partNodes = $xpath->query('part', $templateNode);
    foreach ($partNodes as $node) {
      list($key, $value) = explode('=', $node->textContent, 2);
      $trimmedKey = trim($key);

      if ($trimmedKey && $value !== null) {
        $params[strtolower($trimmedKey)] = trim($value);
      }
    }

    return $params;
  }

  /**
   * @brief 概要テンプレートに項目を追加する。
   *
   * labelN、dataNという名前のパラメータを探し、それを項目のインスタンスに
   * 変換してテンプレートに追加する。
   *
   * @param SrwSummaryTemplate $template 概要テンプレート。
   * @param array $params テンプレートのパラメータの連想配列。
   */
  private function addItemsTo($template, $params) {
    foreach ($params as $key => $value) {
      $matches = [];
      if (preg_match('/^label(\d+)$/', $key, $matches)) {
        $index = intval($matches[1]);
        $dataKey = "data{$index}";
        if (isset($params[$dataKey])) {
          $template->addItem(
            new SrwSummaryTemplateItem($index, $value, $params[$dataKey])
          );
        }
      }
    }
  }
}
