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

    public function autocompleteShowAction()
    {
        $params = $this->getAllParams();
        $db = get_db();
        $autocompleteTable = $db->getTable('ItemAutocomplete');
        $autocompletes = $autocompleteTable->findBy($params);
        $this->view->autocompletes = $autocompletes;
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


}
