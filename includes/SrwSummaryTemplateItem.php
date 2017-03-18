<?php

/**
 * @brief 概要テンプレートの項目を表すクラス。
 */
class SrwSummaryTemplateItem {
  /** @brief 項目の番号。 */
  private $index;
  /** @brief ラベル。 */
  private $labelProp;
  /** @brief データを指定するのに使うパラメータ名。 */
  private $dataProp;

  /**
   * @brief コンストラクタ。
   * @param int $index 項目の番号。
   * @param string $label ラベル。
   * @param string $data データを指定するのに使うパラメータ名。 */
  public function __construct($index, $label, $data) {
    $this->index = $index;
    $this->labelProp = trim($label);
    $this->dataProp = trim($data);
  }

  /**
   * @brief 項目の番号を取得する。
   * @return int 項目の番号。
   */
  public function getIndex() {
    return $this->index;
  }

  /**
   * @brief ラベルを取得する。
   * @return string ラベル。
   */
  public function getLabel() {
    return $this->labelProp;
  }

  /**
    * @brief データを指定するのに使うパラメータ名を取得する。
    * @return string データを指定するのに使うパラメータ名。
    */
  public function getData() {
    return $this->dataProp;
  }

  /**
   * @brief ソースに変換する。
   *
   * ラベルとパラメータ名が等しいときはパラメータ名になる。
   * ラベルとパラメータ名が異なるときは `パラメータ名,ラベル` の形式になる。
   *
   * @return string 項目を表すソース。
   */
  public function toSource() {
    if ($this->labelProp !== $this->dataProp) {
      return "{$this->dataProp},{$this->labelProp}";
    }

    return $this->dataProp;
  }
}
