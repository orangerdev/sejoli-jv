<?php

namespace Sejoli_JV\CLI;

class JV {

    /**
     * Add earning
     *
     * <order_id>
     * : The order id
     *
     *  wp sejolisa wallet add_earning 123
     *
     * @when after_wp_load
     */
    public function add_earning(array $args) {

        list($order_id) = $args;

        $order_data = sejolisa_get_order(array('ID' => $order_id ));

        if( false !== $order_data['valid'] ) :

            $order       = $order_data['orders'];
            $jv_setup    = sejoli_jv_get_product_setup( $order['product_id'] );
            $order_total = floatval( $order['grand_total'] );

            if( false === $jv_setup || 0 >= $order_total) :
                return;
            endif;

            // if has shipment data
            if(
                isset($order['meta_data']) &&
                isset($order['meta_data']['need_shipment']) &&
                true === boolval( $order['meta_data']['need_shipment'] ) &&
                isset($order['meta_data']['need_shipment']['cost'])
            ) :

                $order_total -= floatval($order['meta_data']['need_shipment']['cost']);

            endif;

            // check by total commission
            $total_commission = sejolisa_get_total_commission_by_order( $order_id );

            $order_total -= $total_commission;

            foreach( $jv_setup as $setup ) :

                $value = floatval( $setup['value_portion'] );

                if( 'percentage' === $setup['value_type'] ) :
                    $value = floor( $order_total * $value / 100 );
                endif;

                __print_debug(
                    $setup,
                    $order_total,
                    sejoli_jv_add_earning_data( array(
                    'order_id'   => $order_id,
                    'product_id' => $order['product_id'],
                    'user_id'    => $setup['user'],
                    'value'      => $value,
                ) ) );

            endforeach;

        endif;
    }

    /**
     * Add expenditure
     *
     * <product_id>
     * : The product id
     *
     * <value>
     * : Expenditur value
     *
     *  wp sejolisa wallet add_expend 6 3000000
     *
     * @when after_wp_load
     */
    public function add_expend(array $args) {

        list($product_id, $expend) = $args;

        $jv_setup    = sejoli_jv_get_product_setup( $product_id );

        if( false === $jv_setup || 0 >= $expend) :
            return;
        endif;

        $timestamp = current_time('timestamp');

        foreach( $jv_setup as $setup ) :

            $value = floatval( $setup['value_portion'] );

            if( 'percentage' === $setup['value_type'] ) :
                $value = floor( $expend * $value / 100 );
            endif;

            __print_debug(
                $setup,
                $expend,
                sejoli_jv_add_expend_data( array(
                    'expend_id'  => $timestamp,
                    'product_id' => $product_id,
                    'user_id'    => $setup['user'],
                    'value'      => $value,
                    'meta_data'  => array(
                        'note'  => sprintf( __('Gaji farras sebesar %s', 'sejoli-jv'), sejolisa_price_format($expend))
                    )
                )
            ) );

        endforeach;
    }

}
