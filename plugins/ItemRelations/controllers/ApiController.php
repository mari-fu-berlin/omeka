<?php
/**
 * Item Relations API v2
 * @copyright Copyright 2019 Grandgeorg Websolutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Api controller.
 *
 */
class ItemRelations_ApiController extends Omeka_Controller_AbstractActionController
{

    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
    }

    public function sharedobjectlistAction()
    {
        $Item = $this->_helper->db->getTable('Item');
        $sharedObjects = $Item->findBy(array('item_type' => 27, 'public' => 1));
        $result = array();
        // $count = 0;
        // $db = get_db();
        // echo $db->prefix;
        // return;
        foreach ($sharedObjects as $sharedObject) {

            // $count++;
            // if ($count > 10) {
            //     continue;
            // }


            // $sharedObjectState = $sharedObject->getElementTexts('Item Type Metadata', 'Status Bearbeitung');
            // if (isset($sharedObjectState) && !empty($sharedObjectState)) {
            //     $sharedObjectState = $sharedObjectState[0]['text'];
            // } else {
            //     $sharedObjectState = null;
            // }

            // $lost_art_permalink = $sharedObject->getElementTexts('Item Type Metadata', 'Lost Art Permalink');
            // if (!empty($lost_art_permalink)) {
            //     $lost_art_permalink = $lost_art_permalink[0]['text'];
            // } else {
            //     $lost_art_permalink = null;
            // }

            // $all = $sharedObject->getAllElementTexts('Item Type Metadata');
            $sharedObjectMeta = $this->getNamedElementTexts($sharedObject->id);

            $constMsgMeta = null;
            if (!empty($sharedObjectMeta['Sigle_konstituierende_Nachricht_ID'])) {
                $constMsgMeta = $this->getNamedElementTexts($sharedObjectMeta['Sigle_konstituierende_Nachricht_ID']);
            }

            // var_dump($sharedObject->toArray());
            // $constMsgId = $sharedObject->getElementTexts('Item Type Metadata', 'Sigle konstituierende Nachricht ID');

            // if (!empty($constMsgId)) {
            //     $constMsgId = $constMsgId[0];
            //     $constMsg = $Item->find((int) $constMsgId['text']);
            //     if (isset($constMsg) && is_object($constMsg)) {
            //         $constMsgUrheber = $constMsg->getElementTexts('Item Type Metadata', 'Urheber');
            //         $constMsgTitel = $constMsg->getElementTexts('Item Type Metadata', 'Titel');
            //     }
            // } else {
            //     $constMsgId = null;
            // }
            // if (isset($constMsgUrheber) && isset($constMsgUrheber[0])) {
            //     $constMsgUrheber = $constMsgUrheber[0];
            // } else {
            //     $constMsgUrheber = null;
            // }
            // if (isset($constMsgTitel) && isset($constMsgTitel[0])) {
            //     $constMsgTitel = $constMsgTitel[0];
            // } else {
            //     $constMsgTitel = null;
            // }

            // cmsg_meta = konstituierende Nachricht Meta Felder
            $result[] = array(
                'id' => $sharedObject->id,
                'meta' => $sharedObjectMeta,
                'cmsg_meta' => $constMsgMeta
                // 'const_msg_id' => (int) $constMsgId['text'],
                // 'const_msg_titel' => $constMsgTitel['text'],
                // 'const_msg_urheber' => $constMsgUrheber['text']
            );
        }

        // $this->_helper->json($result);
        $this->_helper->jsonApi($result);

    }

    // public function txtsrclistAction()
    // {
    //     $Item = $this->_helper->db->getTable('Item');
    //     // $select = $Item->getSelect();
    //     $items = $Item->findBy(array('item_type' => 18, 'public' => 1));
    //     $filterByOrt = null;
    //     if (isset($_REQUEST['ort']) || !empty($_REQUEST['ort'])) {
    //         $filterByOrt = $_REQUEST['ort'];
    //     }
    //     $result = array();
    //     foreach ($items as $item) {
    //         $itemMeta = $this->getNamedElementTexts($item->id);
    //         if (!isset($filterByOrt)) {
    //             $result[] = array(
    //                 'id' => $item->id,
    //                 'meta' => $itemMeta,
    //             );
    //         } elseif (isset($itemMeta['Sigle_Ortsangabe']) && urldecode($filterByOrt) === $itemMeta['Sigle_Ortsangabe']) {
    //             $result[] = array(
    //                 'id' => $item->id,
    //                 'meta' => $itemMeta,
    //             );
    //         }
    //     }
    //     $this->_helper->jsonApi($result);
    // }

    public function txtsrclistAction()
    {
        $db = $this->_helper->db->getDb();
        $item = $this->_helper->db->getTable('Item');
        $select = $item->getSelectForFindBy(array('item_type' => 18, 'public' => 1));
        $select->joinLeft(
            array('sho' => $db->ItemRelationsRelation),
            "items.id = sho.subject_item_id AND sho.state = 'current'",
            array('sho_id' => 'sho.object_item_id')
        );
        $items = $item->fetchObjects($select);
        $filterByOrt = null;
        if (isset($_REQUEST['ort']) || !empty($_REQUEST['ort'])) {
            $filterByOrt = $_REQUEST['ort'];
        }
        $result = array();
        foreach ($items as $item) {
            $itemMeta = $this->getNamedElementTexts($item->id);
            if (!isset($filterByOrt)) {
                $result[] = array(
                    'sho_id' => $item->sho_id,
                    'id' => $item->id,
                    'meta' => $itemMeta,
                );
            } elseif (isset($itemMeta['Sigle_Ortsangabe']) && urldecode($filterByOrt) === $itemMeta['Sigle_Ortsangabe']) {
                $result[] = array(
                    'sho_id' => $item->sho_id,
                    'id' => $item->id,
                    'meta' => $itemMeta,
                );
            }
        }
        $this->_helper->jsonApi($result);
    }

    public function txtsrclisttestAction()
    {
        // $db = $this->_helper->db->getDb();
        // $item = $this->_helper->db->getTable('Item');
        // // $item = $db->getTable('Item');
        // $select = $item->getSelectForFindBy(array('item_type' => 18, 'public' => 1));
        // $select->joinLeft(
        //     array('sho' => $db->ItemRelationsRelation),
        //     "items.id = sho.subject_item_id AND sho.state = 'current'",
        //     array('sho_id' => 'sho.object_item_id')
        // );
        // $items = $item->fetchObjects($select);
        // $filterByOrt = null;
        // if (isset($_REQUEST['ort']) || !empty($_REQUEST['ort'])) {
        //     $filterByOrt = $_REQUEST['ort'];
        // }
        // $result = array();
        // foreach ($items as $item) {
        //     $itemMeta = $this->getNamedElementTexts($item->id);
        //     if (!isset($filterByOrt)) {
        //         $result[] = array(
        //             'sho_id' => $item->sho_id,
        //             'id' => $item->id,
        //             'meta' => $itemMeta,
        //         );
        //     } elseif (isset($itemMeta['Sigle_Ortsangabe']) && urldecode($filterByOrt) === $itemMeta['Sigle_Ortsangabe']) {
        //         $result[] = array(
        //             'sho_id' => $item->sho_id,
        //             'id' => $item->id,
        //             'meta' => $itemMeta,
        //         );
        //     }
        // }
        $result = array('test' => 'ok');
        $this->_helper->jsonApi($result);
    }

    public function imgdoclistAction()
    {
        $Item = $this->_helper->db->getTable('Item');
        $items = $Item->findBy(array('item_type' => 29, 'public' => 1));
        $result = array();
        foreach ($items as $item) {
            $itemMeta = $this->getNamedElementTexts($item->id);
            $result[] = array(
                'id' => $item->id,
                'meta' => $itemMeta,
            );
        }
        $this->_helper->jsonApi($result);
    }

    public function itemlistAction()
    {
        if (!isset($_REQUEST['item_type']) || empty($_REQUEST['item_type'])) {
            return $this->_helper->jsonApi(array());
        }
        $Item = $this->_helper->db->getTable('Item');
        $items = $Item->findBy(array('item_type' => $_REQUEST['item_type'], 'public' => 1));
        $result = array();
        foreach ($items as $item) {
            $itemMeta = $this->getNamedElementTexts($item->id);
            $result[] = array(
                'id' => $item->id,
                'meta' => $itemMeta,
            );
        }
        $this->_helper->jsonApi($result);
    }


    public function getNamedElementTexts($id)
    {
        $db = get_db();
        $sql = '
        SELECT `element_texts`.`text`, `elements`.`name` FROM `' . $db->prefix . 'element_texts` AS `element_texts`
        LEFT JOIN `' . $db->prefix . 'elements` AS `elements` ON `elements`.`id` = `element_texts`.`element_id`
        WHERE (element_texts.record_type = \'Item\') AND (`elements`.`element_set_id` = 3) AND (element_texts.record_id = ?) ORDER BY `element_texts`.`id` ASC;
        ';
        $stmt = $db->query($sql, array($id));
        $rows = array();
        while ($row = $stmt->fetch()) {
            $rows[str_replace(array(' ', '-'), '_', $row['name'])] = $row['text'];
        }
        return $rows;
    }
}
