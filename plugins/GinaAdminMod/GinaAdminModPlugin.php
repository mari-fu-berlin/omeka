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
 * The ImageConvert plugin.
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
        // 'admin_items_form_item_types',
        // 'admin_items_panel_fields',
        // 'admin_footer_last',
    );

    protected $_filters = array(
        'admin_navigation_main',
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
            if ($nav['label'] == __('Items') && substr($nav['uri'], -6) == '/items') {
                if (isset($this->itemTypes) && is_array($this->itemTypes)) {
                    foreach ($this->itemTypes as $itemType) {
                        $new[] = array(
                            'label' => __('Objekte') . ' - ' . __($itemType->name),
                            'uri' => url('/items/browse?type=' . $itemType->id),
                        );
                    }
                }
            }
        }
        return $new;
    }


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


}
