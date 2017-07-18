<?php
/**
 * Spreadsheet
 *
 * @copyright Copyright 2017 Viktor Grandgeorg, Grandgeorg Websolutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Spreadsheet plugin.
 *
 * @package Omeka\Plugins\Spreadsheet
 */
class SpreadsheetPlugin extends Omeka_Plugin_AbstractPlugin
{

    protected $_hooks = array(
        'install',
        'uninstall',
        'initialize',
        'define_acl'
    );

    protected $_filters = array(
        'admin_navigation_main'
    );

    public function hookInstall()
    {
        $db  = get_db();

        $db->query("
        CREATE TABLE `{$db->SpreadsheetExport}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL DEFAULT '',
            `options` longtext NOT NULL,
            `created_by_user_id` int(11) unsigned NOT NULL,
            `modified_by_user_id` int(11) unsigned NOT NULL,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `inserted` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");

        $db->query("
        CREATE TABLE `{$db->SpreadsheetImport}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL DEFAULT '',
            `options` longtext NOT NULL,
            `created_by_user_id` int(11) unsigned NOT NULL,
            `modified_by_user_id` int(11) unsigned NOT NULL,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `inserted` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");

    }

    public function hookUninstall()
    {
        $db = get_db();
        $db->query("DROP TABLE IF EXISTS `{$db->SpreadsheetExport}`;");
        $db->query("DROP TABLE IF EXISTS `{$db->SpreadsheetImport}`;");

        $this->_uninstallOptions();
    }

    public function hookInitialize()
    {
        // Register the select filter controller plugin.
        // $front = Zend_Controller_Front::getInstance();
        // $front->registerPlugin(new SimpleVocab_Controller_Plugin_SelectFilter);
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('Spreadsheet');
        // $acl->allow(null, 'Spreadsheet');
        $acl->allow(array('super', 'admin'), array('Spreadsheet'));
        $acl->deny(null, 'Spreadsheet');
    }

    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label'     => __('Spreadsheets'),
            'uri'       => url('spreadsheet'),
            'resource'  => 'Spreadsheet',
            'privilege' => 'browse'
        );
        return $nav;
    }
}
