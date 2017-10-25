<?php
/**
 * Admin Mod
 *
 * @copyright Copyright 2017 Grandgeorg Websolutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Admin Mod index controller class.
 *
 * @package GinaAdminMod
 */
class GinaAdminMod_IndexController extends Omeka_Controller_AbstractActionController
{
    public function init() {}

    public function indexAction()
    {
        $this->_helper->redirector('index', 'index', 'items');
        return;
    }

    public function itemautocompleteAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $query = $this->_request->getParam('term');
        $objectTypeId = $this->_request->getParam('type');

        if (!isset($objectTypeId) || empty($objectTypeId)) {
            $this->_helper->jsonApi(array(
                'errors' => array(
                    'status' => '442',
                    'title'  => 'No item type attribute provided.',
                    'detail' => 'You must provide a type param in the query.'
                )
            ));
            return;
        }

        $db = get_db();
        $select = $db->select()
            ->from(
                array('element' => $db->ElementText),
                array('id', 'record_id', 'element_id', 'text')
            )
            ->join(
                array('item' => $db->Item),
                'element.record_id = item.id',
                array()
            )
            ->where('element.element_id IN (?)', '62')
            ->where('element.text LIKE ?', '%' . $query . '%')
            ->where('item.item_type_id = ?', $objectTypeId)
        ;

        // $sql = $select->__toString();
        // echo $sql;

        $results = $db->fetchAll($select);
        // print_r($results);
        // return;
        $autocomplete = array();

        if (count($results) > 0) {
            foreach ($results as $result) {
                $autocomplete[] = array(
                    'label' => $result['text'],
                    'value' => $result['record_id']
                );
            }
        }
        $this->_helper->jsonApi($autocomplete);
    }

    public function autocompleteConfAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $type = $this->_request->getParam('type_id');

        if (!isset($type) || empty($type)) {
            $this->_helper->jsonApi(array(
                'errors' => array(
                    'status' => '442',
                    'title'  => 'No type_id attribute provided.',
                    'detail' => 'You must provide a type_id in the query.'
                )
            ));
            return;
        }
        $db = get_db();
        $autocompleteTable = $db->getTable('ItemAutocomplete');
        $autocompletes = $autocompleteTable->findBy(array('item_type_id' => $type));

        $item = null;
        $item_id = $this->_request->getParam('type_id', null);
        if (isset($item_id)) {
            $item = $db->getTable('Item')->find($item_id);
        }


        if (!isset($autocompletes) || empty($autocompletes)) {
            $this->_helper->jsonApi(array(
                'errors' => array(
                    'status' => '442',
                    'title'  => 'No config for item type_id found.',
                    'detail' => 'For this item type there is no autocomplete config set.'
                )
            ));
            return;
        }

        $jsonSettings = array();

        foreach ($autocompletes as $autocomplete) {
            $currentValues = $this->_getCurrentAutofieldValues($item, $autocomplete);
            $jsonSettings[] = array(
                'selectorAutocompleteFieldId' => 'element-' . $autocomplete->autocomplete_field_id,
                'autofield' => '#element-' . $autocomplete->auto_field_id,
                'itemType' => $autocomplete->autocomplete_item_type_ids,
                'currentAutocompleteFieldValue' => $currentValues['autocompleteField'],
                'currentAutoFieldValue' => $currentValues['autoField'],
            );
        }

        $this->_helper->jsonApi($jsonSettings);

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
        if (isset($item) && isset($item->item_type_id)) {
            $ret['autocompleteField'] = metadata($item, array('Item Type Metadata', $autocomplete['autocomplete_field_name']));
            $ret['autoField'] = metadata($item, array('Item Type Metadata', $autocomplete['auto_field_name']));
        }
        return $ret;
    }

    public function itemAutocompleteComplexAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $query = $this->_request->getParam('term');
        $objectTypeId = $this->_request->getParam('type');

        if (!isset($objectTypeId) || empty($objectTypeId)) {
            $this->_helper->jsonApi(array(
                'errors' => array(
                    'status' => '442',
                    'title'  => 'No item type attribute provided.',
                    'detail' => 'You must provide a type param in the query.'
                )
            ));
            return;
        }

        $objectTypeId = explode(',', $objectTypeId);

        $db = get_db();
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
            ->where('element.element_id = ?', 62)
            ->where('element.text LIKE ?', '%' . $query . '%')
            ->where('item.item_type_id IN (?)', $objectTypeId)
        ;

        // $sql = $select->__toString();
        // echo $sql;
        // return;

        $results = $db->fetchAll($select);

        // print_r($results);
        // return;

        $autocomplete = array();

        if (count($results) > 0) {
            foreach ($results as $result) {
                $autocomplete[] = array(
                    'label' => $result['text'],
                    'value' => $result['text'],
                    'item_id' => $result['record_id'],
                    'item_type_id' => $result['item_type_id'],
                    'category' => $result['name']
                );
            }
        }
        $this->_helper->jsonApi($autocomplete);
    }

    public function itemautocompleteidAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $sigle = array('status' => 404);
        $id = (int) $this->_request->getParam('id');
        $db = get_db();
        $item = $db->getTable('Item')->find($id);
        if (isset($item) && $item->item_type_id == 27) {
            $sigle = array(
                'sigle' => metadata($item, array('Item Type Metadata', 'Sigle')),
                'id' => $id,
                'status' => 200
            );
        }
        // var_dump($item, $sigle);
        $this->_helper->jsonApi($sigle);
    }
}
