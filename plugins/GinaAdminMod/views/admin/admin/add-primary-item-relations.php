<?php
$head = array(
    'bodyclass'     => 'gina-mod-mari-admin primary',
    'title'         => html_escape(__('Primärzuweisungen an Hand von Sigle konstituierende Nachricht ID setzen')),
    'content_class' => 'horizontal-nav'
);
echo head($head);
echo flash();
?>
<?php if (empty($log)): ?>
    <h4><span style="color:#060;">Keine Änderungen vorgenommen.</span><br>
    Alle Objekte mit &quot;Sigle konstituierende Nachricht ID&quot; haben bereits eine Primärbeziehung.</h4>
<?php else: ?>
    <h4 style="color:#060;">Shared Objects mit neuer Primärbeziehung:</h4>
    <ul>
    <?php foreach ($log as $object_id => $ok): ?>
        <li><a target="_blank" href="<?php echo ADMIN_BASE_URL; ?>/items/edit/<?php echo $object_id; ?>#item-relations-metadata"><?php echo $object_id; ?></a></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php if (!empty($noSigleIdObjects)): ?>
    <h4 style="color:#c00;">Shared Objects, die über keinen Wert in &quot;Sigle konstituierende Nachricht ID&quot; verfügen!</h4>
    <ul>
    <?php foreach ($noSigleIdObjects as $object_id): ?>
        <li><a target="_blank" href="<?php echo ADMIN_BASE_URL; ?>/items/edit/<?php echo $object_id; ?>"><?php echo $object_id; ?></a></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php echo foot(); ?>
