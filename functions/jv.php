<?php

/**
 * Get product JV setup
 * @since   1.0.0
 * @param   integer         $product_id [description]
 * @return  array|false
 */
function sejoli_jv_get_product_setup( $product_id ) {

    $has_setup = false;
    $jv_users  = carbon_get_post_meta( $product_id, 'jv_users');

    if( is_array($jv_users) && 0 < count($jv_users) ) :
        return $jv_users;
    endif;

    return $has_setup;
}

/**
 * Add JV earning data
 * @since   1.0.0
 * @param   array $args
 * @return  array|false
 */
function sejoli_jv_add_earning_data( array $args ) {

    $args = wp_parse_args( $args, array(
                    'order_id'   => 0,
                    'product_id' => 0,
                    'user_id'    => 0,
                    'value'      => 0.0,
                    'status'     => 'pending',
                    'meta_data'  => array()
                ));

    $earning = SejoliJV\Model\JV::reset()
                    ->set_order_id( $args['order_id'] )
                    ->set_product_id( $args['product_id'] )
                    ->set_user_id( $args['user_id'] )
                    ->set_value( $args['value'] )
                    ->set_status( $args['status'] )
                    ->set_meta_data( $args['meta_data'] )
                    ->add_earning()
                    ->respond();

    return $earning;
}

/**
 * Add expend based on product
 * @since   1.0.0
 * @param   array   $args
 * @return  array|false
 */
function sejoli_jv_add_expend_data( array $args ) {

    $args = wp_parse_args( $args, array(
                    'expend_id'  => NULL,
                    'product_id' => 0,
                    'user_id'    => 0,
                    'value'      => 0.0,
                    'status'     => 'added',
                    'meta_data'  => array()
                ));

    $expend = SejoliJV\Model\JV::reset()
                    ->set_expend( $args['expend_id'] )
                    ->set_product_id( $args['product_id'] )
                    ->set_user_id( $args['user_id'] )
                    ->set_value( $args['value'] )
                    ->set_status( $args['status'] )
                    ->set_meta_data( $args['meta_data'] )
                    ->add_expend()
                    ->respond();

    return $expend;

}

/**
 * Get all JV earning data
 * @since   1.0.0
 * @param   array   $args
 * @return  array
 */
function sejoli_jv_get_earning_data( array $args, array $table) {

    $args = wp_parse_args($args,[
        'product_id'    => NULL,
        'date-range'    => date('Y-m-01') . ' - ' . date('Y-m-t'),
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => NULL
    ]);

    if(isset($args['date-range']) && !empty($args['date-range'])) :
        $table['filter']['date-range'] = $args['date-range'];
        unset($args['date-range']);
    endif;

    $query = SejoliJV\Model\JV::reset()
                    ->set_filter_from_array($args);

    if(isset($table['filter']['date-range']) && !empty($table['filter']['date-range'])) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        $query = $query->set_filter('created_at', $start.' 00:00:00', '>=')
                    ->set_filter('created_at', $end.' 23:59:59', '<=');
    endif;

    $response = $query->get_all_earning()
                    ->respond();

    return $response;

}
