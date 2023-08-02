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
                    <input type="hidden" class="filter" name="user_id" value='<?php echo $_GET['user_id']; ?>' />
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
                        <th><?php _e('Tipe', 'sejoli-jv'); ?></th>
                        <th><?php _e('Nilai', 'sejoli-jv'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan=3><?php _e('Total', 'sejoli-jv'); ?></th>
                        <th>Rp 0</th>
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
                    width:   '120px',
                    data:    'updated_at',
                    render : function(data, type, full) {
                        return ("0000-00-00 00:00:00" !== data) ? sejoli.helper.convertdate(data) : sejoli.helper.convertdate(full.created_at) 
                    }
                },{
                    targets: 1,
                    data:    'note',
                    render:  function(data, type, full) {
                        if( 'out' === full.type )
                        { return data + '&nbsp;&nbsp;&nbsp;<button type="button" class="ui mini button yellow sejoli-jv-delete-expenditure" data-id="' + full.expend_id + '">hapus</button>'; }

                        return data;
                    }
                },{
                    targets: 2,
                    width:   '80px',
                    data:    'type',
                    render : function(data, type, full) {
                        if( 'in' === data ) {
                            return '<span class="ui blue label">Debit</span>';
                        } else {
                            return '<span class="ui red label">Kredit</span>';
                        }
                    }
                },{
                    targets:   3,
                    width:     '120px',
                    data:      'value',
                    className: 'price'
                }
            ],
            'footerCallback' : function( row, data, start, end, display ) {
                var api  = this.api(),
                    data,
                    value = 0.0;

                $.each( data, function(i, e){
                    if( 'in' === e.type )
                    { value += parseFloat( e.raw_value ); }
                    else
                    { value -= parseFloat( e.raw_value ); }
                });

                $( api.column(3).footer() ).html(
                    sejoli_admin.text.currency + sejoli.helper.formatPrice( value )
                );
            }
        });

        sejoli_table.on('preXhr',function(){
            sejoli.helper.blockUI('.sejoli-table-holder');
        });

        sejoli_table.on('xhr',function(){

            sejoli.helper.unblockUI('.sejoli-table-holder');

            let date_range = $('input[name="date-range"]').val();

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

        $(document).on('click', '.sejoli-jv-delete-expenditure', function(){
            let expend_id = $(this).data('id'),
                confirmed = confirm(sejoli_admin.jv.delete_expenditure.confirm);

            if(confirmed) {
                $.ajax({
                    url:    sejoli_admin.jv.delete_expenditure.ajaxurl,
                    type:   'POST',
                    data:   {
                        expend_id:  expend_id,
                        nonce:      sejoli_admin.jv.delete_expenditure.nonce
                    },
                    beforeSend: function() {
                        sejoli.helper.blockUI('.sejoli-table-holder');
                    },
                    success:    function(response) {
                        if( false === response.valid ) {
                            alert(response.message);
                        }
                        sejoli_table.ajax.reload();
                    }
                });
            }

            return false;
        });

        // do export csv
        $(document).on('click', '.export-csv', function(){

            sejoli.helper.filterData();

            $.ajax({
                url:      sejoli_admin.jv.earning_export_prepare.link,
                type:     'POST',
                dataType: 'json',
                data:     {
                    nonce: sejoli_admin.jv.earning_export_prepare.nonce,
                    data:  sejoli.var.search
                },
                beforeSend : function() {
                    sejoli.helper.blockUI('#sejoli-jv');
                },
                success : function(response) {
                    sejoli.helper.unblockUI('#sejoli-jv');
                    window.location.href = response.url.replace(/&amp;/g, '&');
                }
            });
            return false;
        });

    });
})(jQuery);
</script>
