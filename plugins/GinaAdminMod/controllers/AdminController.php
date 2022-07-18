<?php
/**
 * Admin Mod
 *
 * @copyright Copyright 2017 Grandgeorg Websolutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Admin Mod admin controller class.
 *
 * @package GinaAdminMod
 */
class GinaAdminMod_AdminController extends Omeka_Controller_AbstractActionController
{
    public function init() {}

    public function indexAction() { }

    /**
     * @url: /admin/gina-admin-mod/admin/autocomplete-show
     */
    public function autocompleteShowAction()
    {
        $params = $this->getAllParams();
        $db = get_db();
        $autocompleteTable = $db->getTable('ItemAutocomplete');
        $autocompletes = $autocompleteTable->findAll();
        $this->view->autocompletes = $autocompletes;
    }

    /**
     * @url: /admin/gina-admin-mod/admin/autocomplete-add
     */
    public function autocompleteAddAction()
    {
        $db = get_db();
        $params = $this->getAllParams();
        $itemTypes = $db->getTable('ItemType')->findPairsForSelectForm();
        $elementDbTbl = $db->getTable('Element');
        $elements = array();
        foreach ($itemTypes as $itemTypeId => $itemTypeName) {
            $elemetsTemps = $elementDbTbl->findByItemType($itemTypeId);
            foreach ($elemetsTemps as $elemetsTemp) {
                $elements[$itemTypeId][$elemetsTemp->id] = $elemetsTemp->name;
            }
        }

        $this->view->elements = json_encode($elements);

        $autocompleteFieldIds = array();
        if (isset($params['item_type_id']) && isset($params['autocomplete_field_id'])) {
            $autocompleteFieldIds = $elements[$params['item_type_id']];
        }
        $autoFieldIds = array();
        if (isset($params['item_type_id']) && isset($params['auto_field_id'])) {
            $autoFieldIds = $elements[$params['item_type_id']];
        }

        $this->view->form = $this->getAutocompleteEditForm($itemTypes, $autocompleteFieldIds, $autoFieldIds);
        if ($this->getRequest()->isPost() && $this->view->form->isValid($_POST) && isset($params['autocompletesave'])) {
            if(
                !isset($params['item_type_id']) || empty($params['item_type_id']) ||
                !isset($params['autocomplete_field_id']) || empty($params['autocomplete_field_id']) ||
                !isset($params['auto_field_id']) || empty($params['auto_field_id']) ||
                !isset($params['autocomplete_item_type_ids']) || empty($params['autocomplete_item_type_ids'])
            ) {
                $this->_helper->flashMessenger('Sie müssen in allen Feldern etwas auswählen', 'error');
            } else {
                $insert = new ItemAutocomplete();
                $insert->item_type_id = (int) $params['item_type_id'];
                $insert->autocomplete_field_id = (int) $params['autocomplete_field_id'];
                $insert->autocomplete_field_name = $elements[$params['item_type_id']][$params['autocomplete_field_id']];
                $insert->auto_field_id = (int) $params['auto_field_id'];
                $insert->auto_field_name = $elements[$params['item_type_id']][$params['auto_field_id']];
                $insert->autocomplete_item_type_ids = implode(',', $params['autocomplete_item_type_ids']);
                $insert->save();
                $this->_helper->flashMessenger('Autovervollständigen-Feld erfolgreich hinzugefügt', 'success');
                $this->_helper->redirector('autocomplete-show', 'admin', 'gina-admin-mod');
            }

        }
    }

