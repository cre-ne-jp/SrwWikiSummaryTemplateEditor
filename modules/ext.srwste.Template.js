/*global mediaWiki */

(function (srwste) {
  'use strict';

  /**
   * 概要テンプレートを表すクラス。
   * @memberof srwste
   * @constructor
   */
  function Template(name, itemGroups) {
    this.name = name;
    this.setItems(itemGroups);
  }

  /**
   * 概要テンプレートの項目を設定する。
   * @param {Array} itemGroups
   *   項目グループの配列。
   *   項目グループとは、項目を表すソースの文字列の配列のこと。
   *   項目を表すソースの書式は "項目名" または "項目名,ラベル"。
   */
  Template.prototype.setItems = function setItems(itemGroups) {
    var
      last,
      itemGroupOffsetPairs = itemGroups.map(function (items, index) {
        var prev = last;

        if (index === 0) {
          last = {
            items: items,
            offset: 0
          };

          return last;
        }

        last = {
          items: items,
          offset: Math.ceil((prev.offset + prev.items.length) / 10) * 10
        };

        return last;
      });

    this.items = itemGroupOffsetPairs.
      map(function (pair) {
        return pair.items.map(function (itemSource, i) {
          var
            index = pair.offset + i,
            labelDataPair = itemSource.split(',', 2);

          if (labelDataPair.length === 1) {
            return new srwste.Item(index, labelDataPair[0], labelDataPair[0]);
          }

          return new srwste.Item(index, labelDataPair[1], labelDataPair[0]);
        });
      }).
      reduce(function (acc, itemGroup) {
        return acc.concat(itemGroup);
      }, []);
  };

  /**
   * テンプレートのソースのヘッダを取得する。
   * @return {string}
   */
  Template.prototype.getHeaderOfTemplateSource = function getHeaderOfTemplateSource() {
    return '<includeonly>{{Infobox\n' +
      '| name = ' + this.name + '\n' +
      '| aboveclass = infobox-above\n' +
      '| above = {{{タイトル|{{PAGENAME}}}}}\n' +
      '| image = {{#invoke:InfoboxImage|InfoboxImage|image={{{image|}}}|size={{{image_size|}}}|sizedefault=frameless|upright=1.15|alt={{{alt|}}}}}\n' +
      '| caption = {{{キャプション|}}}\n' +
      '| rowclass = infobox-row';
  };

  /**
   * テンプレートのソースの本体を取得する。
   * @return {string}
   */
  Template.prototype.getBodyOfTemplateSource = function getBodyOfTemplateSource() {
    return this.items.map(function (item) {
      return item.getMarkup();
    }).join('\n');
  };

  /**
   * テンプレートのソースのフッタを取得する。
   * @return {string}
   */
  Template.prototype.getFooterOfTemplateSource = function getFooterOfTemplateSource() {
    return '}}</includeonly><noinclude>\n' +
      '{{Documentation}}</noinclude>';
  };

  /**
   * テンプレートのソースを取得する。
   * @return {string}
   */
  Template.prototype.getTemplateSource = function getTemplateSource() {
    var
      lines = [
        this.getHeaderOfTemplateSource(),
        this.getBodyOfTemplateSource(),
        this.getFooterOfTemplateSource()
      ];

    return lines.join('\n');
  };

  /**
   * 使い方のソースを取得する。
   * @return {string}
   */
  Template.prototype.getSourceForUsage = function getSourceForUsage() {
    var
      templateStart = '{{' + this.name,
      templateEnd = '}}',

      header = [templateStart],
      footer = [templateEnd],
      body = this.items.map(function (item) {
        return '| ' + item.data + ' = ' + item.data;
      }),

      lines = header.concat(body).concat(footer);

    return lines.join('\n');
  };

  /**
   * 雛形のソースを取得する。
   * @return {string}
   */
  Template.prototype.getSourceForBoilerplate = function getSourceForBoilerplate() {
    var
      preStart = '<pre>',
      templateStart = '{{' + this.name,
      templateEnd = '}}',
      preEnd = '</pre>',

      header = [preStart, templateStart],
      footer = [templateEnd, preEnd],
      body = this.items.map(function (item) {
        return '| ' + item.data + ' = ';
      }),

      lines = header.concat(body).concat(footer);

    return lines.join('\n');
  };

  srwste.Template = Template;
}(mediaWiki.srwste));
