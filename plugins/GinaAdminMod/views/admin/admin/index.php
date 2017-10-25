<?php
$head = array(
    'bodyclass' => 'gina-mod-mari-admin primary',
    'title' => html_escape(__('Mari Verwaltung | Übersicht')),
    'content_class' => 'horizontal-nav'
);
echo head($head);
echo flash();
?>
<a class="add-custom-tile button small green" href="<?php echo html_escape(url('/gina-admin-mod/admin/autocomplete/show')); ?>"><?php echo __('Konfiguration der Autovervollständigen-Felder'); ?></a><br>
<a class="add-custom-tile button small green" href="<?php echo html_escape(url('/gina-admin-mod/admin/sanitize-items')); ?>"><?php echo __('Autovervollständigen-Felder aller Objekte bereinigen'); ?></a>
<?php echo foot(); ?>
