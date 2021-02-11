<?php

namespace Sejoli_JV;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_JV
 * @subpackage Sejoli_JV/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sejoli_JV
 * @subpackage Sejoli_JV/admin
 * @author     Sejoli <orangerdigiart@gmail.com>
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Get nett order ID. will be recalculated by commission and shipping cost
	 * @since 	1.0.0
	 * @param  	integer 	$order_id
	 * @return 	float
	 */
	protected function get_nett_order($order, $order_total) {

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

		return floatval($order_total);
	}

	/**
	 * Set JV earning when an order created
	 * Hooked via action sejoli/order/new, priority 2999
	 * @since 	1.0.0
	 * @param 	array $order_data
	 */
	public function set_jv_earning( array $order_data ) {

		$order_data  = sejolisa_get_order( array('ID' => $order_data['id'] ));
		$order       = $order_data['orders'];
		$jv_setup    = sejoli_jv_get_product_setup( $order['product_id'] );
		$order_total = floatval( $order['grand_total'] );

		if( false === $jv_setup || 0 >= $order_total) :
			return;
		endif;

		$nett_total  = $this->get_nett_order($order, $order_total);

		foreach( $jv_setup as $setup ) :

			$value = floatval( $setup['value_portion'] );

			if( 'percentage' === $setup['value_type'] ) :
				$value = floor( $nett_total * $value / 100 );
			endif;

			sejoli_jv_add_earning_data( array(
				'order_id'   => $order_id,
				'product_id' => $order['product_id'],
				'user_id'    => $setup['user'],
				'value'      => $value
			) );

			do_action(
				'sejoli/log/write',
				'jv-earning',
				sprintf(
					__('JV Earning from order %s for user ID %s, order total %s, nett total %s and earning for the user %s', 'sejoli-jv'),
					$order_id,
					$setup['user'],
					$order_total,
					$nett_total,
					$value
				)
			);

		endforeach;
	}

}
