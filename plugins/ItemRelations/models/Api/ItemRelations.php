<?php
// extend Omeka_Record_Api_AbstractRecordAdapter if second choice
class Api_ItemRelations extends Api_Item
{
    /**
     * Get the REST representation of a record.
     *
     * @param object $record
     * @return array
     */
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        if (isset($_GET['object_item_id']) && !empty($_GET['object_item_id'])) {
            $id = $record->subject_item_id;
        }
        if (isset($_GET['subject_item_id']) && !empty($_GET['subject_item_id'])) {
            $id = $record->object_item_id;
        }
        if (isset($id) && !empty($id)) {
            $item = get_db()->getTable('Item')->find($id);
            if ($item) {
                $representation = parent::getRepresentation($item);
            } else {
                $representation = array("message" => "Invalid record. Record not found.");
                // This would poison everything:
                // throw new Omeka_Controller_Exception_Api('Invalid record. Record not found.', 404);
            }
        } else {

            $representation = array(
                // 'id' => $record->id,
                'object_item_id' => $record->subject_item_id,
                'object_item_url' => self::getResourceUrl('/items/' . $record->subject_item_id),
                'story_item_id' => $record->object_item_id,
                'story_item_url' => self::getResourceUrl('/items/' . $record->object_item_id),
            );
        }
        return $representation;
    }

    // Set data to a record during a POST request.
    public function setPostData(Omeka_Record_AbstractRecord $record, $data)
    {
        // Set properties directly to a new record.
    }

    // Set data to a record during a PUT request.
    public function setPutData(Omeka_Record_AbstractRecord $record, $data)
    {
        // Set properties directly to an existing record.
    }

}
