<script id='order-detail' type="text/x-jsrender">
<!--<button type='button' class='order-detail-trigger ui mini button' data-id='{{:id}}'>DETAIL</button>-->
<strong>
    {{:product}}
    {{if quantity}}
    <span class='ui label red'>x{{:quantity}}</span>
    {{/if}}
</strong>
<hr />
<div style='line-height:220%'>
    <span class="ui olive label">INV {{:id}}</span>
    <span class="ui teal label"><i class="calendar outline icon"></i>{{:date}}</span>

    {{if parent }}
    <span class="ui pink label" style='text-transform:uppercase;'><i class="redo icon"></i>INV {{:parent}}</span>
    {{/if}}

    {{if type }}
    <span class="ui brown label" style='text-transform:uppercase;'><i class="clock icon"></i>{{:type}}</span>
    {{/if}}

    {{if coupon }}
    <span class="ui purple label" style='text-transform:uppercase;'><i class="cut icon"></i>{{:coupon}}</span>
    {{/if}}
</div>
</script>

<script id='order-modal-content' type="text/x-jsrender">
<i class="close icon"></i>
<div class="header">
    <?php _e('Detil Order {{:id}}', 'sejoli'); ?>
</div>
<div class="content">
    <div class="ui divided selection list">
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Tanggal', 'sejoli'); ?></span>
            {{:date}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Nama Pembeli', 'sejoli'); ?></span>
            {{:buyer_name}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Kontak', 'sejoli'); ?></span>
            <span class='ui grey label'><i class="mobile icon"></i>{{:buyer_phone}}</span>
            <span class='ui grey label'><i class="phone icon"></i>{{:buyer_email}}</span>
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label" style='float:left;'><?php _e('Produk', 'sejoli'); ?></span>
            <span class="order-product-detail">
                {{:product_name}} X{{:quantity}} <br />
                {{:variants}}
            </span>
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Total', 'sejoli'); ?></span>
            {{:total}}
        </div>
        {{if courier}}
        <div class='item'>
            <span class="ui large main blue horizontal label"><?php _e('Kurir', 'sejoli'); ?></span>
            {{:courier}}
        </div>
        {{/if}}

        {{if address}}
        <div class='item'>
            <span class="ui large main blue horizontal label"><?php _e('Alamat Pengiriman', 'sejoli'); ?></span>
            {{:address}}
        </div>
        {{/if}}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Status', 'sejoli'); ?></span>
            <span class="ui large horizontal label" style="background-color:{{:color}};color:white;">{{:status}}</span>
        </div>

        {{if subscription }}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Tipe Langganan', 'sejoli'); ?></span>
            <span class="ui brown label" style='text-transform:uppercase;'><i class="clock icon"></i>{{:subscription}}</span>
        </div>
        {{/if}}

        {{if parent_order}}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Invoice Asal', 'sejoli'); ?></span>
            <span class="ui pink label" style='text-transform:uppercase;'><i class="redo icon"></i>INV {{:parent_order}}</span>
        </div>
        {{/if}}

        {{if affiliate_name}}
        <div class='item'>
            <span class="ui large main blue horizontal label"><?php _e('Affiliasi', 'sejoli'); ?></span>
            {{:affiliate_name}}
            <span class='ui grey label'><i class="envelope icon"></i>{{:affiliate_phone}}</span>
            <span class='ui grey label'><i class="mobile icon"></i>{{:affiliate_email}}</span>
        </div>
        {{/if}}
    </div>
</div>
</script>
<script id='order-variant-data' type="text/x-jsrender">
<span style='text-transform:capitalize;'>{{:type}}</span> : {{:label}} <br />
</script>

<script id='order-status' type="text/x-jsrender">
<div class="ui horizontal label boxed" style="background-color:{{:color}};">{{:label}}</div>
</script>