    /**
     * Get AutocompleteEditForm
     *
     * @param array $itemTypes
     * @param array $autocompleteFieldIds
     * @param array $autoFieldIds
     * @return object Omeka Form
     */
    protected function getAutocompleteEditForm($itemTypes, $autocompleteFieldIds = array(), $autoFieldIds = array())
    {
        $form = new Omeka_Form;
        $form->setMethod('post');
        $form->setAttrib('id', 'autocomplete-edit');

        $form->addElement('select', 'item_type_id', array(
            'label' => 'Objekttyp',
            'multiOptions' => (array(0 => '') + $itemTypes)
        ));
        $form->addElement('select', 'autocomplete_field_id', array(
            'label' => 'Autovervollständigen-Feld',
            'multiOptions' => $autocompleteFieldIds
        ));
        $form->addElement('select', 'auto_field_id', array(
            'label' => 'Autovervollständigen-ID-Feld',
            'multiOptions' => $autoFieldIds
        ));
        $form->addElement('multiselect', 'autocomplete_item_type_ids', array(
            'label' => 'Suche in',
            'multiOptions' => $itemTypes
        ));
        $form->addDisplayGroup(
            array(
                'item_type_id',
                'autocomplete_field_id',
                'auto_field_id',
                'autocomplete_item_type_ids'
            ),
            'autocomplete-definitions',
            array('legend' => 'Autocomplete Definition')
        );
        $form->addElement('submit', 'autocomplete-save', array(
            'label' => 'Speichern'
        ));
        return $form;
    }

    public function sanitizeItemsAction()
    {
        $db = get_db();
        $autocompleteTable = $db->getTable('ItemAutocomplete');
        $itemTable = $db->getTable('Item');
        $elementTextTable = $db->getTable('ElementText');
        $autocompletes = $autocompleteTable->findAll();
        $msg = array(
            'ok' => array(),
            'change-text' => array(),
            'set-text' => array(),
            'set-id' => array(),
            'warn' => array(),
            'empty' => array(),
            'warn-notext' => array(),
            'warn-bad-id' => array(),
        );
        $elementSigleId = 62;

        foreach ($autocompletes as $autocomplete) {

            $items = $itemTable->findBy(array('item_type_id' => $autocomplete->item_type_id));
            foreach ($items as $item) {

                $select = $elementTextTable->getSelect()
                    ->where('record_id = ?', $item->id)
                    ->where('record_type = ?', 'Item')
                    ->where('element_id = ?', $autocomplete->autocomplete_field_id);
                $autoCompField = $elementTextTable->fetchObject($select);

                $select = $elementTextTable->getSelect()
                    ->where('record_id = ?', $item->id)
                    ->where('record_type = ?', 'Item')
                    ->where('element_id = ?', $autocomplete->auto_field_id);
                $autoField = $elementTextTable->fetchObject($select);

                if (isset($autoCompField) && isset($autoField)) {
                    // We have BOTH fields, so let's see, if the auto field matches

                    $sigleText = $this->_getSigleElementText($db, $elementTextTable, $autoField, $elementSigleId);
                    // var_dump($sigleText);die();
                    if (!isset($sigleText) || empty($sigleText) || $sigleText === false) {
                        $msg['warn-bad-id'][] = $item->id;
                    } elseif ($sigleText === $autoCompField->text) {
                        // fields match
                        $msg['ok'][] = $item->id;
                    } else {
                        // fields do not match update autocomp. field
                        $autoCompField->text = $sigleText;
                        $autoCompField->save();
                        $msg['change-text'][] = $item->id;
                    }
                } elseif (isset($autoCompField)) {
                    // we ONLY have the autocmp. field so let's see, if we can find a single sigle for it

                    $select = $db->select()
                        ->from(
                            array('element' => $db->ElementText),
                            array('id', 'record_id', 'element_id', 'text')
                        )
                        ->join(
                            array('item' => $db->Item),
                            'element.record_id = item.id',
                            array('item_type_id')
                        )
                        ->join(
                            array('item_type' => $db->ItemType),
                            'item.item_type_id = item_type.id',
                            array('name')
                        )
                        ->where('element.element_id = ?', $elementSigleId)
                        ->where('element.text = ?', $autoCompField->text)
                        ->where('item.item_type_id IN (?)', $autocomplete->autocomplete_item_type_ids)
                    ;
                    $results = $db->fetchAll($select);

                    switch (count($results)) {
                        case 0:
                            $msg['warn'][] = $item->id;
                            break;
                        case 1:
                            $result = $results[0];
                            $modelElementText = new ElementText();
                            $modelElementText->record_id = $item->id;
                            $modelElementText->record_type = 'Item';
                            $modelElementText->element_id = $autocomplete->auto_field_id;
                            $modelElementText->html = 0;
                            $modelElementText->text = $result['record_id'];
                            $modelElementText->save();
                            $msg['set-id'][] = $item->id;
                            break;
                        default:
                            $msg['warn'][] = $item->id;
                            break;
                    }

                } elseif (isset($autoField)) {
                    // we ONLY have the auto field, sol let's insert an autocmp. field

                    $sigleText = $this->_getSigleElementText($db, $elementTextTable, $autoField, $elementSigleId);

                    if (isset($sigleText) && !empty($sigleText) && $sigleText !== false) {
                        $modelElementText = new ElementText();
                        $modelElementText->record_id = $item->id;
                        $modelElementText->record_type = 'Item';
                        $modelElementText->element_id = $autocomplete->autocomplete_field_id;
                        $modelElementText->html = 0;
                        $modelElementText->text = $sigleText;
                        $modelElementText->save();
                        $msg['set-text'][] = $item->id;
                    } else {
                        $msg['warn-notext'][] = $item->id;
                    }

                } else {
                    $msg['empty'][] = $item->id;
                }
            }
        }
        // var_dump($msg);
        $this->view->msg = $msg;
    }

