<table class="full">
    <thead>
        <tr>
            <?php echo browse_sort_links(
                array(
                    __('Objekttyp') => 'item_type_id',
                    __('Autocomp.-Feld Name') => 'autocomplete_field_name',
                    __('Autocomp.-Feld ID') => 'autocomplete_field_id',
                    __('Auto-Feld Name') => 'auto_field_name',
                    __('Auto-Feld ID') => 'auto_field_id',
                    __('Autocomp.-Objektypen IDs') => 'autocomplete_item_type_ids',
                ),
                array(
                    'link_tag' => 'th scope="col"',
                    'list_tag' => ''
                )
            );
            ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach (loop('autocompletes') as $autocomplete): ?>
        <tr>
            <td>
                <span title="<?php echo $autocomplete->item_type_name; ?>"><?php echo $autocomplete->item_type_id; ?></span>
            </td>
            <td>
                <?php echo $autocomplete->autocomplete_field_name; ?>
            </td>
            <td>
                <?php echo $autocomplete->autocomplete_field_id; ?>
            </td>
            <td>
                <?php echo $autocomplete->auto_field_name; ?>
            </td>
            <td>
                <?php echo $autocomplete->auto_field_id; ?>
            </td>
            <td>
                <?php echo $autocomplete->autocomplete_item_type_ids; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
(function($) {
    $('.link button').click(function(){
        $(this).parent().children('input').select();
        document.execCommand("copy");
    });
})(jQuery);
</script>
