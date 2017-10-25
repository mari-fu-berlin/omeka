/*
 *  Item-Sigle-Autocomplete - v1.0.0
 *  Autocomplete for Omeka items' sigle elements
 *
 *  Made by Viktor Grandgeorg
 *  Under MIT License
 */
;(function($, window, document, undefined) {

    'use strict';

    var pluginName = 'autocompleteSigle',
        defaults = {
            adminBaseUrl: '/admin',
            warnNoItemFound: 'Kein passendes Objekt gefunden!',
            itemType: 27
        };

    function Plugin (element, options) {
        this.element = element;
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    $.extend(Plugin.prototype, {

        init: function() {
            this.formatAutocompletefield(this.element);
            this.formatAutofield(this.settings.autofield);
            this.setAutocomplete();
        },

        formatAutofield: function(id) {
            var autofieldElement = $(id);
            var textarea = $('textarea', autofieldElement);
            var currentName = textarea.attr('name');
            var currentId = textarea.attr('id');
            this.settings.autoFieldCurrentId = currentId;
            var currentValue = textarea.val();
            autofieldElement.addClass('gina-element-auto-field');
            // console.log(autofieldElement, textarea, currentName, currentId, currentValue);
            textarea.remove();
            var input = $('.input', autofieldElement).append('<input type="hidden" name="' +
                currentName + '" ' +
                'id="' + currentId +
                '" class="readonly" readonly>');

            // important, as otherwhise value will not be escaped!
            // Here it is not that important, as only ids are values ...
            $('#' + currentId, input).val(currentValue);

            var href = '/';
            var html = '';

            if (currentValue > 0) {
                href = this.settings.adminBaseUrl + '/items/show/' + currentValue;
                html = 'ID: ' + currentValue;
            }

            $('.input', autofieldElement).append('<a href="' + href + '" ' +
                'id="' + 'link-' + currentId + '" ' +
                'target="_blank">' + html + '</a>');
        },

        formatAutocompletefield: function(id) {
            var autofieldElement = $(id);
            var textarea = $('textarea', autofieldElement);
            var currentName = textarea.attr('name');
            var currentId = textarea.attr('id');
            this.settings.autocompleteFieldCurrentId = currentId;
            var currentValue = textarea.val();
            // sanitize autocomplete value:
            if (currentValue !== this.settings.currentAutocompleteFieldValue) {
                currentValue = this.settings.currentAutocompleteFieldValue;
            }
            autofieldElement.addClass('gina-element-auto-field');
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
            var plugin = this;

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
                    settings.selectedAutocompleteFieldValue = ui.item.value;
                    settings.selectedAutoFieldValue = ui.item.item_id;
                    $('#' + settings.autoFieldCurrentId)
                        .val(ui.item.item_id);
                    $('#link-' + settings.autoFieldCurrentId)
                        .attr('href', settings.adminBaseUrl + '/items/show/' + ui.item.item_id)
                        .html('ID: ' + ui.item.item_id);
                },
                response: function(event, ui) {
                    // console.log('response');
                    settings.uiDataCache = ui.content;
                },
                change: function (event, ui) {
                    if(!ui.item){

                        // if one match in response cache
                        if (typeof settings.uiDataCache !== 'undefined' &&
                            settings.uiDataCache.length === 1 &&
                            $(event.target).val().length > 0 &&
                            $(event.target).val() === settings.uiDataCache[0].value
                        ) {
                            settings.selectedAutocompleteFieldValue = settings.uiDataCache[0].value;
                            settings.selectedAutoFieldValue = settings.uiDataCache[0].item_id;

                            $(event.target).val(settings.uiDataCache[0].value);
                            $('#' + settings.autoFieldCurrentId)
                                .val(settings.uiDataCache[0].item_id);
                            $('#link-' + settings.autoFieldCurrentId)
                                .attr('href', settings.adminBaseUrl + '/items/show/' + settings.uiDataCache[0].item_id)
                                .html('ID: ' + settings.uiDataCache[0].item_id);
                        } else {

                            // delete action
                            if ($(event.target).val().length === 0) {

                                $('#' + settings.autoFieldCurrentId).val('');

                                var delMsg = $('<div style="display:none; color:#f30;" id="autocomp-del-msg">gelöscht</div>');

                                $('#link-' + settings.autoFieldCurrentId)
                                    .attr('href', settings.adminBaseUrl + '/')
                                    .html('')
                                    .append(delMsg);
                                $('#autocomp-del-msg').fadeIn(600, function() {
                                    $(this).fadeOut(600, function() {
                                        $(this).remove();
                                    });
                                });
                                settings.selectedAutocompleteFieldValue = '';
                                settings.selectedAutoFieldValue = '';
                                // console.log('cleared');

                            } else {

                                // if previous successfull selection
                                if (typeof settings.selectedAutocompleteFieldValue !== 'undefined' &&
                                    settings.selectedAutocompleteFieldValue.length > 0 &&
                                    currentInput.val() !== settings.selectedAutocompleteFieldValue) {

                                    // console.log('with unsaved');
                                    plugin.displayErrorDialog(currentInput, 'Ihre vorherige Auswahl');
                                    $(event.target).val(settings.selectedAutocompleteFieldValue);
                                    $('#' + settings.autoFieldCurrentId).val(settings.selectedAutoFieldValue);
                                    $('#link-' + settings.autoFieldCurrentId)
                                        .attr('href', settings.adminBaseUrl + '/items/show/' + settings.selectedAutoFieldValue)
                                        .html('ID: ' + settings.selectedAutoFieldValue);

                                // if saved values
                                } else if (settings.currentAutocompleteFieldValue.length > 0 && settings.currentAutoFieldValue > 0) {
                                    // console.log('with saved');
                                    plugin.displayErrorDialog(currentInput, 'den gespeicherten Wert');
                                    $(event.target).val(settings.currentAutocompleteFieldValue);
                                    $('#' + settings.autoFieldCurrentId).val(settings.currentAutoFieldValue);
                                    $('#link-' + settings.autoFieldCurrentId)
                                        .attr('href', settings.adminBaseUrl + '/items/show/' + settings.currentAutoFieldValue)
                                        .html('ID: ' + settings.currentAutoFieldValue);

                                // if empty
                                } else {
                                    // console.log('with empty');
                                    plugin.displayErrorDialog(currentInput, 'leere Werte');
                                    $(event.target).val('');
                                    $('#' + settings.autoFieldCurrentId).val('');
                                    $('#link-' + settings.autoFieldCurrentId)
                                        .attr('href', settings.adminBaseUrl + '/')
                                        .html('');
                                }
                            }

                        }
                    }
                },
                focus: function () {
                    return false;
                }
            });

            currentInput.closest('form').submit(function(e) {

                // if empty (delete)
                if (currentInput.val().length === 0) {
                    // just clear the autofield value and let post through
                    $('#' + settings.autoFieldCurrentId).val('');
                }

                // e.preventDefault();
                // console.log($('#' + settings.autoFieldCurrentId).val());
                // console.log(settings.currentAutoFieldValue);
                // console.log(currentInput.val());
                // console.log(settings.currentAutocompleteFieldValue);

                // if saved values or previous successfull selection
                if (
                    (currentInput.val().length > 0 && $('#' + settings.autoFieldCurrentId).val().length === 0) ||
                    ($('#' + settings.autoFieldCurrentId).val() === settings.currentAutoFieldValue &&
                        currentInput.val().length > 0 && currentInput.val() !== settings.currentAutocompleteFieldValue) ||
                    (typeof settings.selectedAutocompleteFieldValue !== 'undefined' &&
                        settings.selectedAutocompleteFieldValue.length > 0 &&
                        currentInput.val() !== settings.selectedAutocompleteFieldValue)
                ) {

                    e.preventDefault();
                    currentInput.trigger('blur');
                }

            });

        },

        displayErrorDialog: function(currentInput, type) {

            var currentItemTypes = this.settings.itemType.split(',');
            var linkToCurrentItemTypes = '<ul>';
            for (var i = 0; i < currentItemTypes.length; i++) {
                linkToCurrentItemTypes = linkToCurrentItemTypes +
                    '<li><a href="' + this.settings.adminBaseUrl +
                    '/items/add?type=' + currentItemTypes[i] +
                    '" target="_blank">' +
                    this.settings.itemTypes[currentItemTypes[i]] +
                    '</a></li>';
            }
            linkToCurrentItemTypes = linkToCurrentItemTypes + '</ul>';

            var currentInputTxt = $('<div>').text(currentInput.val()).html();
            $('<div />').html('<p>' +
                'Sie haben einen Wert in der Sigle-Quelle angeggeben. ' +
                'Sie haben diesen aber nicht durch die Auswahl aus der Liste bestätigt. ' +
                'Bitte korrigieren Sie das. Sollte die Quelle in der Liste nicht exisitieren, ' +
                'legen Sie die Quelle vorher an.<p>' +
                '<p>Ihre Eingabe lautete: <strong>' + currentInputTxt + '</strong></p>' +
                '<p>Sie wurde zurückgesetzt auf ' + type + '. ' +
                '<p>Folgende Quelltypen werden bei diesem Objekt berücksichtigt:</p>' +
                linkToCurrentItemTypes)
            .dialog({
                modal:true,
                buttons: [{
                    text: 'OK',
                    click: function() {
                        $(this).dialog('close');
                    },
                }],
                open: function() {
                    $('button', $(this).siblings('.ui-dialog-buttonpane')).focus();
                }
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
