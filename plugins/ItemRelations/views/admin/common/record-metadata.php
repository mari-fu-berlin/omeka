<?php foreach ($elementsForDisplay as $setName => $setElements): ?>
<div class="element-set">
    <?php if ($showElementSetHeadings): ?>
    <div class="type">Typ: <?php echo str_replace(' Item Type Metadata', '', html_escape(__($setName))); ?></div>
    <?php endif; ?>
    <?php foreach ($setElements as $elementName => $elementInfo): ?>
    <div id="<?php echo text_to_id(html_escape("$setName $elementName")); ?>" class="meta">
        <div class="name">
            <label><?php echo html_escape(__($elementName)); ?></label>
        </div>
        <?php $i = 0; ?>
        <?php foreach ($elementInfo['texts'] as $text):
        $i++;
        if( $i == 1): ?>
            <div class="text"><?php echo $text; ?></div>
        <?php else: ?>
            <div class="text"><?php echo $text; ?></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>
