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
        </form>
    </div>
    <div class="actions">
        <button class="ui primary button filter-form"><?php _e( 'Filter', 'sejoli' ); ?></button>
    </div>
</div>
