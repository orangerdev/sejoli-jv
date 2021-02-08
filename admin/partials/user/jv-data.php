<?php

$user_id = ( isset($_GET['user_id'] ) ) ? intval($_GET['user_id']) : get_current_user_id();
$jv_data = (array) get_user_meta( $user_id, 'sejoli_jv_data', true );
        __debug($jv_data);
?><p><?php
if( 0 < count($jv_data ) ) :

    ?><ul><?php
    foreach($jv_data as $data) :

        ?><li><?php
        printf(
            '%s - %s',
            $data['product_name'],
            ( 'percentage' === $data['type'] ) ? $data['value'].'%' : sejolisa_price_format($data['value'])
        );
        ?></li><?php

    endforeach;

    ?></ul><?php
else :
    _e('User tidak terdaftar ke program JV apapun', 'sejoli');
endif;
?></p><?php
