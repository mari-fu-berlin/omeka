<?php
$head = array(
    'bodyclass'     => 'gina-mod-mari-admin primary',
    'title'         => html_escape(__('Mari Verwaltung | Übersicht')),
    'content_class' => 'horizontal-nav'
);
echo head($head);
echo flash();
?>
<table>
    <thead>
        <tr>
            <th>
                <?php echo __('Task'); ?>
            </th>
            <th>
                Aktion
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Konfiguration der Autovervollständigen-Felder</td>
            <td style="text-align:center; vertical-align:middle;">
                <a class="add-custom-tile button small green" style="margin:0;" href="<?php echo html_escape(url('/gina-admin-mod/admin/autocomplete-show')); ?>">
                <i class="fa fa-eye" aria-hidden="true"></i> <?php echo __('Anzeigen'); ?>
                </a>
            </td>
        </tr>
        <tr>
            <td>
                Autovervollständigen-Felder aller Objekte untersuchen.<br>
                Eindeutig identifizierbare Siglen automatisch als ID setzten,<br>
                Fehler, wenn möglich bereinigen und Protokoll ausgeben.
            </td>
            <td style="text-align:center; vertical-align:middle;">
                <a class="add-custom-tile button small blue" style="margin:0;" href="<?php echo html_escape(url('/gina-admin-mod/admin/sanitize-items')); ?>">
                <i class="fa fa-cog" aria-hidden="true"></i> <?php echo __('Ausführen'); ?>
                </a>
            </td>
        </tr>
        <tr>
            <td>Objekt-Beziehungen: Primärzuweisungen anhand von &quot;Sigle konstituierende Nachricht ID&quot; setzen.</td>
            <td style="text-align:center; vertical-align:middle;">
                <a class="add-custom-tile button small blue" style="margin:0;" href="<?php echo html_escape(url('/gina-admin-mod/admin/add-primary-item-relations')); ?>">
                <i class="fa fa-cog" aria-hidden="true"></i> <?php echo __('Ausführen'); ?>
                </a>
            </td>
        </tr>
    </tbody>
</table>
<?php echo foot(); ?>
