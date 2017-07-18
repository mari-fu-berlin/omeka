<?php
/**
 * SpreadsheetExportTable
 * @copyright Copyright 2016 Viktor Grandgeorg
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * SpreadsheetExport table.
 */
class SpreadsheetExportTable extends Omeka_Db_Table
{

    public function getSelect()
    {
        $db = $this->getDb();
        return parent::getSelect();
    }

    public function findBy($params = array(), $limit = null, $page = null)
    {
        $user = current_user();
        $select = $this->getSelectForFindBy($params);
        if ($limit) {
            $this->applyPagination($select, $limit, $page);
        }
        return $this->fetchObjects($select);
    }
}
