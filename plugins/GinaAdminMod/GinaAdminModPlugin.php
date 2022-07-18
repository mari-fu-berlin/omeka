<?php
/**
 * Admin Modifications
 *
 * Modify the Admin UI of Omeka to suit our needs
 *
 * @see http://omeka.readthedocs.io/en/latest/Reference/filters/index.html
 * @see http://omeka.readthedocs.io/en/latest/Reference/hooks/index.html
 * @see http://omeka.readthedocs.io/en/latest/Reference/filters/Element_Save_Filter.html
 *
 * @copyright Copyright Grandgeorg Websolutions 2017
 * @license GPLv3
 * @package AdminMod
 */

/**
 * The Admin Modifications plugin.
 * @package Omeka\Plugins\ImageConvert
 */
class GinaAdminModPlugin extends Omeka_Plugin_AbstractPlugin
{
    public $itemTypes = null;

    protected $_hooks = array(
        // 'define_acl',
        'define_routes',
        'initialize',
        // 'admin_head',
        // 'config_form',
        // 'config',
        // 'admin_items_browse_simple_each',
        'admin_items_form_item_types',
        // 'admin_items_panel_fields',
        // 'admin_footer_last',
    );

    protected $_filters = array(
        'admin_navigation_main',
        // 'post_admin_navigation_main',
        // 'admin_dashboard_stats',
        // 'admin_dashboard_panels',
        'admin_items_form_tabs',
        'addItemTypeTitleToDcTitle' => array('Save', 'Item', 'Dublin Core', 'Title')
    );

    /**
     * Modify the admin navigation
     *
     * @param array $navArray The array of admin navigation links
     * @return array
     */
    public function filterAdminNavigationMain($navArray)
    {
        $counter = 0;
        $new     = array();

        // var_dump($this->itemTypes);

        foreach ($navArray as $nav) {
            $new[] = $nav;
            if ($nav['label'] == __('Alle Objekte') && substr($nav['uri'], -6) == '/items') {
                if (isset($this->itemTypes) && is_array($this->itemTypes)) {
                    foreach ($this->itemTypes as $itemType) {
                        $new[] = array(
                            // 'label' => __('Objekte') . ' - ' . __($itemType->name),
                            'label' => '↳ ' . __($itemType->name),
                            'class' => 'subnav',
                            'uri' => url('/items/browse?type=' . $itemType->id),
                        );
                    }
                }
            }
        }

        $new[] = array(
            'label'     => __('Mari Verwaltung'),
            'uri'       => url('gina-admin-mod/admin'),
            'visible'   => true
            // 'resource'  => 'ZlbTiles_CustomTiles',
            // 'privilege' => 'browse'
        );

        return $new;
    }

    // public function filterPostAdminNavigationMain($nav)
    // {
    //     // var_dump($nav);
    //     $strNav = $nav->__toString();
    //     var_dump($strNav);
    //     return $nav;
    // }

    /**
     * Add the translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
        $this->itemTypes = $this->_db->getTable('ItemType')->findAll();

    }

    /**
     * Add the routes
     *
     * @param Zend_Controller_Router_Rewrite $router
     */
    public function hookDefineRoutes($args)
    {
        // Don't add these routes on the public side to avoid conflicts.
        if (!is_admin_theme()) {
            return;
        }

        $router = $args['router'];

        $router->addRoute(
            'gina-admin-mod-autocomplete-conf',
            new Zend_Controller_Router_Route(
                '/gina-admin-mod/autocomplete-conf',
                array(
                    'module'     => 'gina-admin-mod',
                    'controller' => 'index',
                    'action'     => 'autocomplete-conf',
                    // 'id'         =>  null
                )
            )
        );

        $router->addRoute(
            'gina-admin-mod',
            new Zend_Controller_Router_Route(
                '/gina-admin-mod/item-autocomplete',
                array(
                    'module'     => 'gina-admin-mod',
                    'controller' => 'index',
                    'action'     => 'itemautocomplete',
                )
            )
        );
        $router->addRoute(
            'gina-admin-mod-itemautocompletecomplex',
            new Zend_Controller_Router_Route(
                '/gina-admin-mod/item-autocomplete-complex',
                array(
                    'module'     => 'gina-admin-mod',
                    'controller' => 'index',
                    'action'     => 'item-autocomplete-complex',
                )
            )
        );
        $router->addRoute(
            'gina-admin-mod-itemautocompleteid',
            new Zend_Controller_Router_Route(
                '/gina-admin-mod/item-autocomplete-id/:id',
                array(
                    'module'     => 'gina-admin-mod',
                    'controller' => 'index',
                    'action'     => 'itemautocompleteid',
                    'id'         => null
                )
            )
        );

        // config
        // $router->addRoute(
        //     'gina-admin-mod-admin-index',
        //     new Zend_Controller_Router_Route(
        //         '/gina-admin-mod/admin',
        //         array(
        //             'module'     => 'gina-admin-mod',
        //             'controller' => 'admin',
        //             'action'     => 'index',
        //         )
        //     )
        // );

        // $router->addRoute(
        //     'gina-admin-mod-admin-autocomplete-show',
        //     new Zend_Controller_Router_Route(
        //         '/gina-admin-mod/admin/autocomplete/show',
        //         array(
        //             'module'     => 'gina-admin-mod',
        //             'controller' => 'admin',
        //             'action'     => 'autocomplete-show',
        //         )
        //     )
        // );

        // $router->addRoute(
        //     'gina-admin-mod-admin-autocomplete-sanitize-items',
        //     new Zend_Controller_Router_Route(
        //         '/gina-admin-mod/admin/sanitize-items',
        //         array(
        //             'module'     => 'gina-admin-mod',
        //             'controller' => 'admin',
        //             'action'     => 'sanitize-items',
        //         )
        //     )
        // );


    }


