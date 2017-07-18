<?php // var_dump($spreadsheet_exports); // die(); ?>
<table class="full">
    <thead>
        <tr>
            <?php echo browse_sort_links(
                array(
                    __('Name') => 'name',
                    __('Spreadsheet erzeugen') => 'generate',
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
    <?php foreach (loop('spreadsheet_exports') as $spreadsheet_export): ?>
        <tr>
            <td style="width:27%;">
                <span class="name">
                    <?php echo metadata('spreadsheet_export', 'name'); ?>
                </span>
                <ul class="action-links group">
                    <li><a class="edit" href="<?php echo html_escape(url('spreadsheet/index/edit/id/' . metadata('spreadsheet_export', 'id'))); ?>">
                        <?php echo __('Edit'); ?>
                    </a></li>
                    <li><a class="delete-confirm" href="<?php echo html_escape(url('spreadsheet/index/delete-confirm/id/' . metadata('spreadsheet_export', 'id'))); ?>">
                        <?php echo __('Delete'); ?>
                    </a></li>
                </ul>
            </td>
            <td>
                <a class="add-spreadsheet button small blue" href="<?php
                    echo html_escape(url('spreadsheet/index/generate/id/'
                        . metadata('spreadsheet_export', 'id')));
                ?>"><?php echo __('Spreadsheet erzeugen'); ?></a>
            </td>
            <td><?php echo __('<strong>%1$s</strong> am %2$s',
                metadata('spreadsheet_export', 'modified_username'),
                html_escape(format_date(metadata('spreadsheet_export', 'updated'), Zend_Date::DATETIME_SHORT))); ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
