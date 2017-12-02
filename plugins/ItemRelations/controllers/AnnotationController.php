<?php
/**
 * Item Relations
 * @copyright Copyright 2017 Grandgeorg Websolutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Annotations controller.
 *
 */
class ItemRelations_AnnotationController extends Omeka_Controller_AbstractActionController
{

    public function init() {
        $this->_helper->db->setDefaultModelName('ItemRelationsAnnotation');
    }

    public function indexAction()
    {
        $this->_helper->redirector('index', 'index', 'dashboard');
    }

    public function historyAction()
    {
        $params = $this->getAllParams();

        if (!isset($params['rid']) || empty($params['rid']) || 0 === (int) $params['rid']) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Sie müssen eine Annotationshistorie auswhälen.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
            return;
        }

        $history = $this->_helper->db->findByAllByRelation($params['rid'], 'all');

        if (!$history) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Eine Annotationshistorie konte nicht gefunden werden.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $relation = $this->_helper->db->getTable('ItemRelationsRelation')->findById($params['rid']);

        if (!$relation) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Objektbeziehung axistiert nicht mehr.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $subject = $this->_helper->db->getTable('Item')->find($relation->subject_item_id);
        $object = $this->_helper->db->getTable('Item')->find($relation->object_item_id);

        $this->view->history = $history;
        $this->view->relation = $relation;
        $this->view->subject = $subject;
        $this->view->object = $object;
    }
}
