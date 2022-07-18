<?php
$head = array(
    'bodyclass' => 'gina-mod-mari-admin primary',
    'title' => html_escape(__('Primärzuweisungen an Hand von Sigle konstituierende Nachricht ID setzen')),
    'content_class' => 'horizontal-nav',
);
echo head($head);
echo flash();
?>
<?php if (empty($log)): ?>
    <h4><span style="color:#060;">Keine Änderungen vorgenommen.</span><br>
<?php else: ?>
    <h4 style="color:#060;">Objekte mit neuer Zuweisung:</h4>
    <ul>
    <?php foreach ($log as $object_id => $ok): ?>
        <li><a target="_blank" href="<?php echo ADMIN_BASE_URL; ?>/items/edit/<?php echo $object_id; ?>#item-relations-metadata"><?php echo $object_id; ?></a></li>
    <?php endforeach;?>
    </ul>
<?php endif;?>
<?php echo foot(); ?>
