<?php
$head = array('bodyclass' => 'spreadsheet primary',
              'title' => __('Schema "%s" bearbeiten', metadata('spreadsheet', 'name')));
echo head($head);
?>
<?php echo flash(); ?>
<p><?php echo __('Dieses Schema wurde erzeugt von <strong>%1$s</strong> am %2$s Uhr.<br>Es wurde zuletzt bearbeitet von <strong>%3$s</strong> am %4$s Uhr.',
    metadata('spreadsheet', 'created_username'),
    html_escape(format_date(metadata('spreadsheet', 'inserted'), Zend_Date::DATETIME_SHORT)),
    metadata('spreadsheet', 'modified_username'),
    html_escape(format_date(metadata('spreadsheet', 'updated'), Zend_Date::DATETIME_SHORT))); ?></p>
<?php echo $form; ?>
<?php echo foot(); ?>