    /**
     * Tabs in Admin Item Edit
     *
     * @param array $tabs Array of admin edit Tabs
     * @param array $args Args with Item
     * @return array
     */
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        $new = array();
        foreach ($tabs as $key => $tab) {
            if (
                $key !== 'Dublin Core'
                // &&
                // $key !== 'Tags'
            ) {
                if ($key === 'Item Type Metadata') {
                    $new[$key] = $tab
                        . '<input type="hidden" name="Elements[50][0][text]" value="">' // DC title
                        // . '<input type="hidden" name="tags" id="tags" value="">' // Tags
                        ;
                } else {
                    $new[$key] = $tab;
                }
            }
        }
        return $new;
    }

    /**
     * @param $text input element text
     * @param array $args Args
     * @return string The new value for the element text
     */
    public function addItemTypeTitleToDcTitle($text, $args)
    {
        return $_POST['Elements'][62][0]['text'];
    }


    /**
     *
     * @param array array with item and view object
     * @return void
     */
    public function hookAdminItemsFormItemTypes($args)
    {

        $item = $args['item'];
        // var_dump($item);die();
        if (isset($item->item_type_id) && !empty($item->item_type_id)) {
            $type = $item->item_type_id;
        } else {
            $type = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('type', null);
        }

        $itemTypesById = array();
        foreach ($this->itemTypes as $itemType) {
            $itemTypesById[$itemType->id] = $itemType->name;
        }

        $autocompleteTable = $this->_db->getTable('ItemAutocomplete');
        $autocompletes = $autocompleteTable->findBy(array('item_type_id' => $type));

        $settings = array();

        foreach ($autocompletes as $autocomplete) {
            $currentValues = $this->_getCurrentAutofieldValues($item, $autocomplete);
            $currentValues['autocompleteField'] = $this->_sanitizeAutocompleteField($currentValues, $autocomplete, $item);
            $settings[] = array(
                'autocompleteFieldId' => 'element-' . $autocomplete->autocomplete_field_id,
                'autoFieldId' => 'element-' . $autocomplete->auto_field_id,
                'itemTypeIds' => $autocomplete->autocomplete_item_type_ids,
                'currentAutocompleteFieldValue' => $currentValues['autocompleteField'],
                'currentAutoFieldValue' => $currentValues['autoField'],
            );
        }

        echo '<script type="text/javascript" src="'
            . WEB_PLUGIN
            // . '/GinaAdminMod/views/admin/javascripts/autocomplete/src/autocomplete.sigle.js"></script>' . "\n";
            . '/GinaAdminMod/views/admin/javascripts/autocomplete/dist/autocomplete.sigle.min.js"></script>' . "\n";

        $jsonSettings = array();
        foreach ($settings as $setting) {
            $jsonSettings[] = array(
                'selectorAutocompleteFieldId' => $setting['autocompleteFieldId'],
                'autofield' => '#' . $setting['autoFieldId'],
                'itemType' => $setting['itemTypeIds'],
                'currentAutocompleteFieldValue' => $setting['currentAutocompleteFieldValue'],
                'currentAutoFieldValue' => $setting['currentAutoFieldValue'],
                // 'itemTypes' => $itemTypesById
            );
        }

        echo '<script>' . "\n";
        echo 'jQuery(document).data("ginaConfAutocomplete", ' . json_encode($jsonSettings) . ');' . "\n";
        echo 'jQuery(document).data("ginaItemTypesById", ' . json_encode($itemTypesById) . ');' . "\n";
        echo 'jQuery(function($) {
            for (var i = 0; i < $(document).data("ginaConfAutocomplete").length; i++) {
                $(document).data("ginaConfAutocomplete")[i].itemTypes = $(document).data("ginaItemTypesById");
                $("#" + $(document).data("ginaConfAutocomplete")[i].selectorAutocompleteFieldId).autocompleteSigle(
                    $(document).data("ginaConfAutocomplete")[i]
                );
            }
        });' . "\n";
        echo '</script>' . "\n";


        return;

    }

    protected function _getSettings($ItemTypeElements, $fieldNames)
    {
        $fieldIds = array(
            'autocompleteFieldId' => '',
            'autoFieldId' => '',
        );
        foreach ($ItemTypeElements as $key => $ItemTypeElement) {
            if ($ItemTypeElement->name === $fieldNames['autocompleteField']) {
                $fieldIds['autocompleteFieldId'] = $ItemTypeElement->id;
            }
            if ($ItemTypeElement->name === $fieldNames['autoField']) {
                $fieldIds['autoFieldId'] = $ItemTypeElement->id;
            }
        }
        return $fieldIds;
    }

    /**
     *
     * @return array
     */
    protected function _getCurrentAutofieldValues($item, $autocomplete)
    {
        $ret = array(
            'autocompleteField' => '',
            'autoField' => ''
        );
        if (isset($item->item_type_id)) {
            $ret['autocompleteField'] = metadata($item, array('Item Type Metadata', $autocomplete['autocomplete_field_name']));
            $ret['autoField'] = metadata($item, array('Item Type Metadata', $autocomplete['auto_field_name']));
        }
        return $ret;
    }

    protected function _sanitizeAutocompleteField($currentValues, $autocomplete, $item, $sigleField = 'Sigle')
    {
        $ret = $currentValues['autocompleteField'];
        if (isset($currentValues['autoField']) && !empty($currentValues['autoField'])) {

            $sourceItem = $this->_db->getTable('Item')->find($currentValues['autoField']);
            if ($sourceItem) {
                $sourceItemSigleValue = metadata($sourceItem, array('Item Type Metadata', $sigleField));
                if (!empty($sourceItemSigleValue) && $sourceItemSigleValue !== $currentValues['autocompleteField']) {
                    // first the soft update
                    $ret = $sourceItemSigleValue;
                    // Now the db update
                    $update['Elements'][$autocomplete->autocomplete_field_id][0]['text'] = $sourceItemSigleValue;
                    $item->beforeSaveElements($update);
                    $item->save();

                }
            } else {
                $ret = '';
                echo '<script>'
                    . '(function($) {'
                    . '$("<div />").html("'
                    . '<p><spam style=\"color:#f30\">Fehler:</spam> Die &quot;Sigle Quelle&quot; konnte der &quot;Sigle Quelle ID&quot; nicht mehr zugeordnet werden!</p>'
                    . '<p>Bitte geben Sie bei &quot;Sigle Quelle&quot; eine gültige Sigle an!</p>'
                    . '<p>Der aktuelle Wert ist:<br><code>' . $currentValues['autocompleteField'] . '</code></p>'
                    . '<p>Sie können bei der &quot;Sigle Quelle&quot; auch nach dem gleichen Wert suchen.</p>'
                    . '").dialog({'
                    . ' modal:true,
                        buttons: [{
                            text: "OK",
                            click: function() {
                                $(this).dialog("close");
                                $("#Elements-75-0-text").val("' . $currentValues['autocompleteField'] . '").focus().trigger("input");
                                $([document.documentElement, document.body]).animate({
                                    scrollTop: $("#element-75").offset().top - 60
                                }, 1000);
                            },
                        }],
                        open: function() {
                            $("button", $(this).siblings(".ui-dialog-buttonpane")).focus();
                        }
                    });'
                    . '}(jQuery));</script>';
            }
        }
        return $ret;
    }
}