    protected function _getSigleElementText($db, $elementTextTable, $autoField, $elementSigleId)
    {
        $alias = $elementTextTable->getTableAlias();
        $select = new Omeka_Db_Select($db->getAdapter());
        $select
            ->from(array($alias => $elementTextTable->getTableName()), 'text')
            ->where('record_id = ?', $autoField->text)
            ->where('record_type = ?', 'Item')
            ->where('element_id = ?', $elementSigleId);
        return $db->fetchOne($select);
    }

    /**
     * @url: /admin/gina-admin-mod/admin/add-secondary-item-relations
     */
    public function addSecondaryItemRelationsAction()
    {
        $log = array();
        $db = get_db();
        // Get the item relations properties
        $select = new Omeka_Db_Select($db->getAdapter());
        $select
        ->from(
            array('item_relations_property' => $db->ItemRelationsProperty),
            array(
                'label' => 'item_relations_property.label',
                'property_id' => 'item_relations_property.id'
            )
        )
        ->joinLeft(
            array('item_relations_vocabulary' => $db->ItemRelationsVocabulary),
            'item_relations_property.vocabulary_id = item_relations_vocabulary.id',
            array(
                'name' => 'item_relations_vocabulary.name',
            )
        )
        ->where('item_relations_vocabulary.name = ?', 'MARI');
        $relations = $db->fetchAll($select);
        // var_dump($relations);

        // get item types "Nachricht Textquelle"
        $select = new Omeka_Db_Select($db->getAdapter());
        $select
            ->from(
                array('item' => $db->Items),
                array('subject_id' => 'id')
            )
            ->joinLeft(
                array('element_text' => $db->ElementText),
                'item.id = element_text.record_id',
                array(
                    'SO-Ralation sigle' => 'element_text.text',
                )
            )
            ->joinLeft(
                array('element' => $db->Element),
                'element_text.element_id = element.id',
                array(
                    // 'e_name' => 'element.name',
                )
            )
            ->joinLeft(
                array('item_types_element' => $db->ItemTypesElement),
                'element.id = item_types_element.element_id',
                array()
            )
            ->joinLeft(
                array('item_type' => $db->ItemType),
                'item_types_element.item_type_id = item_type.id',
                array(
                    // 'item_type_name' => 'item_type.name',
                )
            )
            ->where('element_text.record_type = ?', 'Item')
            ->where('element.name = ?', 'SO-Ralation sigle')
            ->where('item_type.name = ?', 'Nachricht Textquelle');
        // $sql = $select->__toString();
        // var_dump($sql);
        $items = $db->fetchAll($select);
        // var_dump($items);
        if (!$items) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Keine Objekte vom Typ "MARI - Nachricht Textquelle" mit "SO-Ralation sigle" Werten vorhanden.', 'error');
            $this->_helper->redirector('index', 'admin', 'gina-admin-mod');
        }

