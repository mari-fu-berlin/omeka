;(function($, window, document, undefined) {

    'use strict';

    var pluginName = 'itemrelationsAutocomplete',
        defaults = {
            adminBaseUrl: '/admin',
            warnNoItemFound: 'Kein passendes Objekt gefunden!',
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
            this.bindItemRelations();
            this.setAddRow();
        },

        bindItemRelations: function() {
            var self = this;
            $('.item-relations-entry', this.element).each(function() {
                var currentInput = $('.ui-widget input', $(this).last());
                // console.log(currentInput);
                self.setAutocomplete(currentInput);
            });
        },

        setAutocomplete: function(currentInput) {
            var settings = this.settings;
            currentInput.autocomplete({
                source: '/admin/gina-admin-mod/item-autocomplete?type=27',
                minLength: 1,
                select: function(event, ui) {
                    $('.selected-autocomplete', currentInput.parent())
                        .html('<a href="' +
                            settings.adminBaseUrl +
                            '/items/show/' +
                            ui.item.value +
                            '" target="_blank">' +
                            ui.item.label +
                            '</a>')
                        .show('fade', {}, 500);
                    },
            });

            $(currentInput).on('autocompleteresponse', function( event, ui ) {
                if (ui.content.length === 0) {
                    $.getJSON(settings.adminBaseUrl +
                        '/gina-admin-mod/item-autocomplete-id/' +
                        currentInput.val(), function(data) {
                        // console.log(data);
                        if (data.hasOwnProperty('status')) {
                            if (data.status === 200) {
                                $('.selected-autocomplete', currentInput.parent())
                                    .html('<a href="' +
                                        settings.adminBaseUrl +
                                        '/items/show/' +
                                        data.id +
                                        '" target="_blank">' +
                                        data.sigle +
                                        '</a>')
                                    .show('fade', {}, 500);
                            } else {
                                $('.selected-autocomplete', currentInput.parent())
                                    .html('<div class="warn">' +
                                        settings.warnNoItemFound +
                                        '</div>')
                                    .show('fade', {}, 500);
                            }
                        } else {
                            $('.selected-autocomplete', currentInput.parent()).html('').hide('fade', {}, 500);
                        }
                    });
                }
            } );
        },

        setAddRow: function() {
            var that = this;
            $('#item-relations-add-relation').click(function () {
                var lastRow = $('.item-relations-new-entry').last();
                var lastAnnotation = $('.item-relations-entry-annotation').last();
                var newRow = lastRow.clone();
                var annotationNum = $('textarea', lastAnnotation).attr('name').match(/\[(\d+)\]/);
                var newAnnotationName = 'item_relations_new_annotation[' + (parseInt(annotationNum[1]) + 1) + ']';

                var newAnnotation = $('<tr class="item-relations-entry-annotation"><td colspan="4">' +
                    '<label for="' + newAnnotationName + '" style="margin-bottom: 8px; display: block;">Annotationen</label>' +
                    '<textarea name="' + newAnnotationName + '" id="' + newAnnotationName + '" style="width:100%;"></textarea>' +
                    '</td></tr>');

                // console.log(lastRow);
                // console.log(newAnnotation);

                newRow.insertAfter(lastAnnotation);
                newAnnotation.insertAfter(newRow);

                var input = $('.ui-widget input', newRow);
                var select = newRow.find('select');
                input.val('');
                select.val('');
                $('.selected-autocomplete', newRow).html('').hide();
                that.setAutocomplete(input);
                tinyMCE.execCommand('mceAddControl', true, newAnnotationName);
            });
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
