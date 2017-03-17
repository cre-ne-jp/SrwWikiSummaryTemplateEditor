<?php

/**
 * 概要テンプレートを表すクラス。
 *
 * テンプレートには名前と項目を設定することができる。
 */
class SrwSummaryTemplate {

  private $nameProp;
  private $items;

  public function __construct($name) {
    $this->nameProp = $name;
    $this->items = [];
  }

  public function getName() {
    return $this->nameProp;
  }

  public function __toString() {
    return $this->nameProp;
  }

  public function addItem(SrwSummaryTemplateItem $item) {
    $this->items[] = $item;
  }

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
