<?php
/**
 * Item Relations
 */

/**
 * Item Relations Annotation model.
 */
class ItemRelationsAnnotation extends Omeka_Record_AbstractRecord
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $relation_id;

    /**
     * @var str
     */
    public $state;

    /**
     * @var datetime
     */
    public $added;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var str
     */
    public $annotation;


    // protected $_user;

    // public function fetchUser()
    // {
    //     return ($this->user_id)? $this->getDb()->getTable('user')->find($this->user_id) : null;
    // }

    // public function setUser()
    // {
    //     $this->_user = $this->fetchUser();
    // }

    // public function getUser()
    // {
    //     return $this->_user;
    // }

}
