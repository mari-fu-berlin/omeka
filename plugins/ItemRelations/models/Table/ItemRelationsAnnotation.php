<?php
/**
 * Item Relations
 */

/**
 * Item Relations Annotation table.
 */
class Table_ItemRelationsAnnotation extends Omeka_Db_Table
{

    public function getSelect()
    {
        $db = $this->getDb();
        return parent::getSelect()
            ->joinLeft(
                array('users' => $db->Users),
                'item_relations_annotations.user_id = users.id',
                array(
                    'user_name' => 'name',
                    'user_email' => 'email',
                    'user_active' => 'active',
                )
            );
    }

    public function findByCurrentByRelation($relationId, $state = 'current')
    {
        $db = $this->getDb();
        $select = $this->getSelect()
            ->where('item_relations_annotations.relation_id = ?', (int) $relationId)
            ->where('item_relations_annotations.state = ?', $state);
        return $this->fetchObject($select);
    }

    public function findByAllByRelation($relationId, $state = 'archive')
    {
        $db = $this->getDb();
        $select = $this->getSelect()
            ->where('item_relations_annotations.relation_id = ?', (int) $relationId);
        if ($state === 'archive' || $state === 'current') {
            $select->where('item_relations_annotations.state = ?', $state);
        }
        $select->order(array('item_relations_annotations.added DESC, item_relations_annotations.id'));
        return $this->fetchObjects($select);
    }
}
