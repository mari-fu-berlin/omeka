<?php
$head = array(
    'bodyclass' => 'exhibit-color-scheme-designer primary',
    'title' => html_escape(__('Schemata für Spreadsheets | Übersicht')),
    'content_class' => 'horizontal-nav'
);
echo head($head);
echo flash();
?>
<h2>Objekte als Excel Datei exportieren</h2>
<a class="add-spreadsheet button small green" href="<?php echo html_escape(url('spreadsheet/index/add')); ?>"><?php echo __('Schema hinzufügen'); ?></a>
<?php if (!has_loop_records('spreadsheet_exports')): ?>
    <p><?php echo __('Es sind keine Export-schemata definiert.'); ?>
    <a href="<?php echo html_escape(url('spreadsheet/index/add')); ?>"><?php echo __('Schema hinzufügen'); ?></a></p>
<?php else: ?>
    <?php echo $this->partial('index/browse-list.php', array('spreadsheet_exports' => $spreadsheet_exports)); ?>
<?php endif; ?>
<h2>Objekte aus Excel Datei importieren</h2>
<a class="add-spreadsheet button small green" href="<?php echo html_escape(url('spreadsheet/import/index')); ?>"><?php echo __('Schema hinzufügen &amp; Import erzeugen'); ?></a>
<?php if (has_loop_records('spreadsheet_imports')): ?>
    <?php echo $this->partial('index/browse-import-list.php', array('spreadsheet_imports' => $spreadsheet_imports)); ?>
<?php endif; ?>
<?php echo foot(); ?>
