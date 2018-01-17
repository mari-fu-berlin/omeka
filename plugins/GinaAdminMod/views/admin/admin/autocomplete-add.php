<?php
$head = array(
    'bodyclass' => 'gina-mod-mari-admin-autocompletes-show primary',
    'title' => html_escape(__('Mari Verwaltung | Autovervollständigen-Feld hinzufügen')),
    'content_class' => 'horizontal-nav'
);
echo head($head);
echo flash();
echo $form;
?>
<script>
var ginaAutocompleteElements = <?php echo $elements; ?>;
(function($) {
    function ginaHandleAutocompleteElements (selected) {
        $('#autocomplete_field_id option').remove();
        $.each(ginaAutocompleteElements[selected], function(id, name) {
            $('#autocomplete_field_id').append($('<option>', {
                value: id,
                text : name

            }));
        });
        $('#auto_field_id option').remove();
        $.each(ginaAutocompleteElements[selected], function(id, name) {
            $('#auto_field_id').append($('<option>', {
                value: id,
                text : name

            }));
        });
    }
    $('#item_type_id').change(function() {
        ginaHandleAutocompleteElements(this.value);
    });
})(jQuery);
</script>
<?php echo foot(); ?>
