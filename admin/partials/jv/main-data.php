<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data JV', 'sejoli-jv'); ?>
	</h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>

            <div class="sejoli-form-filter box" style='float:right;'>
                <button type="button" name="button" class='export-csv button'><?php _e('Export CSV', 'sejoli-jv'); ?></button>
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
                        <th><?php _e('User', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pendapatan', 'sejoli-jv'); ?></th>
                        <th><?php _e('Detil', 'sejoli-jv'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('User', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pendapatan', 'sejoli-jv'); ?></th>
                        <th><?php _e('Detil', 'sejoli-jv'); ?></th>
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
            "select[name='user_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.user.placeholder
        );

        sejoli.helper.select_2(
            "select[name='affiliate_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.affiliate.placeholder
        );

        sejoli.helper.select_2(
            "select[name='product_id']",
            sejoli_admin.product.select.ajaxurl,
            sejoli_admin.product.placeholder
        );

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
                url: sejoli_admin.jv.table.ajaxurl,
                data: function(data) {
                    data.filter = sejoli.var.search;
                    data.action = 'sejoli-jv-table';
                    data.nonce = sejoli_admin.jv.table.nonce
                    data.backend  = true;
                }
            },
            pageLength : 50,
            lengthMenu : [
                [10, 50, 100, 200],
                [10, 50, 100, 200],
            ],
            order: [
                [ 0, "asc" ]
            ],
            columnDefs: [
                {
                    targets: [0],
                    orderable: false
                },{
                    targets: 0,
                    data : 'display_name',
                    render: function(data, type, full) {

                        let tmpl = $.templates('#user-detail');

                        return tmpl.render({
                            id : full.user_id,
                            display_name : full.display_name,
                            email : full.user_email,
                            detail_url: full.detail_url,
                        })
                    }
                },{
                    targets: 1,
                    width:  '12n0px',
                    data: 'earning',
                    className: 'price'
                },{
                    targets: 2,
                    width:  '80px',
                    render: function(data, type, full) {
                        return 'action'
                    }
                }
            ]
        });

        sejoli_table.on('preXhr',function(){
            sejoli.helper.blockUI('.sejoli-table-holder');
        });

        sejoli_table.on('xhr',function(){
            sejoli.helper.unblockUI('.sejoli-table-holder');
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
<script id='user-detail' type="text/x-jsrender">
<a type='button' class='ui mini button' href='{{:detail_url}}' target='_blank'>DETAIL</a> {{:display_name}}
<div style='line-height:220%'>
    <span class="ui purple label"><i class="envelope icon"></i>{{:email}}</span>
</div>
</script>
