<?php
$head = array(
    'bodyclass' => 'gina-mod-mari-admin-sanitize-items primary',
    'title' => html_escape(__('Mari Verwaltung | Objekte bereinigen')),
    'content_class' => 'horizontal-nav'
);
echo head($head);
echo flash();
?>

<?php if (!empty($msg['warn'])): ?>
<p style="color:#c00;"><strong>Objekte mit Angabe einer Sigle-Quelle (Text), die aber nicht zugewiesen werden konnten (keine oder keine eindeutige ID gefunden).</strong></p>
<div style="-webkit-column-count: 4; -moz-column-count: 4; column-count: 4;">
<ul>
<?php foreach ($msg['warn'] as $id): ?>
    <li><a target="_blank" href="<?php echo ADMIN_BASE_URL . '/items/show/' . $id; ?>"><?php echo $id; ?></a></li>
<?php endforeach; ?>
<ul>
</div>
<?php endif; ?>

<?php if (!empty($msg['warn-notext'])): ?>
<p style="color:#c00;"><strong>Objekte mit Angabe einer Sigle-ID aber ohne Sigle-Quelle (Text), die aber nicht zugewiesen werden konnten, da das Objekt mit der angegebenen ID keinen Sigle-Text hat.</strong></p>
<div style="-webkit-column-count: 4; -moz-column-count: 4; column-count: 4;">
<ul>
<?php foreach ($msg['warn-notext'] as $id): ?>
    <li><a target="_blank" href="<?php echo ADMIN_BASE_URL . '/items/show/' . $id; ?>"><?php echo $id; ?></a></li>
<?php endforeach; ?>
<ul>
</div>
<?php endif; ?>

<?php if (!empty($msg['warn-bad-id'])): ?>
<p style="color:#c00;"><strong>Objekte mit Angabe einer Sigle-ID und einer Sigle-Quelle (Text), bei denen die Überprüfung der Quelle nicht erfolgreich war.</strong></p>
<div style="-webkit-column-count: 4; -moz-column-count: 4; column-count: 4;">
<ul>
<?php foreach ($msg['warn-bad-id'] as $id): ?>
    <li><a target="_blank" href="<?php echo ADMIN_BASE_URL . '/items/show/' . $id; ?>"><?php echo $id; ?></a></li>
<?php endforeach; ?>
<ul>
</div>
<?php endif; ?>

<?php if (!empty($msg['empty'])): ?>
<p style="color:#900;"><strong>Objekte, die keinen Eintrag für die Sigle Quelle haben (weder Text noch ID).</strong></p>
<div style="-webkit-column-count: 4; -moz-column-count: 4; column-count: 4;">
<ul>
<?php foreach ($msg['empty'] as $id): ?>
    <li><a target="_blank" href="<?php echo ADMIN_BASE_URL . '/items/show/' . $id; ?>"><?php echo $id; ?></a></li>
<?php endforeach; ?>
<ul>
</div>
<?php endif; ?>

<?php if (!empty($msg['set-id'])): ?>
<p style="color:#3d903d;"><strong>Objekte mit Angabe nur einer Sigle-Quelle (Text), die erfolgreich zugewiesen werden konnten.</strong></p>
<div style="-webkit-column-count: 4; -moz-column-count: 4; column-count: 4;">
<ul>
<?php foreach ($msg['set-id'] as $id): ?>
    <li><a target="_blank" href="<?php echo ADMIN_BASE_URL . '/items/show/' . $id; ?>"><?php echo $id; ?></a></li>
<?php endforeach; ?>
<ul>
</div>
<?php endif; ?>

<?php if (!empty($msg['set-text'])): ?>
<p style="color:#3d903d;"><strong>Objekte mit Angabe einer Sigle-ID, bei denen der Text hinzugefügt werden konnte.</strong></p>
<div style="-webkit-column-count: 4; -moz-column-count: 4; column-count: 4;">
<ul>
<?php foreach ($msg['set-text'] as $id): ?>
    <li><a target="_blank" href="<?php echo ADMIN_BASE_URL . '/items/show/' . $id; ?>"><?php echo $id; ?></a></li>
<?php endforeach; ?>
<ul>
</div>
<?php endif; ?>

<?php if (!empty($msg['change-text'])): ?>
<p style="color:#3d903d;"><strong>Objekte, die erfolgreich aktualisiert wurden, bei denen der Text des Autovervollständigen Feldes nicht mit dem Sigle der Quelle übereingestimmt hat.</strong></p>
<div style="-webkit-column-count: 4; -moz-column-count: 4; column-count: 4;">
<ul>
<?php foreach ($msg['change-text'] as $id): ?>
    <li><a target="_blank" href="<?php echo ADMIN_BASE_URL . '/items/show/' . $id; ?>"><?php echo $id; ?></a></li>
<?php endforeach; ?>
<ul>
</div>
<?php endif; ?>

<?php if (!empty($msg['ok'])): ?>
<p style="color:#060;"><strong>Objekte bei denen keine Änderungen vorgenommen werden mussten - alle OK.</strong></p>
<div style="-webkit-column-count: 4; -moz-column-count: 4; column-count: 4;">
<ul>
<?php foreach ($msg['ok'] as $id): ?>
    <li><a target="_blank" href="<?php echo ADMIN_BASE_URL . '/items/show/' . $id; ?>"><?php echo $id; ?></a></li>
<?php endforeach; ?>
<ul>
</div>
<?php endif; ?>

<?php echo foot(); ?>