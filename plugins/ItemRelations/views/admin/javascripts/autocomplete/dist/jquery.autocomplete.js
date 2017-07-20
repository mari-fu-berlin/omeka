/*
 *  Item-Relations-Autocomplete - v1.0.0
 *  Autocomplete for Omeka ItemRelations Plugin
 *
 *  Made by Viktor Grandgeorg
 *  Under MIT License
 */
;(function($, window, document, undefined) {

    "use strict";

    var pluginName = "itemrelationsAutocomplete",
        defaults = {
            adminBaseUrl: "/admin",
            warnNoItemFound: "Kein passendes Objekt gefunden!",
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
            var currentInput = $('.ui-widget input', $(this.element).last());
            this.setAutocomplete(currentInput);
            this.setAddRow();
        },

        setAutocomplete: function(currentInput) {
            var settings = this.settings;
            currentInput.autocomplete({
                source: "/admin/gina-admin-mod/item-autocomplete?type=27",
                minLength: 1,
                select: function(event, ui) {
                    $('.selected-autocomplete', currentInput.parent())
                        .html('<a href="'
                            + settings.adminBaseUrl
                            + '/items/show/'
                            + ui.item.value
                            + '" target="_blank">'
                            + ui.item.label
                            + '</a>')
                        .show('fade', {}, 500);
                    },
            });

            $(currentInput).on("autocompleteresponse", function( event, ui ) {
                if (ui.content.length === 0) {
                    $.getJSON(settings.adminBaseUrl
                        + '/gina-admin-mod/item-autocomplete-id/'
                        + currentInput.val(), function(data) {
                        // console.log(data);
                        if (data.hasOwnProperty('status')) {
                            if (data.status === 200) {
                                $('.selected-autocomplete', currentInput.parent())
                                    .html('<a href="'
                                        + settings.adminBaseUrl
                                        + '/items/show/'
                                        + data.id
                                        + '" target="_blank">'
                                        + data.sigle
                                        + '</a>')
                                    .show('fade', {}, 500);
                            } else {
                                $('.selected-autocomplete', currentInput.parent())
                                    .html('<div class="warn">'
                                        + settings.warnNoItemFound
                                        + '</div>')
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
            $('.item-relations-add-relation').click(function () {
                // var lastRow = $(this.element).last();
                var lastRow = $('.item-relations-entry').last();
                var newRow = lastRow.clone();
                lastRow.after(newRow);
                var input = $('.ui-widget input', newRow);
                var select = newRow.find('select');
                input.val('');
                select.val('');
                $('.selected-autocomplete', newRow).html('').hide();
                that.setAutocomplete(input);
            });
        }
    });

    $.fn[ pluginName ] = function(options) {
        return this.each(function() {
            if ( !$.data(this, "plugin_" + pluginName)) {
                $.data( this, "plugin_" +
                    pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
