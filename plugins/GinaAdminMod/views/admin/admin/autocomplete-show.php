<?php
$head = array(
    'bodyclass' => 'gina-mod-mari-admin-autocompletes-show primary',
    'title' => html_escape(__('Mari Verwaltung | Autovervollständigen-Felder')),
    'content_class' => 'horizontal-nav'
);
echo head($head);
echo flash();
// var_dump($autocompletes);
if (has_loop_records('autocompletes')) {
    echo $this->partial('admin/partial-autocomplete-list.php', array('autocompletes' => $autocompletes));
}
echo foot(); ?>
