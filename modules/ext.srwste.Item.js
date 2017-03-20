/*global mediaWiki */

(function (srwste) {
  /**
   * 概要テンプレートの項目を表すクラス。
   * @memberof srwste
   * @constructor
   * @param {integer} index 項目番号。
   * @param {string} label ラベル。
   * @param {string} data 内容を設定するためのパラメータ名。
   */
  function Item(index, label, data) {
    this.index = index;
    this.label = label;
    this.data = (data === undefined) ? label : data;
  }

  /**
   * 項目のウィキマークアップを取得する。
   * @return {string}
   */
  Item.prototype.getMarkup = function getMarkup() {
    return '| label' + this.index + ' = ' + this.label + '\n' +
      '| data' + this.index + ' = {{{' + this.data + '|}}}';
  };

  srwste.Item = Item;
}(mediaWiki.srwste));
