<?php
/**
 * SpreadsheetExport model
 * @copyright Copyright 2014 Viktor Grandgeorg
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

class SpreadsheetExport extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{

    public $name;
    public $options;
    public $modified_by_user_id;
    public $created_by_user_id;
    public $updated;
    public $inserted;

    protected function _initializeMixins()
    {
        $this->_mixins[] = new Mixin_Search($this);
    }

    public function getResourceId()
    {
        return 'Spreadsheet';
    }

    public function getModifiedByUser()
    {
        return $this->getTable('User')->find($this->modified_by_user_id);
    }

    public function getCreatedByUser()
    {
        return $this->getTable('User')->find($this->created_by_user_id);
    }

    protected function _validate()
    {
        if (empty($this->name)) {
            $this->addError('name', __('Das Schema muss einen Namen haben.'));
        }
    }

    protected function beforeSave($args)
    {
        $this->name = trim($this->name);
        $this->modified_by_user_id = current_user()->id;
        $this->updated = date('Y-m-d H:i:s');
    }

    public function getProperty($property)
    {
        switch($property) {
            case 'created_username':
                return $this->getCreatedByUser()->username;
            case 'modified_username':
                return $this->getModifiedByUser()->username;
            default:
                return parent::getProperty($property);
        }
    }
}
