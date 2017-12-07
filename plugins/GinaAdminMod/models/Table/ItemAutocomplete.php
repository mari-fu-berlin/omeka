<?php
/**
 * Item Autocomplete
 * @copyright Copyright 2017 Grandgeorg Websolutions
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2.0
 */


/**
 * Item Autocomplete  table.
 */
class Table_ItemAutocomplete extends Omeka_Db_Table
{
    public function getSelect()
    {
        $db = $this->getDb();
        return parent::getSelect()
            ->joinLeft(
                array('item_types' => $db->ItemTypes),
                'item_autocompletes.item_type_id = item_types.id',
                array(
                    'item_type_name' => 'name',
                )
            );
    }

    public function findAll()
    {
        $db = $this->getDb();
        $select = $this->getSelect();
        return $this->fetchObjects($select);
    }

}
