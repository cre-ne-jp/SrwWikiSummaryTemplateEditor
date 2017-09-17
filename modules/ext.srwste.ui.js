/*global jQuery, mediaWiki */

(function ($, srwste) {
  'use strict';

  /**
   * 項目一覧のソースから項目グループに変換する。
   * @param {string} source 項目一覧のソース。
   * @return {Array} 項目グループの配列。
   */
  srwste.toItemGroups = function getItemGroups(source) {
    var
      itemGroups = [[]],
      lines = source.trim().split(/\r\n|\r|\n/);

    lines.forEach(function (line) {
      var
        trimmed = line.trim(),
        itemGroup = itemGroups[itemGroups.length - 1];

      if (trimmed.length <= 0) {
        return;
      }

      if (/^----/.test(trimmed)) {
        if (itemGroup.length > 0) {
          // グループの区切りが現れたので、新しいグループを追加する
          itemGroups.push([]);
        }

        return;
      }

      itemGroup.push(trimmed);
    });

    if (itemGroups[itemGroups.length - 1].length <= 0) {
      // 最後に空のグループが残っていたら取り除く
      itemGroups.pop();
    }

    return itemGroups;
  };

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
        itemGroups = srwste.toItemGroups($items.val()),
        template = new srwste.Template($templateName.val(), itemGroups);

      $templateSource.val(template.getTemplateSource());
      $sourceForUsage.val(template.getSourceForUsage());
      $sourceForBoilerplate.val(template.getSourceForBoilerplate());

      ev.preventDefault();
    });
  });
}(jQuery, mediaWiki.srwste));
