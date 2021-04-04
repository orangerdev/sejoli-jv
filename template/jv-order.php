<?php

    sejoli_header();

    $products = get_user_meta( get_current_user_id(), 'sejoli_jv_data', true);
?>
<h2 class="ui header"><?php _e('Data Order', 'sejoli'); ?></h2>

<button class="ui primary button show-filter-form"><i class="filter icon"></i> <?php _e( 'Filter Data', 'sejoli' ); ?></button>
<button class="ui button export-csv"><i class="file alternate icon"></i> <?php _e( 'Export to CSV', 'sejoli' ); ?></button>
<table id="jv-orders" class="ui striped single line table" style="width:100%;word-break: break-word;white-space: normal;">
    <thead>
        <tr>
            <th><?php _e('Detil',       'sejoli'); ?></th>
            <th><?php _e('Pembeli',     'sejoli'); ?></th>
            <th><?php _e('Total',       'sejoli'); ?></th>
            <th><?php _e('Pendapatan',  'sejoli'); ?></th>
            <th><?php _e('Status',      'sejoli'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="5">Tidak ada data yang bisa ditampilkan</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th><?php _e('Detil',       'sejoli'); ?></th>
            <th><?php _e('Pembeli',     'sejoli'); ?></th>
            <th><?php _e('Total',       'sejoli'); ?></th>
            <th><?php _e('Pendapatan',  'sejoli'); ?></th>
            <th><?php _e('Status',      'sejoli'); ?></th>
        </tr>
    </tfoot>
</table>

<div class="order-modal-holder ui modal"></div>

<?php
include( plugin_dir_path( __FILE__ ) . '/order/filter.php');
include( plugin_dir_path( __FILE__ ) . '/order/detail.php');
?>
<script id="tmpl-nodata" type="text/x-js-render">
    <p>Tidak ada data ditemukan</p>
</script>
<script type='text/javascript'>
let sejoli_table;

(function($){

    'use strict';

    $(document).ready(function(){
        // render date range
        sejoli.daterangepicker("#date-range");

        // do export csv
        $(document).on('click', '.export-csv', function(){
            $.ajax({
                url :  sejoli_jv.export_prepare.link,
                type : 'POST',
                dataType: 'json',
                data : {
                    action : 'sejoli-jv-order-export-prepare',
                    nonce : sejoli_jv.export_prepare.nonce,
                    data : sejoli.filter('#filter-form'),
                },
                beforeSend : function() {
                    sejoli.block('#jv-orders');
                },
                success : function(response) {
                    sejoli.unblock('#jv-orders');
                    window.location.href = response.url.replace(/&amp;/g, '&');
                }
            });
            return false;
        });

        sejoli_table = $('#jv-orders').DataTable({
			"language"	: dataTableTranslation,
			'ajax'		: {
				'method': 'POST',
				'url'   : sejoli_jv.order.link,
				'data'  : function(data) {
					data.filter   = sejoli.filter('#filter-form');
	                data.action   = 'sejoli-jv-order-table'
					data.nonce 	  = sejoli_jv.order.nonce;
				}
			},
			// "bLengthChange": false,
			"bFilter": false,
			"serverSide": true,
			pageLength : 50,
			lengthMenu : [
				[10, 50, 100, 200, -1],
				[10, 50, 100, 200, dataTableTranslation.all],
			],
			order: [
				[ 0, "desc" ]
			],
			columnDefs: [
				{
					targets: [1, 2, 3, 4],
					orderable: false
				},
				{
					targets: 0,
					data : 'ID',
					render : function( data, type, full) {
						let tmpl = $.templates('#order-detail'),
							subsctype = null,
							quantity = null;

						if(1 < parseInt(full.quantity)) {
							quantity = full.quantity;
						}

						return tmpl.render({
							id : full.ID,
							product : full.product_name,
							coupon : full.coupon_code,
							parent : full.order_parent_id,
							date : sejoli.convertdate(full.created_at),
							type : sejoli_member_area.subscription.type[full.type],
							quantity : quantity,
						})
					}
				},{
					targets: 1,
					width: '15%',
					data : 'user_name'
				},{
					targets: 2,
					width: '15%',
					data : 'grand_total',
					className : 'price',
					render : function(data, type, full) {
						return sejoli_member_area.text.currency + sejoli.formatPrice(data)
					}
				},{
					targets: 3,
					width: '15%',
					data : 'earning',
					className : 'price',
					render : function(data, type, full) {
						return sejoli_member_area.text.currency + sejoli.formatPrice(data)
					}
				},{
					targets:4,
					data : 'status',
					width: '100px',
					render : function( data, type, full ) {
						let tmpl = $.templates('#order-status');
						return tmpl.render({
							label : sejoli_member_area.order.status[full.status],
							color : sejoli_member_area.color[full.status]
						});
					}
				}
			]
		});

		sejoli_table.on( 'preXhr.dt', function( e, settings, data ){
			sejoli.block('#jv-orders');
		});

		sejoli_table.on( 'xhr.dt', function ( e, settings, json, xhr ) {
			sejoli.unblock('#jv-orders');
		});

        // show filter form
        $(document).on('click','.show-filter-form', function(){
            $('#filter-form-wrap').modal('show');
        });

        // trigger filter form
        $(document).on('click','.filter-form',function(e){
            e.preventDefault();
            $('#filter-form-wrap').modal('hide');
            sejoli_table.ajax.reload();
        });
    });
})(jQuery)
</script>

<?php sejoli_footer();
