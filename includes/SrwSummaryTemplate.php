<?php

/**
 * @brief 概要テンプレートを表すクラス。
 *
 * テンプレートには名前と項目を設定することができる。
 */
class SrwSummaryTemplate {
  /** @brief テンプレート名。 */
  private $nameProp;
  /** @brief 項目の配列。 */
  private $items;

  /**
   * @brief コンストラクタ。
   * @param string $name テンプレート名。
   */
  public function __construct($name) {
    $this->nameProp = $name;
    $this->items = [];
  }

  /**
   * @brief テンプレート名を取得する。
   * @return string テンプレート名。
   */
  public function getName() {
    return $this->nameProp;
  }

  /**
   * @brief 文字列に変換する。
   * @return string テンプレートの文字列表現。
   */
  public function __toString() {
    return $this->nameProp;
  }

  /**
   * @brief 項目を追加する。
   * @param SrwSummaryTemplateItem $item 追加する項目。
   */
  public function addItem(SrwSummaryTemplateItem $item) {
    $this->items[] = $item;
  }

  /**
   * @brief 項目の一覧をソースに変換する。
   * @return string 項目の一覧のソース。
   */
  public function itemsToSource() {
    if (count($this->items) <= 0) {
      return '';
    }

    uasort(
      $this->items,
      function (SrwSummaryTemplateItem $a, SrwSummaryTemplateItem $b) {
        $aIndex = $a->getIndex();
        $bIndex = $b->getIndex();

        if ($aIndex === $bIndex) {
          return 0;
        }

        return ($aIndex < $bIndex) ? -1 : 1;
      }
    );

    $lines = [];
    $prevIndex = $this->items[0]->getIndex() - 1;
    foreach ($this->items as $item) {
      $index = $item->getIndex();

      if ($index > $prevIndex + 1) {
        $lines[] = '----';
      }

      $lines[] = $item->toSource();

      $prevIndex = $index;
    }

    return implode(PHP_EOL, $lines);
  }
}
