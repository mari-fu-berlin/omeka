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

        $elementTextsTbl = get_db()->getTable('ElementText');
        $select = $elementTextsTbl->getSelect()
            ->where('element_id IN (?)', '50,52,53')
            ->where('text LIKE ?', '%' . $query . '%');
        $results = $elementTextsTbl->fetchAll($select);

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

}
