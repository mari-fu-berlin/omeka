;(function($, window, document, undefined) {

    'use strict';

    var pluginName = 'autocompleteSigle',
        defaults = {
            adminBaseUrl: '/admin',
            warnNoItemFound: 'Kein passendes Objekt gefunden!',
            itemType: 27
        };

    function Plugin ( element, options ) {
        this.element = element;
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    $.extend( Plugin.prototype, {

        init: function() {
            this.formatAutocompletefield(this.element);
            this.formatAutofield(this.settings.autofield);
            this.setAutocomplete();
            // var currentInput = $('.ui-widget input', $(this.element).last());
        },

        formatAutofield: function(id) {
            var autofieldElement = $(id);
            var textarea = $('textarea', autofieldElement);
            var currentName = textarea.attr('name');
            var currentId = textarea.attr('id');
            this.settings.autoFieldCurrentId = currentId;
            var currentValue = textarea.val();
            autofieldElement.addClass('gina-element-read-only');
            textarea.remove();
            var input = $('.input', autofieldElement).append('<input type="hidden" name="' +
                currentName + '" ' +
                'id="' + currentId +
                '" class="readonly" readonly>');

            // important, as otherwhise value will not be escaped!
            // Here it is not that important, as only ids are values ...
            $('#' + currentId, input).val(currentValue);

            $('.input', autofieldElement).append('<a href="' +
                this.settings.adminBaseUrl +
                '/items/show/' +
                currentValue + '" ' +
                'id="' + 'link-' + currentId + '" ' +
                'target="_blank">' +
                'ID: ' + currentValue +
                '</a>');

        },

        formatAutocompletefield: function(id) {
            var autofieldElement = $(id);
            var textarea = $('textarea', autofieldElement);
            var currentName = textarea.attr('name');
            var currentId = textarea.attr('id');
            this.settings.autocompleteFieldCurrentId = currentId;
            var currentValue = textarea.val();
            autofieldElement.addClass('gina-element-read-only');
            textarea.remove();
            var input = $('.input', autofieldElement).append('<input type="text" ' +
                'name="' + currentName + '" ' +
                'id="' + currentId + '">');
            // important, as otherwhise value will not be escaped!
            // here it is IMPORNANT as values can have quotation marks etc.
            $('#' + currentId, input).val(currentValue);
        },

        setAutocomplete: function() {
            var settings = this.settings;
            var currentInput = $('#' + this.settings.autocompleteFieldCurrentId, this.element);
            var savedValue = currentInput.val();
            // console.log(this.settings.autocompleteFieldCurrentId, currentInput.attr('id'));

            $.widget('custom.catcomplete', $.ui.autocomplete, {
                _create: function() {
                    this._super();
                    this.widget().menu('option', 'items', '> :not(.ui-autocomplete-category)');
                },
                _renderMenu: function(ul, items) {
                    var that = this,
                    currentCategory = '';
                    $.each(items, function(index, item) {
                        var li;
                        if (item.category !== currentCategory) {
                            ul.append('<li class="ui-autocomplete-category">' + item.category + '</li>');
                            currentCategory = item.category;
                        }
                        li = that._renderItemData(ul, item);
                        if (item.category) {
                            li.attr('aria-label', item.category + ' : ' + item.label);
                        }
                    });
                }
            });


            currentInput.catcomplete({
                source: '/admin/gina-admin-mod/item-autocomplete-complex?type=' + settings.itemType,
                minLength: 1,
                select: function(event, ui) {
                    $('#' + settings.autoFieldCurrentId)
                        .val(ui.item.item_id);
                    $('#link-' + settings.autoFieldCurrentId)
                        .attr('href', settings.adminBaseUrl + '/items/show/' + ui.item.item_id)
                        .html('ID: ' + ui.item.item_id);
                },
                change: function (event, ui) {
                    if(!ui.item){
                        $(event.target).val(savedValue);
                    }
                },
                focus: function () {
                    return false;
                }
            });

            // $(currentInput).on("autocompleteresponse", function( event, ui ) {
            //     if (ui.content.length === 0) {
            //         $.getJSON(settings.adminBaseUrl
            //             + '/gina-admin-mod/item-autocomplete-id/'
            //             + currentInput.val(), function(data) {
            //             if (data.hasOwnProperty('status')) {
            //                 if (data.status === 200) {
            //                     $('.selected-autocomplete', currentInput.parent())
            //                         .html('<a href="'
            //                             + settings.adminBaseUrl
            //                             + '/items/show/'
            //                             + data.id
            //                             + '" target="_blank">'
            //                             + data.sigle
            //                             + '</a>')
            //                         .show('fade', {}, 500);
            //                 } else {
            //                     $('.selected-autocomplete', currentInput.parent())
            //                         .html('<div class="warn">'
            //                             + settings.warnNoItemFound
            //                             + '</div>')
            //                         .show('fade', {}, 500);
            //                 }
            //             } else {
            //                 $('.selected-autocomplete', currentInput.parent()).html('').hide('fade', {}, 500);
            //             }
            //         });
            //     }
            // } );
        }
    });

    $.fn[ pluginName ] = function(options) {
        return this.each(function() {
            if ( !$.data(this, 'plugin_' + pluginName)) {
                $.data( this, 'plugin_' +
                    pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
