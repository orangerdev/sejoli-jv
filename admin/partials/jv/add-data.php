<div id='sejoli-jv-add-data-modal' class="ui medium modal">
    <i class="close icon"></i>
    <div class="header">
        <?php _e('Input Data', 'sejoli-jv'); ?>
    </div>
    <form id='sejoli-jv-add-data-form' class="ui form">

        <div class="content">

            <div class="ui yellow message">
                <p>
                    <?php _e('Form ini berfungsi untuk menambah data PENGELUARAN, seperti gaji, biaya iklan, biaya operasional dan lain-lain. <br />Secara otomatis, sistem akan membagi nilai pengeluaran berdasarkan proporsi JV', 'sejoli-jv'); ?>
                </p>
            </div>

            <div class="field">

                <label for="amount"><?php _e('Nilai', 'sejoli-jv'); ?></label>

                <div class="ui right labeled input">
                    <label for="amount" class="ui label">Rp.</label>
                    <input type="text" placeholder="<?php _e('Nilai pengeluaran', 'sejoli-jv'); ?>" id="amount" name='amount' required>
                </div>

            </div>

            <div class="field">
                <label for="note"><?php _e('Catatan', 'sejoli-jv'); ?></label>
                <textarea rows="2" id='note' name='note' required></textarea>
            </div>

            <div class="field">
                <label><?php _e('Product', 'sejoli-jv'); ?></label>
                <select class="autosuggest filter" id='product_id' name="product_id" required></select>
            </div>

        </div>

        <div id='sejoli-jv-data-response' class="ui message" style="display:none;">
            <p></p>
        </div>

        <div class="actions">

            <?php wp_nonce_field('sejoli-jv-add-data', 'nonce'); ?>

            <button type="button" class="ui black deny button">
                <?php _e('Batalkan', 'sejoli-jv'); ?>
            </button>

            <button type="submit" class="ui green right labeled icon button">
                <?php _e('Tambah data', 'sejoli-jv'); ?>
                <i class="checkmark icon"></i>
            </button>

        </div>

    </form>
</div>

<script type="text/javascript">
(function($){

    'use strict';

    $(document.body).on('click', '.add-expenditure', function(){
        $('#sejoli-jv-add-data-modal').modal('show');
        return false;
    });

    $(document.body).on('submit', '#sejoli-jv-add-data-form', function(){

        let data = new FormData($(this)[0]);

        $.ajax({
            url:         sejoli_admin.jv.add_expenditure.ajaxurl,
            processData: false,
            contentType: false,
            type:        'POST',
            data:        data,
            dataType:    'json',
            beforeSend:  function() {
                sejoli.helper.blockUI('#sejoli-jv-add-data-modal');
                $('#sejoli-jv-data-response').removeClass('yellow green').hide();
            },
            success:    function(response) {

                sejoli.helper.unblockUI('#sejoli-jv-add-data-modal');

                if(false === response.valid) {
                    $('#sejoli-jv-data-response').addClass('yellow').show();
                } else {
                    $('#sejoli-jv-data-response').addClass('green').show();
                    sejoli_table.ajax.reload();
                }

                $('#sejoli-jv-data-response').find('p').html(response.message);


            }
        })

        return false;
    });

    $('#amount').ready(function(){
        $('#amount').mask('0.000.000.000.000', {reverse: true});
    })

})(jQuery);
</script>
