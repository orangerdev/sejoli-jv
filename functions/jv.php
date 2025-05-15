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
 * Delete expenditure data
 * @since   1.0.0
 * @param   integer  $expend_id
 * @return  array
 */
function sejoli_jv_delete_expend_data( int $expend_id ) {

    $response = SejoliJV\Model\JV::reset()
                    ->set_expend( $expend_id )
                    ->delete_expend()
                    ->respond();

    return $response;

}

/**
 * Get all JV earning data
 * @since   1.0.0
 * @param   array   $args
 * @param   array   $table
 * @return  array
 */
function sejoli_jv_get_earning_data( array $args, $table = array()) {

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

    if(isset($table['filter']['date-range']) && !empty($table['filter']['date-range'])) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        // $query = $query->set_filter('updated_at', $start.' 00:00:00', '>=')
        //             ->set_filter('updated_at', $end.' 23:59:59', '<=');
    endif;

    $response = SejoliJV\Model\JV::reset()
                    ->set_filter_from_array($args)
                    // ->setProductId($args['product_id'])
                    ->get_all_earning($start, $end)
                    ->respond();

    return $response;

}

/**
 * Get single jv user data
 * @since   1.0.0
 * @param   integer     $user_id
 * @param   array       $args
 * @param   array       $table
 * @return  array
 */
function sejoli_jv_get_single_user_data( int $user_id, array $args, $table = array()) {

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

    if(isset($args['user_id']) && !empty($args['user_id'])) :

        $query = SejoliJV\Model\JV::reset()
                        ->set_filter_from_array($args)
                        // ->setProductId($args['product_id'])
                        ->set_user_id($args['user_id']);

    else:

        $query = SejoliJV\Model\JV::reset()
                        ->set_filter_from_array($args)
                        // ->setProductId($args['product_id'])
                        ->set_user_id($user_id);

    endif;

    if(isset($table['filter']['date-range']) && !empty($table['filter']['date-range'])) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        // $query = $query->set_filter('updated_at', $start.' 00:00:00', '>=')
        //             ->set_filter('updated_at', $end.' 23:59:59', '<=');
    endif;

    if(!is_null($table['order']) && is_array($table['order'])) :
        foreach($table['order'] as $order) :
            $query->set_data_order($order['column'], $order['sort']);
        endforeach;
    endif;

    $response = $query->get_single_user($start, $end)
                    ->respond();

    return $response;

}

/**
 * Update earning status
 * @since   1.0.0
 * @param   int     $order_id
 * @param   string  $status
 * @return  array
 */
function sejoli_jv_update_earning_status( int $order_id, string $status ) {

    $response = SejoliJV\Model\JV::reset()
                    ->set_order_id( $order_id )
                    ->set_status( $status )
                    ->update_status()
                    ->respond();

    return $response;
}

/**
 * Get all JV-related orders
 * @since   1.0.0
 * @param  array  $args
 * @param  array  $table
 * @return array
 * - valid      bool
 * - order      array
 * - messages   array
 */
function sejoli_jv_get_orders( array $args, $table = array() ) {

    $args = wp_parse_args($args,[
        'product_id'      => NULL,
        'user_id'         => NULL,
        'affiliate_id'    => NULL,
        'coupon_id'       => NULL,
        'payment_gateway' => NULL,
        'status'          => NULL,
        'type'            => NULL
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
                ->set_filter_from_array($args)
                ->set_data_start($table['start']);

    if(isset($table['filter']['date-range']) && !empty($table['filter']['date-range'])) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        $query = $query->set_filter('data_order.updated_at', $start.' 00:00:00', '>=')
                    ->set_filter('data_order.updated_at', $end.' 23:59:59', '<=');
    endif;

    if(0 < $table['length']) :
        $query->set_data_length($table['length']);
    endif;

    if(!is_null($table['order']) && is_array($table['order'])) :
        foreach($table['order'] as $order) :
            $query->set_data_order($order['column'], $order['sort']);
        endforeach;
    endif;

    $respond = $query->set_user_id( get_current_user_id() )
                    ->get_orders()
                    ->respond();

    foreach($respond['orders'] as $i => $order) :
        $respond['orders'][$i]->product   = sejolisa_get_product( intval($order->product_id) );
        $respond['orders'][$i]->meta_data = apply_filters('sejoli/order/table/meta-data', maybe_unserialize($order->meta_data), $respond['orders'][$i]);
    endforeach;

    return wp_parse_args($respond,[
        'valid'    => false,
        'orders'   => NULL,
        'messages' => []
    ]);

}

/**
 * Update single jv profit paid status
 * @since   1.1.3
 * @param   array  $args
 * @return  array
 */
function sejolisa_update_single_jv_profit_paid_status( array $args ) {

    $args = wp_parse_args($args, array(
        'user_id'       => 0,
        'paid_status'   => NULL,
        'current_time'  => current_time( 'mysql' ),
        'date_range'    => '',
    ));

    $query = SejoliJV\Model\JV::reset()
                ->set_user_id( $args['user_id'] )
                ->set_paid_status( $args['paid_status'] )
                ->set_paid_time( $args['current_time'] );

    if ( isset( $args['date_range'] ) && ! empty( $args['date_range'] ) ) :
        list($start, $end) = explode(' - ', $args['date_range']);
        $query->set_filter('updated_at', $start.' 00:00:00', '>=')
                ->set_filter('updated_at', $end.' 23:59:59', '<=');
    endif;

    $response = $query->update_single_jv_profit_paid_status()
                      ->respond();

    return $response;

}