        foreach ($items as $itemKey => $item) {

            // SO-Relation initial for each found item
            $select = new Omeka_Db_Select($db->getAdapter());
            $select
                ->from(
                    array('element_text' => $db->ElementText),
                    array('SO-Ralation type' => 'text')
                )
                ->joinLeft(
                    array('element' => $db->Element),
                    'element_text.element_id = element.id',
                    array(
                        // 'e_name' => 'element.name',
                    )
                )
                ->where('element_text.record_type = ?', 'Item')
                ->where('element_text.record_id = ?', $item['subject_id'])
                ->where('element.name = ?', 'SO-Relation initial');

            // if ($item['SO-Ralation sigle'] == 'Liebermann, Lesendes Mädchen') {
            //     // var_dump($item, $relationType);
            //     $sql = $select->__toString();
            //     var_dump($sql);

            // }
            $relationType = $db->fetchRow($select);


            if ($relationType['SO-Ralation type']) {
                // check if there is already any relation
                $select = new Omeka_Db_Select($db->getAdapter());
                $select
                    ->from(
                        array('item_relations' => $db->ItemRelationsRelation),
                        '*'
                    )
                    ->where('subject_item_id = ?', (int) $item['subject_id'])
                    // ->where('property_id = ?', $relationMariPrimAssignment['property_id'])
                    // ->where('object_item_id = ?', $sharedObject['object_id'])
                    ;
                $relationInDB = $db->fetchRow($select);
                // var_dump($relationInDB);
                if ($relationInDB) {
                    continue;
                } else {
                    // get shared object by sigle
                    $select = new Omeka_Db_Select($db->getAdapter());
                    $select
                        ->from(
                            array('item' => $db->Item),
                            array(
                                'id' => 'id',
                            )
                        )
                        ->joinLeft(
                            array('item_type' => $db->ItemType),
                            'item.item_type_id = item_type.id',
                            array()
                        )
                        ->joinLeft(
                            array('element_text' => $db->ElementText),
                            'item.id = element_text.record_id',
                            array()
                        )
                        ->joinLeft(
                            array('element' => $db->Element),
                            'element_text.element_id = element.id',
                            array()
                        )
                        // ->joinLeft(
                        //     array('item_types_element' => $db->ItemTypesElement),
                        //     'element.id = item_types_element.element_id',
                        //     array()
                        // )
                        ->where('item_type.name = ?', 'Shared Object')
                        ->where('element.name = ?', 'Sigle')
                        ->where('element_text.text = ?', $item['SO-Ralation sigle']);

                    $sharedObject = $db->fetchRow($select);
                    // var_dump($sharedObject);

                    if(!$sharedObject) {
                        continue;
                    } else {
                        $propId = false;
                        foreach ($relations as $relation) {
                            if ($relation['label'] === $relationType['SO-Ralation type']) {
                                $propId = $relation['property_id'];
                            }
                        }
                        // var_dump($propId);
                        if (!$propId) {
                            continue;
                        } else {
                            $log[$item['subject_id']] = $this->_insertNewRelation(
                                $item['subject_id'],
                                $propId,
                                $sharedObject['id'],
                                'Beim Ingest vorgenommene Zuweisung: ' . $relationType['SO-Ralation type']
                            );
                        }

                    }

                }

            }

        }
        $this->view->log = $log;
    }

    /**
     * @url: /admin/gina-admin-mod/admin/add-primary-item-relations
     */
    public function addPrimaryItemRelationsAction()
    {
        $db = get_db();

        // Get the item relations property id for MARI -> Primärzuweisung
        $select = new Omeka_Db_Select($db->getAdapter());
        $select
        ->from(
            array('item_relations_property' => $db->ItemRelationsProperty),
            array(
                'label' => 'item_relations_property.label',
                'property_id' => 'item_relations_property.id'
            )
        )
        ->joinLeft(
            array('item_relations_vocabulary' => $db->ItemRelationsVocabulary),
            'item_relations_property.vocabulary_id = item_relations_vocabulary.id',
            array(
                'name' => 'item_relations_vocabulary.name',
            )
        )
        ->where('item_relations_property.label = ?', 'Primärzuweisung')
        ->where('item_relations_vocabulary.name = ?', 'MARI');
        // $sql = $select->__toString();
        // var_dump($sql);
        $relationMariPrimAssignment = $db->fetchRow($select);
        // var_dump($relationMariPrimAssignment);
        if (!isset($relationMariPrimAssignment) || empty($relationMariPrimAssignment)) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Keine Objektbezihung vom Typ "MARI" mit Beziehung "Primärzuweisung" vorhanden.', 'error');
            $this->_helper->redirector('index', 'admin', 'gina-admin-mod');
        }

        // Get all Shared Object items
        $select = new Omeka_Db_Select($db->getAdapter());
        $select
            ->from(
                array('item' => $db->Item),
                array(
                    'id' => 'id'
                )
            )
            ->joinLeft(
                array('item_type' => $db->ItemType),
                'item.item_type_id = item_type.id',
                array()
            )
            ->where('item_type.name = ?', 'Shared Object');

        // $sql = $select->__toString();
        // var_dump($sql);
        $allSharedObjectsResults = $db->fetchAll($select);
        $allSharedObjects = array();
        foreach ($allSharedObjectsResults as $allSharedObjectsResult) {
            array_push($allSharedObjects, $allSharedObjectsResult['id']);
        }
        // var_dump($allSharedObjects);

        // Get Shared Objects with metadata 'Sigle konstituierende Nachricht ID' set

        /**
         * SELECT `item`.`id` AS `object_id`,
         * `element_text`.`text` AS `subject_id`
         * FROM `omeka_mari_items` AS `item`
         *
         * LEFT JOIN `omeka_mari_element_texts` AS `element_text`
         * ON item.id = element_text.record_id
         *
         * LEFT JOIN `omeka_mari_elements` AS `element`
         * ON element_text.element_id = element.id
         *
         * LEFT JOIN `omeka_mari_item_types_elements` AS `item_types_element`
         * ON element.id = item_types_element.element_id
         *
         * LEFT JOIN `omeka_mari_item_types` AS `item_type`
         * ON item_types_element.item_type_id = item_type.id
         *
         * WHERE (element_text.record_type = 'Item')
         * AND (element.name = 'Sigle konstituierende Nachricht ID')
         * AND (item_type.name = 'Shared Object')
         */


        $select = new Omeka_Db_Select($db->getAdapter());
        $select
            ->from(
                array('item' => $db->Items),
                array('object_id' => 'id')
            )
            ->joinLeft(
                array('element_text' => $db->ElementText),
                'item.id = element_text.record_id',
                array(
                    'subject_id' => 'element_text.text',
                )
            )
            ->joinLeft(
                array('element' => $db->Element),
                'element_text.element_id = element.id',
                array(
                    // 'e_name' => 'element.name',
                )
            )
            ->joinLeft(
                array('item_types_element' => $db->ItemTypesElement),
                'element.id = item_types_element.element_id',
                array()
            )
            ->joinLeft(
                array('item_type' => $db->ItemType),
                'item_types_element.item_type_id = item_type.id',
                array(
                    // 'item_type_name' => 'item_type.name',
                )
            )
            ->where('element_text.record_type = ?', 'Item')
            ->where('element.name = ?', 'Sigle konstituierende Nachricht ID')
            ->where('item_type.name = ?', 'Shared Object');

        // $sql = $select->__toString();
        // var_dump($sql);
        $sharedObjects = $db->fetchAll($select);
        // var_dump($sharedObjects);

        if (!isset($sharedObjects) || empty($sharedObjects)) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Keine Objekte vom Typ "Shared Object" mit Wert aus Metafeld "Sigle konstituierende Nachricht ID" vorhanden.', 'error');
            $this->_helper->redirector('index', 'admin', 'gina-admin-mod');
        }

        // iterate Shared Objects and see if there are MARI -> Primärzuweisung item relations set for them
        $relations = array();
        $log = array();
        foreach ($sharedObjects as $sharedObject) {
            $select = new Omeka_Db_Select($db->getAdapter());
            $select
                ->from(
                    array('item_relations' => $db->ItemRelationsRelation),
                    '*'
                )
                ->where('subject_item_id = ?', (int) $sharedObject['subject_id'])
                ->where('property_id = ?', $relationMariPrimAssignment['property_id'])
                ->where('object_item_id = ?', $sharedObject['object_id']);
            // $sql = $select->__toString();
            // var_dump($sql);
            $relations[$sharedObject['object_id']] = $db->fetchAll($select);

            // remove from $allSharedObjects
            $allSharedObjectsKey = array_search($sharedObject['object_id'], $allSharedObjects);
            if ($allSharedObjectsKey !== false) {
                unset($allSharedObjects[$allSharedObjectsKey]);
            }

            if (empty($relations[$sharedObject['object_id']])) {
                // inssert new relation
                $log[$sharedObject['object_id']] = $this->_insertNewRelation($sharedObject['subject_id'], $relationMariPrimAssignment['property_id'],  $sharedObject['object_id']);
            } else if (count($relations[$sharedObject['object_id']]) > 1) {
                // check for state
                $hasCurrent = false;
                foreach ($relations[$sharedObject['object_id']] as $relation) {
                    if ($relation['state'] === 'current') {
                        $hasCurrent = true;
                        break;
                    }
                }
                if ($hasCurrent === false) {
                    // insert new relation
                    $log[$sharedObject['object_id']] = $this->_insertNewRelation($sharedObject['subject_id'], $relationMariPrimAssignment['property_id'],  $sharedObject['object_id']);
                }
            } else {
                // check for state === current
                if ($relations[$sharedObject['object_id']][0]['state'] !== 'current') {
                    // inssert new relation
                    $log[$sharedObject['object_id']] = $this->_insertNewRelation($sharedObject['subject_id'], $relationMariPrimAssignment['property_id'],  $sharedObject['object_id']);
                }
            }


        }
        $this->_insertNewAnnotation(1);
        $this->view->log = $log;
        $this->view->noSigleIdObjects = $allSharedObjects;
        // var_dump($relations);

    }

    protected function _insertNewRelation($subject_id, $property_id, $object_id, $annotation = '') {
        $relation = new ItemRelationsRelation();
        $relation->subject_item_id = (int) $subject_id;
        $relation->property_id = (int) $property_id;
        $relation->object_item_id = (int) $object_id;
        $relation->state = 'current';
        $res = $relation->save();
        $this->_insertNewAnnotation($relation->id, '', $annotation);
        return $res;
    }

    protected function _insertNewAnnotation($relation_id, $user_id = '', $annotation = '') {

        if (empty($user_id)) {
            $user = $this->_getSystemuser();
            if (isset($user->id)) {
                $user_id = $user->id;
            }
        }

        if (empty($annotation)) {
            $annotation = 'Beim Ingest vorgenommene Primärzuweisung';
        }

        $itemRelationAnnotation = new ItemRelationsAnnotation;
        $itemRelationAnnotation->relation_id = $relation_id;
        $itemRelationAnnotation->state = 'current';
        $itemRelationAnnotation->user_id = $user_id;
        $itemRelationAnnotation->annotation = $annotation;
        $saved = $itemRelationAnnotation->save();
    }

    protected function _getSystemuser()
    {
        $db = get_db();
        $result = $db->getTable('User')->findBy(array('username' => 'marisystem'));
        if (count($result) === 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

}
