<div id="order-detail" class="ui small modal">
    <i class="close icon"></i>
    <div class="header">
        <?php _e( 'Order Detail', 'sejoli' ); ?>
    </div>
    <div class="content">
    </div>
</div>
<script id="orderDetailTmpl" type="text/x-jsrender">
    <table class="ui striped very basic table">
        <tbody>
            <tr>
                <td width="200px"><b><?php _e( 'Tanggal', 'sejoli' ); ?></b></td>
                <td>{{:date}}</td>
            </tr>
            <tr>
                <td><b><?php _e( 'Nomor Invoice', 'sejoli' ); ?></b></td>
                <td>{{:invoice_number}}</td>
            </tr>
            <tr>
                <td><b><?php _e( 'Nama Customer', 'sejoli' ); ?></b></td>
                <td>{{:customer_name}}</td>
            </tr>
            <tr>
                <td><b><?php _e( 'Total Komisi', 'sejoli' ); ?></b></td>
                <td>{{:commission_total}}</td>
            </tr>
            <tr>
                <td><b><?php _e( 'Referal', 'sejoli' ); ?></b></td>
                <td>{{:referal}}</td>
            </tr>
            <tr>
                <td><b><?php _e( 'Status', 'sejoli' ); ?></b></td>
                <td>{{:status}}</td>
            </tr>
        </tbody>
    </table>
</script>
