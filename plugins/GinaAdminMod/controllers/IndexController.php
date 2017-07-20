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
        $autocomplete = array();

        if (count($results) > 1) {
            foreach ($results as $result) {
                $autocomplete[] = array(
                    'label' => $result['text'],
                    'value' => $result['record_id']
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
