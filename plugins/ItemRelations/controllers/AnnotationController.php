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

    public function init() 
    {
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

        $currentAnnotation = null;
        foreach ($history as $annotation) {
            if ($annotation->state === 'current') {
                $currentAnnotation = $annotation;
                break;
            }
        }

        $relation = $this->_helper->db->getTable('ItemRelationsRelation')->findById($params['rid']);

        if (!$relation) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Objektbeziehung axistiert nicht mehr.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $subject = $this->_helper->db->getTable('Item')->find($relation->subject_item_id);
        $object = $this->_helper->db->getTable('Item')->find($relation->object_item_id);

        if (!$subject || !$object) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Subject oder Objekt auf das sich die Annotation bezieht existiert nicht mehr.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $this->view->history = $history;
        $this->view->relation = $relation;
        $this->view->subject = $subject;
        $this->view->object = $object;
        $this->view->currentAnnotation = $currentAnnotation;
    }

    public function dlhistoryAction()
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

        $currentAnnotation = null;
        foreach ($history as $annotation) {
            if ($annotation->state === 'current') {
                $currentAnnotation = $annotation;
                break;
            }
        }

        $relation = $this->_helper->db->getTable('ItemRelationsRelation')->findById($params['rid'], 'deleted');

        if (!$relation) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Objektbeziehung axistiert nicht mehr.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $subject = $this->_helper->db->getTable('Item')->find($relation->subject_item_id);
        $object = $this->_helper->db->getTable('Item')->find($relation->object_item_id);

        if (!$subject || !$object) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Subject oder Objekt auf das sich die Annotation bezieht existiert nicht mehr.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $this->view->history = $history;
        $this->view->relation = $relation;
        $this->view->subject = $subject;
        $this->view->object = $object;
        $this->view->currentAnnotation = $currentAnnotation;
    }

    public function compareAction()
    {
        $currentid = $this->getParam('currentid');
        $compareid = $this->getParam('compareid');

        if (!$currentid || !$compareid) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Sie müssen zum Vergleich zwei Annotationen auswählen.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $current = $this->_helper->db->find((int) $currentid);
        $compare = $this->_helper->db->find((int) $compareid);

        if (!$current || !$compare) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Annotation nicht gefunden.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        require __DIR__ . '/../vendor/autoload.php';
        
        $currentAnnotation = explode("\n", Html2Text\Html2Text::convert($current->annotation));
        $compareAnnotation = explode("\n", Html2Text\Html2Text::convert($compare->annotation));
        $diffOptions = array('context' => 9999999999);
        $diff = new Diff($compareAnnotation, $currentAnnotation, $diffOptions);
        $rendererOptions = array(
            'currentTitle' => 'Version vom ' . date("d.m.Y H:i:s", strtotime($compare->added)),
            'compareTitle' => 'Version vom ' . date("d.m.Y H:i:s", strtotime($current->added)) . ' (aktuell)'
        );
        $diffRenderer = new Diff_Renderer_Html_SideBySide($rendererOptions);
        $this->view->diff = $diff->render($diffRenderer);

    }

    public function setcurrentAction()
    {
        $id = $this->getParam('id');
        if (!$id) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Sie müssen eine Annotationen auswählen.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $new = $this->_helper->db->find((int) $id);
        if (!$new) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Annotation nicht gefunden.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $relation = $this->_helper->db->getTable('ItemRelationsRelation')->findById($new->relation_id);
        if (!$relation) {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Sie können die Annotationen für gelöschte Beziehungen nicht ändern.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

        $old = get_db()->getTable('ItemRelationsAnnotation')->findByCurrentByRelation($new->relation_id);
        if ($old) {
            $old->state = 'archive';
            $old->save();
        }

        $user = Zend_Registry::get('bootstrap')->getResource('CurrentUser');
        $itemRelationAnnotation = new ItemRelationsAnnotation;
        $itemRelationAnnotation->relation_id = $new->relation_id;
        $itemRelationAnnotation->state = 'current';
        $itemRelationAnnotation->user_id = $user->id;
        $itemRelationAnnotation->annotation = $new->annotation;
        $saved = $itemRelationAnnotation->save();

        if ($saved) {
            $this->_helper->flashMessenger('Annotation erfolgreich geändert.', 'success');
            $this->_helper->redirector('show', 'items', false, array('id' => $relation->subject_item_id));
        } else {
            $this->_helper->flashMessenger('Vorgang abgebrochen. Beim Speichern gab es einen Fehler.', 'error');
            $this->_helper->redirector('index', 'index', 'dashboard');
        }

    }
}
