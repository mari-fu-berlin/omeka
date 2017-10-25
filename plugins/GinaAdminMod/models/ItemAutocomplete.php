<?php
/**
 * Item Autocomplete
 * @copyright Copyright 2017 Grandgeorg Websolutions
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2.0
 */

/**
 * Item Autocomplete model.
 */
class ItemAutocomplete extends Omeka_Record_AbstractRecord
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $item_type_id;

    /**
     * @var string
     */
    public $autocomplete_field_name;

    /**
     * @var int
     */
    public $autocomplete_field_id;

    /**
     * @var string
     */
    public $auto_field_name;

    /**
     * @var int
     */
    public $auto_field_id;

    /**
     * @var string
     */
    public $autocomplete_item_type_ids;

}
