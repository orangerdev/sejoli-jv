<div id="filter-form-wrap" class="ui small modal">
    <i class="close icon"></i>
    <div class="header">
        <?php _e( 'Filter Data', 'sejoli' ); ?>
    </div>
    <div class="content">
        <form id="filter-form" class="ui form">
            <div class="field">
                <label><?php _e( 'Tanggal', 'sejoli' ); ?></label>
                <input type="text" name="date-range" id="date-range"/>
            </div>
            <div class="field">
                <label><?php _e( 'Produk', 'sejoli' ); ?></label>
                <select name="product_id" id="product_id" class='select2-filled'>
                    <option value=""><?php _e( '--Pilih Produk--' ); ?></option>
                    <?php foreach($products as $product_id => $product) : ?>
                    <option value="<?php echo $product_id; ?>"><?php echo $product['product_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label><?php _e( 'Status Order', 'sejoli' ); ?></label>
                <select name="status" id="status" class="select2-filled">
                    <option value=""><?php _e( '--Pilih Status Order--' ); ?></option>
                    <?php
                    $order_status = apply_filters('sejoli/order/status', []);
                    foreach($order_status as $key => $label) :
                    ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php
                    endforeach;
                    ?>
                </select>
            </div>
            <div class="field">
                <label><?php _e( 'Tipe Order', 'sejoli' ); ?></label>
                <select name="type" id="type" class="select2-filled">
                    <option value=""><?php _e( '--Pilih Tipe Order--' ); ?></option>
                    <option value="regular"><?php _e('Pembelian sekali waktu', 'sejoli'); ?></option>
                    <option value="subscription-tryout"><?php _e("Berlangganan - Tryout", 'sejoli'); ?></option>
                    <option value="subscription-signup"><?php _e("Berlangganan - Awal", 'sejoli'); ?></option>
                    <option value="subscription-regular"><?php _e("Berlangganan - Regular", 'sejoli'); ?></option>
                </select>
            </div>
        </form>
    </div>
    <div class="actions">
        <button class="ui primary button filter-form"><?php _e( 'Filter', 'sejoli' ); ?></button>
    </div>
</div>
