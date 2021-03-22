<?php
    $date = date('Y-m-01') . ' - ' . date('Y-m-t');
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data JV', 'sejoli-jv'); ?>
	</h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>

            <div class="sejoli-form-information" style="float:left;">
                <h3 id='sejoli-filter-date'></h3>
            </div>

            <div class="sejoli-form-filter box" style='float:right;'>

                <button type="button" name="button" class='export-csv button'><?php _e('Export CSV', 'sejoli-jv'); ?></button>
                <button type="button" name="button" class='add-data button'><?php _e('Input Data', 'sejoli-jv'); ?></button>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli-jv'); ?></button>

                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <select class="autosuggest filter" name="product_id"></select>
                    <input type="text" class='filter' name="date-range" value="<?php echo $date; ?>" placeholder="<?php _e('Pencarian berdasarkan tanggal', 'sejoli-jv'); ?>">
                    <?php wp_nonce_field('search-jv', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli-jv'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli-jv'); ?></button> -->
                </div>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-jv" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><?php _e('Tanggal', 'sejoli-jv'); ?></th>
                        <th><?php _e('Detil', 'sejoli-jv'); ?></th>
                        <th><?php _e('Nilai', 'sejoli-jv'); ?></th>
                        <th><?php _e('Tipe', 'sejoli-jv'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Tanggal', 'sejoli-jv'); ?></th>
                        <th><?php _e('Detil', 'sejoli-jv'); ?></th>
                        <th><?php _e('Nilai', 'sejoli-jv'); ?></th>
                        <th><?php _e('Tipe', 'sejoli-jv'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">

let sejoli_table;

(function( $ ) {
	'use strict';
    $(document).ready(function() {

        sejoli.helper.select_2(
            "select[name='product_id']",
            sejoli_admin.product.select.ajaxurl,
            sejoli_admin.product.placeholder
        );

        sejoli.helper.daterangepicker("input[name='date-range']");

        sejoli.helper.filterData();

        sejoli_table = $('#sejoli-jv').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: true,
            serverSide: false,
            info: false,
            paging: false,
            ajax: {
                type: 'POST',
                url:  sejoli_admin.jv.single_table.ajaxurl,
                data: function(data) {
                    data.user    = sejoli_admin.jv.single_table.user;
                    data.filter  = sejoli.var.search;
                    data.action  = 'sejoli-jv-single-table';
                    data.nonce   = sejoli_admin.jv.single_table.nonce
                    data.backend = true;
                }
            },
            pageLength : 50,
            lengthMenu : [
                [10, 50, 100, 200],
                [10, 50, 100, 200],
            ],
            order: [
                [ 0, "desc" ]
            ],
            columnDefs: [
                {
                    targets: [1,2,3],
                    orderable: false
                },{
                    targets: 0,
                    data:    'date'
                },{
                    targets: 1,
                    data:    'note'
                },{
                    targets:   2,
                    width:     '120px',
                    data:      'value',
                    className: 'price'
                },{
                    targets: 3,
                    width:   '80px',
                    data:    'type'
                }
            ]
        });

        sejoli_table.on('preXhr',function(){
            sejoli.helper.blockUI('.sejoli-table-holder');
        });

        sejoli_table.on('xhr',function(){
            sejoli.helper.unblockUI('.sejoli-table-holder');
                $('#sejoli-filter-date').html('Data tanggal : ' + date_range);
        });

        $(document).on('click', '.toggle-search', function(){
            $('.sejoli-form-filter-holder').toggle();
        });

        $(document).on('click', '.do-search', function(){
            sejoli.helper.filterData();
            sejoli_table.ajax.reload();
            $('.sejoli-form-filter-holder').hide();
        });

    });
})(jQuery);
</script>
