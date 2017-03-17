<?php

/**
 * 概要テンプレートの解析器。
 */
class SrwSummaryTemplateParser {
  private $source;

  public function __construct($source) {
    $this->source = $source;
  }

  public function parse() {
    if (!$this->source) {
      return null;
    }

    $parser = new Parser();
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
