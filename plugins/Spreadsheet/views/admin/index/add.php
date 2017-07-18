<?php
$head = array(
    'bodyclass' => 'spreadsheet primary',
    'title' => html_escape(__('Schema hinzufügen'))
);
echo head($head);
?>
<?php echo flash(); ?>
<?php echo $form; ?>
<?php echo foot(); ?>
