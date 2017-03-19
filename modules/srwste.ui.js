/*global jQuery, mediaWiki */

(function ($, srwste) {
  $(function () {
    var
      $templateName = $('#srwste-template-name'),
      $items = $('#srwste-items'),
      $generate = $('#srwste-generate'),
      $templateSource = $('#srwste-template-source'),
      $sourceForUsage = $('#srwste-source-for-usage'),
      $sourceForBoilerplate = $('#srwste-source-for-boilerplate');

    $generate.click(function generateOnSubmit(ev) {
      var
        itemGroups = srwste.getItemGroups($items.text()),
        template = new srwste.Template($templateName.val(), itemGroups);

      $templateSource.text(template.getTemplateSource());
      $sourceForUsage.text(template.getSourceForUsage());
      $sourceForBoilerplate.text(template.getSourceForBoilerplate());

      ev.preventDefault();
    });
  });
}(jQuery, mediaWiki.srwste));
