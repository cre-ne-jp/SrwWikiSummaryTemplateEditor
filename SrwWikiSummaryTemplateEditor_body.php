<?php

/**
 * 概要テンプレートエディタの特別ページ。
 */
class SpecialSummaryTemplateEditor extends SpecialPage {
  function __construct() {
    parent::__construct('SummaryTemplateEditor');
  }

  /**
   * 特別ページのグループ名を返す。
   * @return string
   */
  protected function getGroupName() {
    return 'wiki';
  }

  public function execute($par) {
    $request = $this->getRequest();
    $query = $request->getQueryValues();
    $output = $this->getOutput();

    $templatePageToLoad = $query['wptemplate-page-to-load'] ?: null;
    $templateSource = $templatePageToLoad ?
      $this->getTemplateSource($templatePageToLoad) : null;
    $summaryTemplate = null;
    if ($templateSource !== null) {
      $parser = new Parser();
      $parser->mOptions = new ParserOptions();

      try {
        $templateParser = new SrwSummaryTemplateParser($templateSource);
        $summaryTemplate = $templateParser->parse();
      } catch (Exception $e) {
      }
    }

    $this->setHeaders();

    $output->addModules('ext.srwWikiSummaryTemplateEditor');

    $output->addWikiMsg('summarytemplateeditor-summary');

    $output->addWikiMsg('srwste-input-section');

    $output->addWikiMsg('srwste-load-template-section');
    $output->addWikiMsg('srwste-load-template-description');

    $loadTemplateForm = $this->createLoadTemplateForm();
    $loadTemplateForm->show();

    $output->addWikiMsg('srwste-item-list-section');
    $output->addWikiMsg('srwste-enter-items');
    $output->addWikiMsg('srwste-item-format');
    $output->addWikiMsg('srwste-separate-items');

    $itemListForm = $this->createInputForm($summaryTemplate);
    $itemListForm->show();

    $output->addWikiMsg('srwste-output-section');

    $outputForm = $this->createOutputForm();
    $outputForm->show();
  }

  /**
   * テンプレートのソースを取得する。
   * @param string $templateName テンプレート名。
   * @return string テンプレートのソース。
   * @return null テンプレートのソースを取得できなかった場合。
   */
  private function getTemplateSource($templateName) {
    if (!$templateName) {
      return null;
    }

    $templateTitle = Title::newFromText($templateName);
    if ($templateTitle === null) {
      return null;
    }

    $templatePage = WikiPage::factory($templateTitle);
    if (!$templatePage instanceof WikiPage) {
      return null;
    }

    $templatePage->loadPageData();
    if (!$templatePage->exists()) {
      return null;
    }

    try {
      return $templatePage->getContent()->getNativeData();
    } catch (Exception $e) {
      return null;
    }
  }

  private function createLoadTemplateForm() {
    $category = Category::newFromName('概要テンプレート');
    $pageTitles = $category->getMembers();
    $templateSelectorOptions = [];
    $firstTemplateTitle = null;
    foreach ($pageTitles as $title) {
      $titleText = $title->getFullText();
      $templateSelectorOptions[$titleText] = $titleText;

      if ($firstTemplateTitle === null) {
        $firstTemplateTitle = $titleText;
      }
    }

    $formDescriptor = [
      'template-page-to-load' => [
        'type' => 'select',
        'label-message' => 'srwste-template-page',
        'options' => $templateSelectorOptions
      ]
    ];

    if ($firstTemplateTitle !== null) {
      $formDescriptor['template-page-to-load']['default'] =
        $firstTemplateTitle;
    }

    $form = new HTMLForm($formDescriptor, $this->getContext());
    $form->setSubmitText(wfMessage('srwste-load'));
    $form->setMethod('GET');
    $form->setSubmitCallback(function ($data, $formArg) { return false; });

    return $form;
  }

  private function createInputForm($summaryTemplate = null) {
    $formDescriptor = [
      'template-name' => [
        'id' => 'srwste-template-name',
        'type' => 'text',
        'default' => $summaryTemplate ? $summaryTemplate->getName() : '○○概要',
        'label-message' => 'srwste-template-name'
      ],
      'items' => [
        'id' => 'srwste-items',
        'type' => 'textarea',
        'default' => $summaryTemplate ?
          $summaryTemplate->itemsToSource() :
          "項目A\n項目B\n----\n項目C\n----\n項目D\n項目E",
        'label-message' => 'srwste-items'
      ]
    ];
    $form = new HTMLForm($formDescriptor, $this->getContext());
    $form->setDisplayFormat('div');
    $form->setSubmitCallback(function ($data, $formArg) { return false; });
    $form->setSubmitId('srwste-generate');
    $form->setSubmitText(wfMessage('srwste-generate'));

    return $form;
  }

  private function createOutputForm() {
    $formDescriptor = [
      'output' => [
        'id' => 'srwste-output',
        'type' => 'textarea',
        'readonly' => true,
        'class' => 'HTMLTextAreaField'
      ]
    ];

    $form = new HTMLForm($formDescriptor, $this->getContext());
    $form->setDisplayFormat('div');
    $form->setSubmitCallback(function ($data, $formArg) { return false; });
    $form->suppressDefaultSubmit();

    return $form;
  }
}
