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
                <button type="button" name="export-csv"      class='export-csv button'><?php _e('Export CSV', 'sejoli-jv'); ?></button>
                <button type="button" name="add-expenditure" class='add-expenditure button'><?php _e('Input Data', 'sejoli-jv'); ?></button>
                <button type="button" name="toggle-search"   class='button toggle-search'><?php _e('Filter Data', 'sejoli-jv'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <select class="autosuggest filter" name="product_id"></select>
                    <input type="text" class='filter' name="date-range" value="<?php echo $date; ?>" placeholder="<?php _e('Pencarian berdasarkan tanggal', 'sejoli-jv'); ?>">
                    <?php wp_nonce_field('search-jv', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli-jv'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli-jv'); ?></button> -->
                </div>
            </div>

            <div style='clear:both;display:block'></div>
        </div>

        <div class="sejoli-table-holder">
            <table id="sejoli-jv" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><?php _e('User', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pendapatan', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pendapatan belum dibayar', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pendapatan sudah dibayar', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pembayaran pendapatan', 'sejoli-jv'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('User', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pendapatan', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pendapatan belum dibayar', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pendapatan sudah dibayar', 'sejoli-jv'); ?></th>
                        <th><?php _e('Pembayaran pendapatan', 'sejoli-jv'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once( plugin_dir_path( __FILE__) . '/add-data.php' ); ?>

<script id='pay-jv-profit' type="text/x-jsrender">
<form method='POST' action='' enctype='multipart/form-data' class='pay-jv-profit-form'>
    
    <?php echo wp_nonce_field('sejoli-pay-single-jv-profit', 'sejoli-nonce'); ?>

    <input type='hidden' name='total_commission' value='{{:unpaid_commission_html}}' />
    <input type='hidden' name='user_id' value='{{:user_id}}' />

    <input type='hidden' name='display_name' value='{{:display_name}}' />
    <input type='hidden' name='unpaid_commission' value='{{:unpaid_commission}}' />

    <div class="pay-jv-profit">
        <?php _e('Rekening :', 'sejoli-jv'); ?> {{:informasi_rekening}}<br>
        <?php _e('Bukti Transfer :', 'sejoli-jv'); ?> <input type="file" name="proof" class="bukti_transfer"><br>
    </div>
</form>
</script>

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
                url: sejoli_admin.jv.table.ajaxurl,
                data: function(data) {
                    data.filter = sejoli.var.search;
                    data.action = 'sejoli-jv-earning-table';
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
                    targets:   [1,2],
                    orderable: false
                },{
                    targets: 0,
                    width:   '30%',
                    data:    'display_name',
                    render: function(data, type, full) {
                        return "<a class='order-detail-trigger ui mini button' target='new' href='" + sejoli_admin.jv.table.single_link + "&user_id=" + full.user_id + "'>DETAIL</a><strong> "+ full.display_name+ "</strong>";
                    }
                },{
                    targets:   1,
                    width:     '18%',
                    data:      'total_value',
                    className: 'price'
                },{
                    targets:   2,
                    width:     '18%',
                    data:      'unpaid_commission',
                    className: 'price',
                    render: function(data, type, full) {
                        return sejoli_admin.text.currency + sejoli.helper.formatPrice(full.unpaid_commission);
                    }
                },{
                    targets:   3,
                    width:     '18%',
                    data:      'paid_commission',
                    className: 'price'
                },{
                    targets: 4,
                    width: '16%',
                    data : 'informasi_rekening',
                    className : 'informasi_rekening',
                    render : function(data ,type, full) {

                        var dateNow = new Date,
                            dateNowFormatted = [
                                dateNow.getFullYear().toString().padStart(4, '0'),
                                (dateNow.getMonth()+1).toString().padStart(2, '0'),
                                dateNow.getDate().toString().padStart(2, '0'),
                            ].join('-')+' '+[
                                dateNow.getHours().toString().padStart(2, '0'),
                                dateNow.getMinutes().toString().padStart(2, '0'),
                                dateNow.getSeconds().toString().padStart(2, '0')
                            ].join(':');
                        
                        let tmpl = $.templates('#pay-jv-profit');
                        return tmpl.render({
                            informasi_rekening: data,
                            display_name: full.display_name,
                            unpaid_commission: full.unpaid_commission,
                            unpaid_commission_html: sejoli_admin.text.currency + sejoli.helper.formatPrice(full.unpaid_commission),
                            user_id: full.user_id,
                        });

                    }
                }
            ]
        });

        $(document).on('change','.bukti_transfer',function(e){

            e.preventDefault();

            if ( $(this).get(0).files.length !== 0 ) {

                // trigger submit .pay-jv-profit-form
                $(this).parent().parent().trigger('submit');

            }

        });

        $(document).on('submit','.pay-jv-profit-form',function(e){

            e.preventDefault();

            var formData = new FormData(this);

            var display_name = formData.get('display_name');
            var unpaid_commission = formData.get('unpaid_commission');
            var total_commission = formData.get('total_commission');
            var date_range = $("input[name='date-range']").val();
            formData.append('date_range',date_range);

            if ( unpaid_commission > 0 ) {
                var message = 'Apakah anda yakin akan membayar pendapatan '+display_name+' sebesar '+total_commission+' ?';
                if ( confirm( message ) ) {
                    // ajax here

                    $.ajax({
                        url:     sejoli_admin.jv.pay.ajaxurl,
                        type:    'POST',
                        enctype: 'multipart/form-data',
                        processData: false,
                        contentType: false,
                        cache: false,
                        data: formData,
                        dataType: 'json',
                        beforeSend : function() {
                            sejoli.helper.blockUI('.sejoli-table-holder');
                        },
                        success : function(response) {

                            sejoli.helper.unblockUI('.sejoli-table-holder');

                            let tmpl = $.templates('#confirmation-message-content'),
                                html = tmpl.render(response.messages);

                            $('.commission-paid-modal-holder .message').html(html);
                            $('.commission-paid-modal-holder').modal('show');
                            sejoli_table.ajax.reload();

                            setInterval(function(){
                                $('.commission-paid-modal-holder').modal('hide');
                            },5000);

                        }
                    });
                } else {
                    alert('Pembayaran pendapatan dibatalkan');
                }
            } else {
                alert('Tidak dapat memproses pembayaran pendapatan, pendapatan belum dibayar '+total_commission);
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

        // do export csv
        $(document).on('click', '.export-csv', function(){

            sejoli.helper.filterData();

            $.ajax({
                url:      sejoli_admin.jv.multi_earning_export_prepare.link,
                type:     'POST',
                dataType: 'json',
                data:     {
                    nonce: sejoli_admin.jv.multi_earning_export_prepare.nonce,
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
