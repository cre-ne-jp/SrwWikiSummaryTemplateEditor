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
   * @return string 「データとツール」のグループ。
   */
  protected function getGroupName() {
    return 'wiki';
  }

  /**
   * 特別ページの表示処理。
   */
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

    $output->addWikiMsg('srwste-template-source-section');

    if ($summaryTemplate !== null) {
      $output->addWikiMsg('srwste-replace-source-with', "[[{$templatePageToLoad}]]");
    }

    $templateSourceForm = $this->createTemplateSourceForm();
    $templateSourceForm->show();

    $output->addWikiMsg('srwste-sources-for-documentation-section');

    if ($summaryTemplate !== null) {
      $output->addWikiMsg('srwste-paste-into', "[[{$templatePageToLoad}/doc]]");
    }

    $sourcesForDocumentationForm = $this->createSourcesForDocumentationForm();
    $sourcesForDocumentationForm->show();
  }

  /**
   * テンプレートのソースを取得する。
   * @param string $templateName テンプレート名。
   * @return string|null テンプレートのソース。取得できなかった場合はnullになる。
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

  /**
   * テンプレート読み込みフォームを作成する。
   * @return HTMLForm
   */
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

  /**
   * 項目入力フォームを作成する。
   * @return HTMLForm
   */
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

  /**
   * テンプレートのソースのフォームを作成する。
   * @return HTMLForm
   */
  private function createTemplateSourceForm() {
    $formDescriptor = [
      'template-source' => [
        'id' => 'srwste-template-source',
        'type' => 'textarea',
        'readonly' => true
      ]
    ];

    $form = new HTMLForm($formDescriptor, $this->getContext());
    $form->setDisplayFormat('div');
    $form->setSubmitCallback(function ($data, $formArg) { return false; });
    $form->suppressDefaultSubmit();

    return $form;
  }

  /**
   * 解説用のソースのフォームを作成する。
   * @return HTMLForm
   */
  private function createSourcesForDocumentationForm() {
    $formDescriptor = [
      'source-for-usage' => [
        'id' => 'srwste-source-for-usage',
        'type' => 'textarea',
        'readonly' => true,
        'label-message' => 'srwste-source-for-usage'
      ],
      'source-for-boilerplate' => [
        'id' => 'srwste-source-for-boilerplate',
        'type' => 'textarea',
        'readonly' => true,
        'label-message' => 'srwste-source-for-boilerplate'
      ]
    ];

    $form = new HTMLForm($formDescriptor, $this->getContext());
    $form->setDisplayFormat('div');
    $form->setSubmitCallback(function ($data, $formArg) { return false; });
    $form->suppressDefaultSubmit();

    return $form;
  }
}
