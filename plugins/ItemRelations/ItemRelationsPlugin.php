<?php
/**
 * Item Relations
 * @copyright Copyright 2010-2014 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Item Relations plugin.
 */
class ItemRelationsPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'config',
        'config_form',
        'define_acl',
        'initialize',
        'after_save_item',
        'admin_head',
        'admin_items_show_sidebar',
        'admin_items_search',
        'admin_items_batch_edit_form',
        'items_batch_edit_custom',
        'public_items_show',
        'items_browse_sql',
        'define_routes'
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_items_form_tabs',
        'admin_navigation_main',
        'api_resources',
        'api_extend_items'
    );

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'item_relations_public_append_to_items_show' => 1,
        'item_relations_relation_format' => 'prefix_local_part'
    );

    protected static $_post = null;
    protected $_elementsCache = [];

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Create tables.
        $db = $this->_db;

        $sql = "
        CREATE TABLE IF NOT EXISTS `$db->ItemRelationsVocabulary` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `description` text,
            `namespace_prefix` varchar(100) NOT NULL,
            `namespace_uri` varchar(200) DEFAULT NULL,
            `custom` BOOLEAN NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $db->query($sql);

        $sql = "
        CREATE TABLE IF NOT EXISTS `$db->ItemRelationsProperty` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `vocabulary_id` int(10) unsigned NOT NULL,
            `local_part` varchar(100) NOT NULL,
            `label` varchar(100) DEFAULT NULL,
            `description` text,
            `autocpmplete_item_type_ids` varchar(255) COLLATE 'utf8_unicode_ci' NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $db->query($sql);

        $sql = "
        CREATE TABLE IF NOT EXISTS `$db->ItemRelationsRelation` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `subject_item_id` int(10) unsigned NOT NULL,
            `property_id` int(10) unsigned NOT NULL,
            `object_item_id` int(10) unsigned NOT NULL,
            `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $db->query($sql);

        $sql = "
        CREATE TABLE IF NOT EXISTS `$db->ItemRelationsAnnotation` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `relation_id` int(11) unsigned NOT NULL,
          `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `user_id` int(11) NOT NULL,
          `annotation` longtext COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);

        // Install the formal vocabularies and their properties.
        $formalVocabularies = include 'formal_vocabularies.php';
        foreach ($formalVocabularies as $formalVocabulary) {
            $vocabulary = new ItemRelationsVocabulary;
            $vocabulary->name = $formalVocabulary['name'];
            $vocabulary->description = $formalVocabulary['description'];
            $vocabulary->namespace_prefix = $formalVocabulary['namespace_prefix'];
            $vocabulary->namespace_uri = $formalVocabulary['namespace_uri'];
            $vocabulary->custom = 0;
            $vocabulary->save();

            $vocabularyId = $vocabulary->id;

            foreach ($formalVocabulary['properties'] as $formalProperty) {
                $property = new ItemRelationsProperty;
                $property->vocabulary_id = $vocabularyId;
                $property->local_part = $formalProperty['local_part'];
                $property->label = $formalProperty['label'];
                $property->description = $formalProperty['description'];
                $property->save();
            }
        }

        // Install a custom vocabulary.
        $customVocabulary = new ItemRelationsVocabulary;
        $customVocabulary->name = 'Custom';
        $customVocabulary->description = 'Custom vocabulary containing relations defined for this Omeka instance.';
        $customVocabulary->namespace_prefix = ''; // cannot be NULL
        $customVocabulary->namespace_uri = null;
        $customVocabulary->custom = 1;
        $customVocabulary->save();

        $this->_installOptions();
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $db = $this->_db;

        // Drop the vocabularies table.
        $sql = "DROP TABLE IF EXISTS `$db->ItemRelationsVocabulary`";
        $db->query($sql);

        // Drop the properties table.
        $sql = "DROP TABLE IF EXISTS `$db->ItemRelationsProperty`";
        $db->query($sql);

        // Drop the relations table.
        $sql = "DROP TABLE IF EXISTS `$db->ItemRelationsRelation`";
        $db->query($sql);

        $this->_uninstallOptions();
    }

   /**
     * Display the plugin configuration form.
     */
    public static function hookConfigForm()
    {
        $publicAppendToItemsShow = get_option('item_relations_public_append_to_items_show');
        $relationFormat = get_option('item_relations_relation_format');

        require dirname(__FILE__) . '/config_form.php';
    }

    /**
     * Handle the plugin configuration form.
     */
    public static function hookConfig()
    {
        set_option('item_relations_public_append_to_items_show',
            (int)(boolean) $_POST['item_relations_public_append_to_items_show']);
        set_option('item_relations_relation_format',
            $_POST['item_relations_relation_format']);
    }

    /**
     * Upgrade the plugin.
     *
     * @param array $args
     */
    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $db = $this->_db;
        if ($oldVersion <= '1.1') {
            $sql = "
            INSERT INTO `{$db->ItemRelationsProperty}`
            (`vocabulary_id`, `local_part`, `label`, `description`)
            VALUES
            (1, 'abstract', 'Abstract', 'A summary of the resource.'),
            (1, 'accessRights', 'Access Rights', 'Information about who can access the resource or an indication of its security status.'),
            (1, 'accrualMethod', 'Accrual Method', 'The method by which items are added to a collection.'),
            (1, 'accrualPeriodicity', 'Accrual Periodicity', 'The frequency with which items are added to a collection.'),
            (1, 'accrualPolicy', 'Accrual Policy', 'The policy governing the addition of items to a collection.'),
            (1, 'audience', 'Audience', 'A class of entity for whom the resource is intended or useful.'),
            (1, 'contributor', 'Contributor', 'An entity responsible for making contributions to the resource.'),
            (1, 'coverage', 'Coverage', 'The spatial or temporal topic of the resource, the spatial applicability of the resource, or the jurisdiction under which the resource is relevant.'),
            (1, 'creator', 'Creator', 'An entity primarily responsible for making the resource.'),
            (1, 'description', 'Description', 'An account of the resource.'),
            (1, 'educationLevel', 'Audience Education Level', 'A class of entity, defined in terms of progression through an educational or training context, for which the described resource is intended.'),
            (1, 'extent', 'Extent', 'The size or duration of the resource.'),
            (1, 'format', 'Format', 'The file format, physical medium, or dimensions of the resource.'),
            (1, 'instructionalMethod', 'Instructional Method', 'A process, used to engender knowledge, attitudes and skills, that the described resource is designed to support.'),
            (1, 'language', 'Language', 'A language of the resource.'),
            (1, 'license', 'License', 'A legal document giving official permission to do something with the resource.'),
            (1, 'mediator', 'Mediator', 'An entity that mediates access to the resource and for whom the resource is intended or useful.'),
            (1, 'medium', 'Medium', 'The material or physical carrier of the resource.'),
            (1, 'provenance', 'Provenance', 'A statement of any changes in ownership and custody of the resource since its creation that are significant for its authenticity, integrity, and interpretation.'),
            (1, 'publisher', 'Publisher', 'An entity responsible for making the resource available.'),
            (1, 'rights', 'Rights', 'Information about rights held in and over the resource.'),
            (1, 'rightsHolder', 'Rights Holder', 'A person or organization owning or managing rights over the resource.'),
            (1, 'spatial', 'Spatial Coverage', 'Spatial characteristics of the resource.'),
            (1, 'subject', 'Subject', 'The topic of the resource.'),
            (1, 'tableOfContents', 'Table Of Contents', 'A list of subunits of the resource.'),
            (1, 'temporal', 'Temporal Coverage', 'Temporal characteristics of the resource.'),
            (1, 'type', 'Type', 'The nature or genre of the resource.')";
            $db->query($sql);
        }

        if ($oldVersion <= '2.0') {
            // Fix un-upgraded old table name if present.
            $correctTableName = (bool) $db->fetchOne("SHOW TABLES LIKE '{$db->ItemRelationsRelation}'");
            if (!$correctTableName) {
                $sql = "RENAME TABLE `{$db->prefix}item_relations_item_relations` TO `{$db->ItemRelationsRelation}`";
                $db->query($sql);
            }
        }
    }

   /**
     * Add the translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    /**
     * Define the ACL.
     *
     * @param array $args
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];

        $indexResource = new Zend_Acl_Resource('ItemRelations_Index');
        $vocabResource = new Zend_Acl_Resource('ItemRelations_Vocabularies');
        $acl->add($indexResource);
        $acl->add($vocabResource);
    }

    /**
     * Display item relations on the public items show page.
     */
    public function hookPublicItemsShow() {
        if (get_option('item_relations_public_append_to_items_show')) {
            $item = get_current_record('item');

            echo common('item-relations-show', array(
                'subjectRelations' => self::prepareSubjectRelations($item),
                'objectRelations' => self::prepareObjectRelations($item)
            ));
        }
    }

    /**
     * Display item relations on the admin items show page.
     *
     * @param Item $item
     */
    public function hookAdminItemsShowSidebar($args)
    {
        $item = $args['item'];

        echo common('item-relations-show', array(
            'subjectRelations' => self::prepareSubjectRelations($item),
            'objectRelations' => self::prepareObjectRelations($item)
        ));
    }

    /**
     * Display the item relations form on the admin advanced search page.
     */
    public function hookAdminItemsSearch()
    {
        echo common('item-relations-advanced-search', array(
            'formSelectProperties' => get_table_options('ItemRelationsProperty'))
        );
    }

    /**
     * Save the item relations after saving an item add/edit form.
     *
     * @param array $args
     */
    public function hookAfterSaveItem($args)
    {
        if (!$args['post']) {
            return;
        }

        // var_dump($args); die();

        $record = $args['record'];
        $post = $args['post'];

        self::$_post = $post;
        // var_dump($post, $record); die();
        // $view = Zend_Registry::get('view');

        $db = $this->_db;

        // Update Annotations
        if (isset($post['item_relations_annotation'])) {
            foreach ($post['item_relations_annotation'] as $itemRelationId => $annotation) {
                self::updateAnnotation($itemRelationId, $annotation);
            }
        }

        // Save item relations.
        if (isset($post['item_relations_property_id'])) {
            foreach ($post['item_relations_property_id'] as $key => $propertyId) {
                self::insertItemRelation(
                    $record,
                    $propertyId,
                    $post['item_relations_item_relation_object_item_id'][$key]
                );
            }
        }

        // Delete item relations.
        if (isset($post['item_relations_item_relation_delete'])) {
            foreach ($post['item_relations_item_relation_delete'] as $itemRelationId) {
                $itemRelation = $db->getTable('ItemRelationsRelation')->find($itemRelationId);
                // When an item is related to itself, deleting both relations
                // simultaneously will result in an error. Prevent this by
                // checking if the item relation exists prior to deletion.
                if ($itemRelation) {
                    // $itemRelation->delete();
                    $itemRelation->state = 'deleted';
                    $itemRelation->save();
                }
            }
        }
    }

    /**
     * Filter for an item relation after search page submission.
     *
     * @param array $args
     */
    public function hookItemsBrowseSql($args)
    {
        $select = $args['select'];
        $params = $args['params'];

        if (isset($params['item_relations_property_id'])
            && is_numeric($params['item_relations_property_id'])
        ) {
            $db = $this->_db;
            // Set the field on which to join.
            if (isset($params['item_relations_clause_part'])
                && $params['item_relations_clause_part'] == 'object'
            ) {
                $onField = 'object_item_id';
            } else {
                $onField = 'subject_item_id';
            }
            $select
                ->join(
                    array('item_relations_relations' => $db->ItemRelationsRelation),
                    "item_relations_relations.$onField = items.id",
                    array()
                )
                ->where('item_relations_relations.property_id = ?',
                    $params['item_relations_property_id']
                );
        }
    }

    public function hookAdminHead()
    {
        queue_css_file('relations');
    }

    /**
     * Add custom fields to the item batch edit form.
     */
    public function hookAdminItemsBatchEditForm()
    {
        $formSelectProperties = get_table_options('ItemRelationsProperty');
?>
<fieldset id="item-relation-fields">
<h2><?php echo __('Item Relations'); ?></h2>
<table>
    <thead>
    <tr>
        <th><?php echo __('Subjects'); ?></th>
        <th><?php echo __('Relation'); ?></th>
        <th><?php echo __('Object');  ?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?php echo __('These Items'); ?></td>
        <td><?php echo get_view()->formSelect('custom[item_relations_property_id]', null, array(), $formSelectProperties); ?></td>
        <td>
            <?php echo __('Item ID'); ?>
            <?php echo get_view()->formText('custom[item_relations_item_relation_object_item_id]', null, array('size' => 6)); ?>
        </td>
    </tr>
    </tbody>
</table>
</fieldset>
<?php
    }

    /**
     * Process the item batch edit form.
     *
     * @param array $args
     */
    public function hookItemsBatchEditCustom($args)
    {
        $item = $args['item'];
        $custom = $args['custom'];

        self::insertItemRelation(
            $item,
            $custom['item_relations_property_id'],
            $custom['item_relations_item_relation_object_item_id']
        );
    }

    /**
     * Add the routes from routes.ini in this plugin folder.
     *
     * @param array $args Router object in 'router' key
     */
    function hookDefineRoutes($args)
    {
        $router = $args['router'];
        $router->addConfig(new Zend_Config_Ini(dirname(__FILE__) .
            DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
    }

    /**
     * Add the Item Relations link to the admin main navigation.
     *
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Item Relations'),
            'uri' => url('item-relations'),
            'resource' => 'ItemRelations_Index',
            'privilege' => 'index'
        );
        return $nav;
    }

    /**
     * Add the "Item Relations" tab to the admin items add/edit page.
     *
     * @return array
     */
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        $item = $args['item'];

        $formSelectProperties = get_table_options('ItemRelationsProperty');
        $subjectRelations = self::prepareSubjectRelations($item, 'all');
        $objectRelations = self::prepareObjectRelations($item, 'all');
        $annotations = self::prepareAnnotations($subjectRelations, $objectRelations);
        // var_dump($annotations);

        ob_start();
        include 'item_relations_form.php';
        $content = ob_get_contents();
        ob_end_clean();

        $tabs['Item Relations'] = $content;
        return $tabs;
    }

    /**
     * Prepare Annotations
     *
     * @param obj $subjectRelations
     * @param obj $objectRelations
     * @return array $annotations
     */
    public static function prepareAnnotations($subjectRelations, $objectRelations)
    {
        $annotations = array('subjectRelations' => array(), 'objectRelations' => array());
        foreach ($subjectRelations as $subjectRelation) {
            $annotation = get_db()->getTable('ItemRelationsAnnotation')->findByCurrentByRelation($subjectRelation['item_relation_id']);
            // if ($annotation) {
            //     $annotation->setUser();
            // }
            $annotations['subjectRelations'][$subjectRelation['item_relation_id']] = $annotation;
        }
        foreach ($objectRelations as $objectRelation) {
            $annotation = get_db()->getTable('ItemRelationsAnnotation')->findByCurrentByRelation($objectRelation['item_relation_id']);
            // if ($annotation) {
            //     $annotation->setUser();
            // }
            $annotations['objectRelations'][$objectRelation['item_relation_id']] = $annotation;
        }
        return $annotations;
    }


    /**
     * Prepare subject item relations for display.
     *
     * @param Item $item
     * @return array
     */
    public static function prepareSubjectRelations(Item $item, $state = 'current')
    {
        $subjects = get_db()->getTable('ItemRelationsRelation')->findBySubjectItemId($item->id, $state);
        $subjectRelations = array();

        foreach ($subjects as $subject) {
            if (!($item = get_record_by_id('item', $subject->object_item_id))) {
                continue;
            }
            $subjectRelations[] = array(
                'item_relation_id' => $subject->id,
                'object_item_id' => $subject->object_item_id,
                'object_item_title' => self::getItemTitle($item),
                'relation_text' => $subject->getPropertyText(),
                'relation_description' => $subject->property_description,
                'state' => $subject->state
            );
        }
        return $subjectRelations;
    }

    /**
     * Prepare object item relations for display.
     *
     * @param Item $item
     * @return array
     */
    public static function prepareObjectRelations(Item $item, $state = 'current')
    {
        $objects = get_db()->getTable('ItemRelationsRelation')->findByObjectItemId($item->id, $state);
        $objectRelations = array();
        foreach ($objects as $object) {
            if (!($item = get_record_by_id('item', $object->subject_item_id))) {
                continue;
            }
            $objectRelations[] = array(
                'item_relation_id' => $object->id,
                'subject_item_id' => $object->subject_item_id,
                'subject_item_title' => self::getItemTitle($item),
                'relation_text' => $object->getPropertyText(),
                'relation_description' => $object->property_description,
                'state' => $object->state
            );
        }
        return $objectRelations;
    }

    /**
     * Return a item's title.
     *
     * @param Item $item The item.
     * @return string
     */
    public static function getItemTitle($item)
    {
        $title = metadata($item, array('Dublin Core', 'Title'), array('no_filter' => true));
        if (!trim($title)) {
            $title = '#' . $item->id;
        }
        return $title;
    }

    /**
     * Insert an item relation.
     *
     * @param Item|int $subjectItem
     * @param int $propertyId
     * @param Item|int $objectItem
     * @return bool True: success; false: unsuccessful
     */
    public static function insertItemRelation($subjectItem, $propertyId, $objectItem)
    {

        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');

        // Only numeric property IDs are valid.
        if (!is_numeric($propertyId)) {
            return false;
        }

        // Set the subject item.
        if (!($subjectItem instanceOf Item)) {
            $subjectItem = get_db()->getTable('Item')->find($subjectItem);
        }

        // Set the object item.
        if (!($objectItem instanceOf Item)) {
            $objectItem = get_db()->getTable('Item')->find($objectItem);
        }

        // Don't save the relation if the subject or object items don't exist.
        if (!$subjectItem || !$objectItem) {
            $flashMessenger->addMessage('FEHLER bei Objekt Beziehungen: Das Subjekt oder das Objekt exisitiert nicht! Beziehung konnte nicht angelegt werden.', 'error');
            return false;
        }

        // Check if we have primary link set
        if ((int) $propertyId === 119) {
            //findBy($params = array(), $limit = null, $page = null)
            $primaryAssignment = get_db()->getTable('ItemRelationsRelation')->findBy(
                array(
                    'property_id' => $propertyId,
                    'object_item_id' => $objectItem->id,
                    'state' => 'current'
                )
            );
            if (isset($primaryAssignment) && !empty($primaryAssignment)) {
                $flashMessenger->addMessage(
                    'FEHLER bei Objekt Beziehungen: Das das Objekt (ID: ' .
                    $objectItem->id .
                    ') hat bereits eine Primärzuweisung (Subjekt ID: ' .
                    $primaryAssignment[0]['subject_item_id'] .
                    '). Ein Shared Objekt kann nur eine Primärzuweisung haben. ' .
                    'Neue Beziehung konnte nicht angelegt werden.', 'error');
            }
            return false;
            // var_dump($primaryAssignment); die();
        }

        $itemRelation = new ItemRelationsRelation;
        $itemRelation->subject_item_id = $subjectItem->id;
        $itemRelation->property_id = $propertyId;
        $itemRelation->object_item_id = $objectItem->id;
        $itemRelation->state = 'current';
        $itemRelation->save();

        self::generateAnnotationFromNewReation($itemRelation);

        return true;
    }

    /**
     * Prepare and persyst new Annotation
     *
     * @param obj $itemRelation
     * @return bool
     */
    public static function generateAnnotationFromNewReation($itemRelation)
    {
        if (!isset(self::$_post) || !is_object(self::$_post)) {
            return false;
        }

        $annotation = null;
        foreach (self::$_post['item_relations_property_id'] as $num => $id) {
            if ((int) $id === (int) $itemRelation->property_id &&
                (int) self::$_post['item_relations_item_relation_object_item_id'][$num] === (int) $itemRelation->object_item_id) {
                    $annotation = self::$_post['item_relations_new_annotation'][$num];
                    break;
            }
        }
        if (!isset($annotation)) {
            return false;
        }

        // $user = Zend_Registry::get('bootstrap')->getResource('CurrentUser');
        // $itemRelationAnnotation = new ItemRelationsAnnotation;
        // $itemRelationAnnotation->relation_id = $itemRelation->id;
        // $itemRelationAnnotation->state = 'current';
        // $itemRelationAnnotation->user_id = $user->id;
        // $itemRelationAnnotation->annotation = $annotation;
        // $itemRelationAnnotation->save();

        $user = Zend_Registry::get('bootstrap')->getResource('CurrentUser');
        return self::insertAnnotation($itemRelation->id, $user->id, $annotation);
    }

    /**
     * Persis Annotation
     * @param type $relationId
     * @param type $userId
     * @param type|string $annotation
     * @param type|string $state
     * @return type
     */
    public static function insertAnnotation($relationId, $userId, $annotation = '', $state = 'current')
    {
        $relationId = (int) $relationId;
        $userId = (int) $userId;

        $itemRelationAnnotation = new ItemRelationsAnnotation;
        $itemRelationAnnotation->relation_id = $relationId;
        $itemRelationAnnotation->state = $state;
        $itemRelationAnnotation->user_id = $userId;
        $itemRelationAnnotation->annotation = $annotation;
        $itemRelationAnnotation->save();
        return true;
    }

    /**
     * Description
     * @param srt $itemRelationId
     * @param str $annotation
     * @return void
     */
    public static function updateAnnotation($itemRelationId, $annotation)
    {
        $currentAnnation =  get_db()->getTable('ItemRelationsAnnotation')->findByCurrentByRelation($itemRelationId);
        $user = Zend_Registry::get('bootstrap')->getResource('CurrentUser');
        if ($currentAnnation) {
            if ($annotation !== $currentAnnation->annotation) {
                $currentAnnation->state = 'archive';
                $currentAnnation->save();
                self::insertAnnotation($itemRelationId, $user->id, $annotation);
            }
        } else {
            self::insertAnnotation($itemRelationId, $user->id, $annotation);
        }
    }


    public function filterApiResources($apiResources)
    {
        $apiResources['linked_objects'] = array(
            'record_type' => 'ItemRelations',
            'actions' => array('get', 'index'),
            'index_params' => array('object_item_id', 'show_related')
        );
        $apiResources['linked_subjects'] = array(
            'record_type' => 'ItemRelations',
            'actions' => array('get', 'index'),
            'index_params' => array('subject_item_id', 'show_related')
        );
        return $apiResources;
    }

    public function filterApiExtendItems($extend, $args)
    {
        $item = $args['record'];

        $objectRelationsCount = 0;
        $apiReltedObjects = array();

        $objectRelations = self::prepareObjectRelations($item);
        $objectRelationsCount = count($objectRelations);

        // array (size=1)
        //     0 =>
        //         array (size=6)
        //         'item_relation_id' => int 90
        //         'subject_item_id' => int 928
        //         'subject_item_title' => string 'Lepke 1934, Winternitz, Trio' (length=28)
        //         'relation_text' => string 'Primärzuweisung' (length=16)
        //         'relation_description' => string 'Zuweisung bei Instanziierung eines Shared Object' (length=48)
        //         'state' => string 'current' (length=7)

        if (isset($objectRelations) && !empty($objectRelations) && isset($objectRelationsCount) && $objectRelationsCount > 0) {
            foreach ($objectRelations as $relatedObject) {
                if ($relatedObject['state'] === 'current') {
                    $representations = array();
                    $subjectItemType = '';
                    $files = '';
                    if (!isset($_GET['public']) || empty($_GET['public']) || $_GET['public'] !== '1') {
                        $subjectItem = get_db()->getTable('Item')->find($relatedObject['subject_item_id']);
                        // @see application\libraries\Omeka\Record\Api\AbstractRecordAdapter.php
                        // and application\models\Api\Item.php
                        if (is_object($subjectItem)) {
                            // var_dump($subjectItem->getItemType());
                            $subjectItemType = $subjectItem->getItemType();
                            if (isset($subjectItemType) && !empty($subjectItemType) && $subjectItemType['name'] === 'Bilddokument') {
                                $files = $subjectItem->getFiles();
                            }

                            foreach ($subjectItem->getAllElementTexts() as $elementText) {
                                if (!isset($this->_elementsCache[$elementText->element_id])) {
                                    $element = get_db()->getTable('Element')->find($elementText->element_id);
                                    if (!$element) {
                                        continue;
                                    }
                                    $this->_elementsCache[$element->id] = array(
                                        'id' => $element->id,
                                        'element_set_id' => $element->element_set_id,
                                        'name' => $element->name,
                                    );
                                }
                                $element = $this->_elementsCache[$elementText->element_id];
                                $representation = array(
                                    'html' => (bool) $elementText->html,
                                    'text' => $elementText->text,
                                    'element' => array(
                                        'id' => $elementText->element_id,
                                        'url' => Omeka_Record_Api_AbstractRecordAdapter::getResourceUrl('/elements/' . $elementText->element_id),
                                        'name' => $element['name'],
                                        'resource' => 'elements',
                                    ),
                                );
                                $representations[] = $representation;
                            }
                        }
                    }
                    $apiReltedObjects[] = array(
                        'id' => $relatedObject['subject_item_id'],
                        'url' => Omeka_Record_Api_AbstractRecordAdapter::getResourceUrl('/items/' . $relatedObject['subject_item_id']),
                        'item_relation_id' => $relatedObject['item_relation_id'],
                        'subject_item_title' => $relatedObject['subject_item_title'],
                        'subject_item_type' => $subjectItemType,
                        'subject_item_files' => $files,
                        'relation_text' => $relatedObject['relation_text'],
                        'relation_description' => $relatedObject['relation_description'],
                        'subject_element_texts' => $representations,
                        // 'state' => $relatedObject['state']
                    );
                }
            }
        } else {
            $objectRelationsCount = 0;
        }

        // Always display resource in API (otherwhise move inside condition)
        $extend['linked_objects'] = array(
            'count' => $objectRelationsCount,
            'url' => Omeka_Record_Api_AbstractRecordAdapter::getResourceUrl('/item-relations?object_item_id=' . $item->id),
            'resource' => 'linked_objects',
            'items' => $apiReltedObjects,
        );

        $subjectRelationsCount = 0;
        $apiReltedSubjects = array();
        $subjectRelations = self::prepareSubjectRelations($item);
        $subjectRelationsCount = count($subjectRelations);

        // var_dump($subjectRelationsCount);

        // array (size=6)
        //     'item_relation_id' => int 90
        //     'object_item_id' => int 830
        //     'object_item_title' => string 'Winternitz, Trio' (length=16)
        //     'relation_text' => string 'Primärzuweisung' (length=16)
        //     'relation_description' => string 'Zuweisung bei Instanziierung eines Shared Object' (length=48)
        //     'state' => string 'current' (length=7)

        if (isset($subjectRelations) && !empty($subjectRelations) && isset($subjectRelationsCount) && $subjectRelationsCount > 0) {
            foreach ($subjectRelations as $relatedSubject) {

                // var_dump($relatedSubject);

                if ($relatedSubject['state'] === 'current') {
                    $objectItem = get_db()->getTable('Item')->find($relatedSubject['object_item_id']);
                    $representations = array();
                    $objectItemType = '';
                    // $files = '';
                    if (is_object($objectItem)) {

                        $objectItemType = $objectItem->getItemType();

                        // if (isset($objectItemType) && !empty($objectItemType) && $objectItemType['name'] === 'Bilddokument') {
                        //     $files = $objectItem->getFiles();
                        // }


                        // @see application\libraries\Omeka\Record\Api\AbstractRecordAdapter.php
                        // and application\models\Api\Item.php
                        // var_dump($objectItem);

                        foreach ($objectItem->getAllElementTexts() as $elementText) {
                            if (!isset($this->_elementsCache[$elementText->element_id])) {
                                $element = get_db()->getTable('Element')->find($elementText->element_id);
                                if (!$element) {
                                    continue;
                                }
                                $this->_elementsCache[$element->id] = array(
                                    'id' => $element->id,
                                    'element_set_id' => $element->element_set_id,
                                    'name' => $element->name,
                                );
                            }
                            $element = $this->_elementsCache[$elementText->element_id];
                            $representation = array(
                                'html' => (bool) $elementText->html,
                                'text' => $elementText->text,
                                'element' => array(
                                    'id' => $elementText->element_id,
                                    'url' => Omeka_Record_Api_AbstractRecordAdapter::getResourceUrl('/elements/' . $elementText->element_id),
                                    'name' => $element['name'],
                                    'resource' => 'elements',
                                ),
                            );
                            $representations[] = $representation;
                        }
                        $apiReltedSubjects[] = array(
                            'id' => $relatedSubject['object_item_id'],
                            'url' => Omeka_Record_Api_AbstractRecordAdapter::getResourceUrl('/items/' . $relatedSubject['object_item_id']),
                            'item_relation_id' => $relatedSubject['item_relation_id'],
                            'object_item_title' => $relatedSubject['object_item_title'],
                            'object_item_type' => $objectItemType,
                            // 'object_files' => $files,
                            'relation_text' => $relatedSubject['relation_text'],
                            'relation_description' => $relatedSubject['relation_description'],
                            'object_element_texts' => $representations,
                            // 'state' => $relatedObject['state']
                        );
                    }
                }
            }
        } else {
            $subjectRelationsCount = 0;
        }

        // Always display resource in API (otherwhise move inside condition)
        $extend['linked_subjects'] = array(
            'count' => $subjectRelationsCount,
            'url' => Omeka_Record_Api_AbstractRecordAdapter::getResourceUrl('/item-relations?subject_item_id=' . $item->id),
            'resource' => 'linked_subjects',
            'items' => $apiReltedSubjects,
        );

        return $extend;

    }

}
