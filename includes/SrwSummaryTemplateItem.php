<?php

class SrwSummaryTemplateItem {
  private $index;
  private $labelProp;
  private $dataProp;

  public function __construct($index, $label, $data) {
    $this->index = $index;
    $this->labelProp = trim($label);
    $this->dataProp = trim($data);
  }

  public function getIndex() {
    return $this->index;
  }

  public function getLabel() {
    return $this->labelProp;
  }

  public function getData() {
    return $this->dataProp;
  }

  public function toSource() {
    if ($this->labelProp !== $this->dataProp) {
      return "{$this->dataProp},{$this->labelProp}";
    }

    return $this->dataProp;
  }
}
