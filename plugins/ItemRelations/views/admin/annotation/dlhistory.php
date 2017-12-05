<?php echo head(array('title' => __('Annotation Historie'))); ?>
<div class="annotation-history">
    <h2>Objekt Beziehung</h2>
    <div class="annotation-history-relation-type">
        <strong>
            <?php echo $relation->property_label; ?>
        </strong>
        <br>
        <small>
            <?php echo $relation->property_description; ?>
        </small>
    </div>
    <table class="subject-object">
        <thead>
            <tr>
                <th><?php echo __('Subject'); ?></th>
                <th><?php echo __('Object'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div>
                        <a href="<?php echo ADMIN_BASE_URL; ?>/items/edit/<?php echo $subject->id; ?>#item-relations-metadata">
                            <?php echo metadata($subject, array('Dublin Core', 'Title'), array('no_filter' => true)); ?>
                        </a>
                    </div>
                    <div class="container-details-show">
                        <button type="button" class="button grey" id="subject-details-show">
                            <i class="fa fa-chevron-down" aria-hidden="true"></i> Metadaten
                        </button>
                    </div>
                    <div class="clearfix subject-details" id="subject-details">
                        <?php echo all_element_texts($subject); ?>
                    </div>
                </td>
                <td>
                    <div>
                        <a href="<?php echo ADMIN_BASE_URL; ?>/items/edit/<?php echo $object->id; ?>#item-relations-metadata">
                            <?php echo metadata($object, array('Dublin Core', 'Title'), array('no_filter' => true)); ?>
                        </a>
                    </div>
                    <div class="container-details-show">
                        <button type="button" class="button grey" id="object-details-show">
                            <i class="fa fa-chevron-down" aria-hidden="true"></i> Metadaten
                        </button>
                    </div>
                    <div class="clearfix object-details" id="object-details">
                        <?php echo all_element_texts($object); ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="annotations">
    <?php foreach ($history as $key => $annotation): ?>
        <div class="annotation <?php echo $annotation->state; ?>">
            <div class="annotation-meta">
                <span class="time">
                    <i class="fa fa-clock-o" aria-hidden="true" title="Letze Bearbeitung"></i>
                    <?php echo date("d.m.Y H:i:s", strtotime($annotation->added)); ?>
                </span>
                <span class="user">
                    <i class="fa fa-user" aria-hidden="true" title="Benutzer"></i>
                    <a href="mailto:<?php echo $annotation->user_email; ?>">
                        <?php echo $annotation->user_name; ?>
                    </a>
                </span>
                <?php if($currentAnnotation->id !== $annotation->id): ?>
                <span class="compare">
                <a class="compare-link" href="<?php 
                        echo ADMIN_BASE_URL; ?>/item-relations/annotation/compare/currentid/<?php 
                        echo $currentAnnotation->id; ?>/compareid/<?php 
                        echo $annotation->id; ?>">
                    <i class="fa fa-exchange fa-lg" aria-hidden="true" title="Diese mit aktueller Annotation vergleichen"></i>
                </a>
                </span>
                <?php endif; ?>
            </div>
            <div class="text">
                <?php echo $annotation->annotation; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<script>
jQuery(function($){
    $('#subject-details-show').click(function() {
        $('#subject-details').toggle('slow');
        var icon = $('i', this);
        if (icon.hasClass('fa-chevron-down')) {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    });
    $('#object-details-show').click(function() {
        var icon = $('i', this);
        if (icon.hasClass('fa-chevron-down')) {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
        $('#object-details').toggle('slow');
    });
    $('.compare-link').click(function(event) {
        event.preventDefault();
        event.stopPropagation();
        var lightboxContainer = $('<div class="easylightbox"/>');
        var LightBoxContent = $('<div class="content"/>');
        var LightBoxHeader = $('<div class="header"><i class="fa fa-times fa-2x close" aria-hidden="true" id="easylightbox-close"></i></div>');
        var url = $(this).attr('href');
        $.get($(this).attr('href'), function(data) {
            LightBoxContent.html('' + data);
        });
        lightboxContainer.append(LightBoxHeader);
        lightboxContainer.append(LightBoxContent);
        $('body').append(lightboxContainer);
        $('#easylightbox-close').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            lightboxContainer.remove();
        });
    });
});
</script>
<?php echo foot(); ?>