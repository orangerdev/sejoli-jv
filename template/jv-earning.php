<?php
    sejoli_header();
    $products = get_user_meta( get_current_user_id(), 'sejoli_jv_data', true);
?>
<h2 class="ui header"><?php _e('Data Pendapatan Anda', 'sejoli'); ?></h2>

<button class="ui primary button show-filter-form"><i class="filter icon"></i> <?php _e( 'Filter Data', 'sejoli' ); ?></button>
<button class="ui button export-csv"><i class="file alternate icon"></i> <?php _e( 'Export to CSV', 'sejoli' ); ?></button>
<table id="jv-orders" class="ui striped single line table" style="width:100%;word-break: break-word;white-space: normal;">
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
            <th>0</th>
        </tr>
    </tfoot>
</table>
<?php
    include( plugin_dir_path( __FILE__ ) . '/order/single-filter.php');
?>
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
                url :  sejoli_jv.export_earning_prepare.link,
                type : 'POST',
                dataType: 'json',
                data : {
                    action : 'sejoli-jv-earning-export-prepare',
                    nonce : sejoli_jv.export_earning_prepare.nonce,
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
				'url'   : sejoli_jv.earning.link,
				'data'  : function(data) {
					data.filter   = sejoli.filter('#filter-form');
	                data.action   = 'sejoli-jv-user-earning-table'
					data.nonce 	  = sejoli_jv.earning.nonce;
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
                    targets: [1,2,3],
                    orderable: false
                },{
                    targets: 0,
                    width:   '120px',
                    data:    'created_at'
                },{
                    targets: 1,
                    data:    'note'
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
                    sejoli_member_area.text.currency + sejoli.formatPrice( value )
                );
            }
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

        // do export csv
        $(document).on('click', '.export-csv', function(){

            $.ajax({
                url:      sejoli.jv.earning_export_prepare.link,
                type:     'POST',
                dataType: 'json',
                data:     {
                    nonce: sejoli.jv.earning_export_prepare.nonce,
                    data:  sejoli.filter('#filter-form')
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
    });
})(jQuery)
</script>

<?php sejoli_footer();
