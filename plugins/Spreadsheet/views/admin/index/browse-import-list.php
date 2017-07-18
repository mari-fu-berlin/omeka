<?php // var_dump($spreadsheet_exports); // die(); ?>
<table class="full">
    <thead>
        <tr>
            <?php echo browse_sort_links(
                array(
                    __('Name') => 'name',
                    __('Import nach Schema') => 'import',
                    __('Last Modified') => 'updated'
                ),
                array(
                    'link_tag' => 'th scope="col"',
                    'list_tag' => ''
                )
            );
            ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach (loop('spreadsheet_imports') as $spreadsheet_import): ?>
        <tr>
            <td style="width:27%;">
                <span class="name">
                    <?php echo metadata('spreadsheet_import', 'name'); ?>
                </span>
                <ul class="action-links group">
                    <li>
                        <a class="delete-confirm" href="<?php echo html_escape(url('spreadsheet/import/delete-confirm/id/' . metadata('spreadsheet_import', 'id'))); ?>">
                            <?php echo __('Delete'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo html_escape(url('spreadsheet/import/show-sheme/id/' . metadata('spreadsheet_import', 'id'))); ?>">
                            <?php echo __('Schema anzeigen'); ?>
                        </a>
                    </li>
                </ul>
            </td>
            <td>
                <a class="add-spreadsheet button small blue" href="<?php
                    echo html_escape(url('spreadsheet/import/import-by-sheme/id/'
                        . metadata('spreadsheet_import', 'id')));
                ?>"><?php echo __('Excel Datei importieren'); ?></a>
            </td>
            <td><?php echo __('<strong>%1$s</strong> am %2$s',
                metadata('spreadsheet_import', 'modified_username'),
                html_escape(format_date(metadata('spreadsheet_import', 'updated'), Zend_Date::DATETIME_SHORT))); ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
