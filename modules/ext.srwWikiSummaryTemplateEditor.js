/**
 * JavaScript for SrwWikiSummaryTemplateEditor
 */

(function ($, mw) {
  (function () {
    var ns = {
    };

    ns.itemPrototype = {
      getMarkup: function getMarkup() {
        return '| label' + this.index + ' = ' + this.label + '\n' +
          '| data' + this.index + ' = {{{' + this.data + '|}}}';
      }
    };

    ns.templatePrototype = {
      setItems: function setItems(itemGroups) {
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
                return ns.newItem(index, labelDataPair[0], labelDataPair[0]);
              }

              return ns.newItem(index, labelDataPair[1], labelDataPair[0]);
            });
          }).
          reduce(function (acc, itemGroup) {
            return acc.concat(itemGroup);
          }, []);
      },

      getHeaderOfTemplateSource: function getHeaderOfTemplateSource() {
        return '<includeonly>{{Infobox\n' +
          '| name = ' + this.name + '\n' +
          '| aboveclass = infobox-above\n' +
          '| above = {{{タイトル|{{PAGENAME}}}}}\n' +
          '| image = {{#invoke:InfoboxImage|InfoboxImage|image={{{image|}}}|size={{{image_size|}}}|sizedefault=frameless|upright=1.15|alt={{{alt|}}}}}\n' +
          '| caption = {{{キャプション|}}}\n' +
          '| rowclass = infobox-row';
      },

      getBodyOfTemplateSource: function getBodyOfTemplateSource() {
        return this.items.map(function (item) {
          return item.getMarkup();
        }).join('\n');
      },

      getFooterOfTemplateSource: function getFooterOfTemplateSource() {
        return '}}</includeonly><noinclude>\n' +
          '{{Documentation}}</noinclude>';
      },

      getTemplateSource: function getTemplateSource() {
        var
          lines = [
            this.getHeaderOfTemplateSource(),
            this.getBodyOfTemplateSource(),
            this.getFooterOfTemplateSource()
          ];

        return lines.join('\n');
      },

      getSourceForUsage: function getSourceForUsage() {
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
      },

      getSourceForBoilerplate: function getSourceForBoilerplate() {
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
      }
    };

    ns.newTemplate = function newTemplate(name, itemGroups) {
      var o = Object.create(ns.templatePrototype);

      o.name = name;

      o.setItems(itemGroups);

      return o;
    };

    ns.newItem = function newItem(index, label, data) {
      var o = Object.create(ns.itemPrototype);

      o.index = index;
      o.label = label;
      o.data = (data === undefined) ? label : data;

      return o;
    }

    ns.getItemGroups = function getItemGroups(str) {
      var
        items = [[]],
        lines = str.trim().split(/\r\n|\r|\n/);

      lines.forEach(function (line) {
        var
          trimmed = line.trim(),
          holder = items[items.length - 1];

        if (trimmed.length === 0) {
          return;
        }

        if (/^----/.test(trimmed)) {
          if (holder.length > 0) {
            items.push([]);
          }

          return;
        }

        holder.push(trimmed);
      });

      if (items[items.length - 1].length === 0) {
        items.pop();
      }

      return items;
    };

    mw.srwWikiSummaryTemplateEditor = ns;
  }());

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
        itemGroups = mw.srwWikiSummaryTemplateEditor.
          getItemGroups($items.text()),
        template = mw.srwWikiSummaryTemplateEditor.
          newTemplate($templateName.val(), itemGroups);

      $templateSource.text(template.getTemplateSource());
      $sourceForUsage.text(template.getSourceForUsage());
      $sourceForBoilerplate.text(template.getSourceForBoilerplate());

      ev.preventDefault();
    });
  });
}(jQuery, mediaWiki));
