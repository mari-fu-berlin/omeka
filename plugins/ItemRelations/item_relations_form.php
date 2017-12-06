<p>
<?php
$link = '<a href="' . url('item-relations/vocabularies/') . '">'
      . __('Browse Vocabularies') . '</a>';

$hasDeletedRelations = false;
?>
</p>
<table id="item-relations-table">
    <thead>
    <tr>
        <th><?php echo __('Subject'); ?></th>
        <th><?php echo __('Relation'); ?></th>
        <th><?php echo __('Object'); ?></th>
        <th><?php echo __('Delete'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $subjectRelationAnnotationElements = array();
    foreach ($subjectRelations as $subjectRelation):
        if ($subjectRelation['state'] !== 'current') { $hasDeletedRelations = true; continue; }
        $subjectRelationAnnotationElement = 'item_relations_annotation[' . $subjectRelation['item_relation_id'] . ']';
        // var_dump($annotations['subjectRelations'][$subjectRelation['item_relation_id']]);
        array_push($subjectRelationAnnotationElements, $subjectRelationAnnotationElement);
    ?>
    <tr class="item-relations-entry">
        <td><?php echo __('This Item'); ?></td>
        <td><?php echo $subjectRelation['relation_text']; ?></td>
        <td><a href="<?php echo url('items/show/' . $subjectRelation['object_item_id']); ?>" target="_blank"><?php echo $subjectRelation['object_item_title']; ?></a></td>
        <td><input type="checkbox" name="item_relations_item_relation_delete[]" value="<?php echo $subjectRelation['item_relation_id']; ?>" /></td>
    </tr>
    <tr>
        <td colspan="4">
            <label for="<?php echo $subjectRelationAnnotationElement; ?>" class="clearfix item-relation-annotation-label">
                <div class="title">Annotationen</div>
                <?php if($annotations['subjectRelations'][$subjectRelation['item_relation_id']]{'added'}): ?>
                <div class="edit">
                    <a href="<?php echo ADMIN_BASE_URL; ?>/item-relations/annotation/history/rid/<?php echo $subjectRelation['item_relation_id']; ?>">
                        <i class="fa fa-history" aria-hidden="true"></i>
                        <span class="sr-only">Annotationshistorie Bearbeiten</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php if($annotations['subjectRelations'][$subjectRelation['item_relation_id']]{'user_name'}): ?>
                <div class="user">
                    <i class="fa fa-user" aria-hidden="true" title="Benutzer"></i>
                    <a href="mailto:<?php echo $annotations['subjectRelations'][$subjectRelation['item_relation_id']]{'user_email'}; ?>">
                        <?php echo $annotations['subjectRelations'][$subjectRelation['item_relation_id']]{'user_name'}; ?>
                    </a>
                </div>
                <?php endif; ?>
                <?php if($annotations['subjectRelations'][$subjectRelation['item_relation_id']]{'added'}): ?>
                <div class="time">
                    <i class="fa fa-clock-o" aria-hidden="true" title="Letze Bearbeitung"></i>
                    <?php echo date("d.m.Y H:i:s", strtotime($annotations['subjectRelations'][$subjectRelation['item_relation_id']]{'added'})); ?>
                </div>
                <?php endif; ?>
            </label>
            <textarea name="<?php echo $subjectRelationAnnotationElement; ?>" id="<?php echo $subjectRelationAnnotationElement; ?>" style="width:100%;">
                <?php if (array_key_exists($subjectRelation['item_relation_id'], $annotations['subjectRelations'])) {
                    echo htmlspecialchars($annotations['subjectRelations'][$subjectRelation['item_relation_id']]{'annotation'});
                } ?>
            </textarea>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php
    $objectRelationAnnotationElements = array();
    foreach ($objectRelations as $objectRelation):
        if ($objectRelation['state'] !== 'current') { $hasDeletedRelations = true; continue; }
        $objectRelationAnnotationElement = 'item_relations_annotation[' . $objectRelation['item_relation_id'] . ']';
        array_push($objectRelationAnnotationElements, $objectRelationAnnotationElement);
    ?>
    <tr class="item-relations-entry">
        <td><a href="<?php echo url('items/show/' . $objectRelation['subject_item_id']); ?>" target="_blank"><?php echo $objectRelation['subject_item_title']; ?></a></td>
        <td><?php echo $objectRelation['relation_text']; ?></td>
        <td><?php echo __('This Item'); ?></td>
        <td><input type="checkbox" name="item_relations_item_relation_delete[]" value="<?php echo $objectRelation['item_relation_id']; ?>" /></td>
    </tr>
    <tr>
        <td colspan="4">
            <label for="<?php echo $objectRelationAnnotationElement; ?>" class="clearfix item-relation-annotation-label">
                <div class="title">Annotationen</div>
                <?php if($annotations['objectRelations'][$objectRelation['item_relation_id']]{'added'}): ?>
                <div class="edit">
                    <a href="<?php echo ADMIN_BASE_URL; ?>/item-relations/annotation/history/rid/<?php echo $objectRelation['item_relation_id']; ?>">
                        <i class="fa fa-history" aria-hidden="true"></i>
                        <span class="sr-only">Annotationshistorie Bearbeiten</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php if($annotations['objectRelations'][$objectRelation['item_relation_id']]{'user_name'}): ?>
                <div class="user">
                    <i class="fa fa-user" aria-hidden="true" title="Benutzer"></i>
                    <a href="mailto:<?php echo $annotations['subjectRelations'][$subjectRelation['item_relation_id']]{'user_email'}; ?>">
                        <?php echo $annotations['objectRelations'][$objectRelation['item_relation_id']]{'user_name'}; ?>
                    </a>
                </div>
                <?php endif; ?>
                <?php if($annotations['objectRelations'][$objectRelation['item_relation_id']]{'added'}): ?>
                <div class="time">
                    <i class="fa fa-clock-o" aria-hidden="true" title="Letze Bearbeitung"></i>
                    <?php echo date("d.m.Y H:i:s", strtotime($annotations['objectRelations'][$objectRelation['item_relation_id']]{'added'})); ?>
                </div>
                <?php endif; ?>
            </label>
            <textarea name="<?php echo $objectRelationAnnotationElement; ?>" id="<?php echo $objectRelationAnnotationElement; ?>" style="width:100%;">
                <?php if (array_key_exists($objectRelation['item_relation_id'], $annotations['objectRelations'])) {
                    echo htmlspecialchars($annotations['objectRelations'][$objectRelation['item_relation_id']]{'annotation'});
                } ?>
            </textarea>
        </td>
    </tr>
    <?php endforeach; ?>
    <!-- new relation -->
    <tr class="item-relations-entry item-relations-new-entry">
        <td><?php echo __('This Item'); ?></td>
        <td><?php echo get_view()->formSelect('item_relations_property_id[]', null, array('multiple' => false), $formSelectProperties); ?></td>
        <td>
            <div class="ui-widget">
                <div class="search clearfix">
                    <label>Suche</label>
                </div>
                <div class="input-id clearfix">
                    <label><?php echo __('Item ID'); ?></label>
                    <?php 
                    // echo get_view()->formText('item_relations_item_relation_object_item_id[]', null, array('size' => 8)); 
                    ?>
                    <?php echo get_view()->formText('item_relations_item_relation_object_item_id[]', null); ?>
                </div>
                <div class="selected-autocomplete"></div>
            </div>
        </td>
        <td><span style="color:#ccc;">n/a</span></td>
    </tr>
    <tr class="item-relations-entry-annotation">
        <td colspan="4">
            <label for="item_relations_new_annotation[0]" style="margin-bottom: 8px; display: block;">Annotationen</label>
            <textarea name="item_relations_new_annotation[0]" id="item_relations_new_annotation[0]" style="width:100%;" rows="25"></textarea>
        </td>
    </tr>
    </tbody>
</table>
<button type="button" class="item-relations-add-relation green" id="item-relations-add-relation"><?php echo __('Add a Relation'); ?></button>
<?php if($hasDeletedRelations === true): ?>
<button type="button" class="item-relations-show-deleted grey" id="item-relations-show-deleted">Zeige gelöschte Beziehungen</button>
<?php endif; ?>

<div class="item-relations-deleted" id="item-relations-deleted">
    <h3>Gelöschte Beziehungen</h3>
    <table id="deleted-item-relations-table" class="deleted-item-relations-table inactive">
        <thead>
        <tr>
            <th><?php echo __('Subject'); ?></th>
            <th><?php echo __('Relation'); ?></th>
            <th><?php echo __('Object'); ?></th>
            <th class="item-relations-deleted-annotations-history"><i class="fa fa-history" aria-hidden="true"></i></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($subjectRelations as $subjectRelation): ?>
        <?php if ($subjectRelation['state'] === 'current') { continue; } ?>
        <tr class="inactive-item-relations-entry">
            <td><?php echo __('This Item'); ?></td>
            <td><?php echo $subjectRelation['relation_text']; ?></td>
            <td colspan="2">
                <a href="<?php echo url('items/show/' . $subjectRelation['object_item_id']); ?>"><?php echo $subjectRelation['object_item_title']; ?></a>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="show-current-annotation">
                <?php echo $annotations['subjectRelations'][$subjectRelation['item_relation_id']]{'annotation'}; ?>
            </td>
            <td class="item-relations-deleted-annotations-history">
                <span class="edit">
                    <a href="<?php echo ADMIN_BASE_URL; ?>/item-relations/annotation/dlhistory/rid/<?php echo $subjectRelation['item_relation_id']; ?>">
                        <i class="fa fa-history" aria-hidden="true"></i>
                    </a>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php foreach ($objectRelations as $objectRelation): ?>
        <?php if ($objectRelation['state'] === 'current') { continue; } ?>
        <tr class="inactive-item-relations-entry">
            <td><a href="<?php echo url('items/show/' . $objectRelation['subject_item_id']); ?>"><?php echo $objectRelation['subject_item_title']; ?></a></td>
            <td><?php echo $objectRelation['relation_text']; ?></td>
            <td colspan="2"><?php echo __('This Item'); ?></td>
        </tr>
        <tr>
            <td colspan="3" class="show-current-annotation">
                <?php echo $annotations['objectRelations'][$objectRelation['item_relation_id']]{'annotation'}; ?>
            </td>
            <td class="item-relations-deleted-annotations-history">
                <span class="edit">
                    <a href="<?php echo ADMIN_BASE_URL; ?>/item-relations/annotation/dlhistory/rid/<?php echo $objectRelation['item_relation_id']; ?>">
                        <i class="fa fa-history" aria-hidden="true"></i>
                    </a>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="<?php echo WEB_PLUGIN; ?>/ItemRelations/views/admin/javascripts/autocomplete/dist/jquery.item-relations.min.js"></script>
<script type="text/javascript">
jQuery(function($){
    $('#item-relations-table').itemrelationsAutocomplete({
        adminBaseUrl: '<?php echo ADMIN_BASE_URL; ?>',
        warnNoItemFound: '<?php echo __('Kein passendes Objekt gefunden!'); ?>',
    });
    $('#item-relations-show-deleted').click(function() {
        $('#item-relations-deleted').toggle('slow');
    });
});
jQuery(window).load(function($) {
    <?php foreach ($subjectRelationAnnotationElements as $subjectRelationAnnotationElement): ?>
    tinyMCE.execCommand('mceAddControl', true, '<?php echo $subjectRelationAnnotationElement; ?>');
    <?php endforeach; ?>
    <?php foreach ($objectRelationAnnotationElements as $objectRelationAnnotationElement): ?>
    tinyMCE.execCommand('mceAddControl', true, '<?php echo $objectRelationAnnotationElement; ?>');
    <?php endforeach; ?>
    tinyMCE.execCommand('mceAddControl', true, 'item_relations_new_annotation[0]');
});
</script>
