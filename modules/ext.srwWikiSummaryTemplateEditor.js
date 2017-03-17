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
      getHeader: function getHeader() {
        return '<includeonly>{{Infobox\n' +
          '| name = ' + this.name + '\n' +
          '| aboveclass = infobox-above\n' +
          '| above = {{{タイトル|{{PAGENAME}}}}}\n' +
          '| image = {{#invoke:InfoboxImage|InfoboxImage|image={{{image|}}}|size={{{image_size|}}}|sizedefault=frameless|upright=1.15|alt={{{alt|}}}}}\n' +
          '| caption = {{{キャプション|}}}\n' +
          '| rowclass = infobox-row';
      },

      getBody: function getBody(itemGroups) {
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
          }),
          items = itemGroupOffsetPairs.
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

        return items.map(function (item) {
          return item.getMarkup();
        }).join('\n');
      },

      getFooter: function getFooter() {
        return '}}</includeonly><noinclude>\n' +
          '{{Documentation}}</noinclude>';
      }
    };

    ns.newTemplate = function newTemplate(name) {
      var o = Object.create(ns.templatePrototype);

      o.name = name;

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
      $output = $('#srwste-output');

    $generate.click(function generateOnSubmit(ev) {
      var
        template = mw.srwWikiSummaryTemplateEditor.
          newTemplate($templateName.val()),
        itemGroups = mw.srwWikiSummaryTemplateEditor.
          getItemGroups($items.text()),
        lines = [
          template.getHeader(),
          template.getBody(itemGroups),
          template.getFooter()
        ];

      $output.text(lines.join('\n'));

      ev.preventDefault();
    });
  });
}(jQuery, mediaWiki));
