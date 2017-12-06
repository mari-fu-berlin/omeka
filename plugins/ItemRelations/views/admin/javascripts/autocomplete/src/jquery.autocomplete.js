;(function ($, window, document, undefined) {

    'use strict';

    var pluginName = 'itemrelationsAutocomplete',
        defaults = {
            adminBaseUrl: '/admin',
            warnNoItemFound: 'Kein passendes Objekt gefunden!',
            itemType: 27
        };

    function Plugin(element, options) {
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;

        this.searchCache = {};
        this.checkCache = {};

        this.init();
    }

    $.extend(Plugin.prototype, {
        init: function () {
            this.addgparentJQueryFunction();
            this.bindItemRelations();
            this.setAddRow();
        },

        addgparentJQueryFunction: function() {
            $.fn.gparent = function(level){
                if(level > 1) {
                    return $(this).parent().gparent(level - 1);
                }
                return $(this).parent();
            };
        },

        bindItemRelations: function () {
            var self = this;
            $('.item-relations-entry', this.element).each(function () {
                var last = $(this).last();
                var currentInput = $('.ui-widget .input-id input', last);
                var searchContainer = $('.search', last);
                self.setAutocomplete(currentInput);
                self.setSearch(searchContainer, currentInput);
            });
        },

        setSearch: function (searchContainer, currentInput) {
            var old = $('.search-input', searchContainer);
            if(old.length) {
                old.remove();
            }
            var input = $('<input type="text">');
            input.addClass('search-input');
            var settings = this.settings;
            var searchCache = this.searchCache;

            $.widget('custom.catcomplete', $.ui.autocomplete, {
                _create: function () {
                    this._super();
                    this.widget().menu('option', 'items', '> :not(.ui-autocomplete-category)');
                },
                _renderMenu: function (ul, items) {
                    var self = this,
                        currentCategory = '';
                    $.each(items, function (index, item) {
                        var li;
                        if (item.category !== currentCategory) {
                            ul.append('<li class="ui-autocomplete-category">' + item.category + '</li>');
                            currentCategory = item.category;
                        }
                        li = self._renderItemData(ul, item);
                        if (item.category) {
                            li.attr('aria-label', item.category + ' : ' + item.label);
                        }
                    });
                }
            });

            input.catcomplete({
                source: function (request, response) {
                    var term = request.term;
                    if (term in searchCache) {
                        response(searchCache[term]);
                        return;
                    }
                    $.getJSON('/admin/gina-admin-mod/item-autocomplete-complex?type=' + 
                        settings.itemType, 
                        request, 
                        function (data) {
                            searchCache[term] = data;
                            response(data);
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                    currentInput.val(ui.item.item_id).trigger('input');
                },
                // response: function (event, ui) {
                // },
                // change: function (event, ui) {
                // },
                focus: function () {
                    return false;
                }
            });

            searchContainer.append(input);

        },

        syncSearch: function(currentInput, response) {
            var currentSearch = $('.search-input', currentInput.gparent(2));
            if (currentSearch.length && response.hasOwnProperty('sigle') && currentSearch.val() !== response.sigle) {
                currentSearch.val(response.sigle);
            } else if (currentSearch.length && !response.hasOwnProperty('sigle')) {
                currentSearch.val('');
            }
        },

        showAutocompleteResult: function (currentInput, response) {
            var settings = this.settings;
            var autocomplete = $('.selected-autocomplete', currentInput.gparent(2));
            if (response.hasOwnProperty('status')) {
                if (response.status === 200) {
                    autocomplete
                        .html('<a href="' +
                            settings.adminBaseUrl +
                            '/items/show/' +
                            response.id +
                            '" target="_blank">' +
                            response.sigle +
                            '</a>')
                        .show('fade', {}, 500);
                } else {
                    autocomplete
                        .html('<div class="warn">' +
                            settings.warnNoItemFound +
                            '</div>')
                        .show('fade', {}, 500);
                }
            } else {
                autocomplete.html('').hide('fade', {}, 500);
            }
        },

        setAutocomplete: function (currentInput) {
            var settings = this.settings;
            var checkCache = this.checkCache;
            var self = this;
            currentInput.bind('input', function() {
                var term = $(this).val();
                if (term.length > 0) {
                    if (term in checkCache) {
                        self.showAutocompleteResult(currentInput, checkCache[term]);
                        self.syncSearch(currentInput, checkCache[term]);
                    } else {
                        $.getJSON(settings.adminBaseUrl +
                        '/gina-admin-mod/item-autocomplete-id/' +
                        term,
                        function (response) {
                            checkCache[term] = response;
                            self.showAutocompleteResult(currentInput, response);
                            self.syncSearch(currentInput, response);
                        });
                    }
                } else {
                    self.showAutocompleteResult(currentInput, {});
                    self.syncSearch(currentInput, {});
                }
            });
        },

        setAddRow: function () {
            var self = this;
            $('#item-relations-add-relation').click(function () {
                var lastRow = $('.item-relations-new-entry').last();
                var lastAnnotation = $('.item-relations-entry-annotation').last();
                var newRow = lastRow.clone();
                var searchContainer = $('.search', newRow);
                var annotationNum = $('textarea', lastAnnotation).attr('name').match(/\[(\d+)\]/);
                var newAnnotationName = 'item_relations_new_annotation[' + (parseInt(annotationNum[1]) + 1) + ']';

                var newAnnotation = $('<tr class="item-relations-entry-annotation"><td colspan="4">' +
                    '<label for="' + newAnnotationName + '" style="margin-bottom: 8px; display: block;">Annotationen</label>' +
                    '<textarea name="' + newAnnotationName + '" id="' + newAnnotationName + '" style="width:100%;"></textarea>' +
                    '</td></tr>');

                newRow.insertAfter(lastAnnotation);
                newAnnotation.insertAfter(newRow);

                var input = $('.ui-widget input', newRow);
                var select = newRow.find('select');
                input.val('');
                select.val('');
                $('.selected-autocomplete', newRow).html('').hide();
                input.unbind('input');
                self.setAutocomplete(input);
                self.setSearch(searchContainer, $('.ui-widget input', newRow));
                tinyMCE.execCommand('mceAddControl', true, newAnnotationName);
            });
        }
    });

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
