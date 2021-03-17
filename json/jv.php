<?php
namespace Sejoli_JV\JSON;

Class JV extends \Sejoli_JV\JSON
{
    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Set JV products related
     * @since   1.0.0
     * @param   integer     $product_requested
     * @return  integer|array
     */
    protected function set_products($product_requested) {

        $jv_data     = (array) get_user_meta( get_current_user_id(), 'sejoli_jv_data', true);
        $jv_products = wp_list_pluck($jv_data, 'product_id');


        if( 0 === count($jv_products)) :
            return -999;
        else :
            if( in_array($product_request, $jv_products)) :
                return $product_request;
            else :
                return $jv_products;
            endif;
        endif;

        return -999;
    }

    /**
     * Set jv product detail for user data
     * Hooked via action sejoli_ajax_set-for-userdata, priority 1
     * @since 1.0.0
     */
    public function set_for_userdata() {

        $response = array();
        $get_data = wp_parse_args( $_GET, array(
                        'nonce'   => NULL,
                        'user_id' => NULL
                    ));

        if(
            is_user_logged_in() &&
            wp_verify_nonce($get_data['nonce'], 'sejoli-jv-set-for-userdata')
        ) :

            if(
                !empty($get_data['user_id']) &&
                current_user_can('manage_options')
            ) :
                $user_id = $get_data['user_id'];
            else :
                $user_id = get_current_user_id();
            endif;

            $jv_data = (array) get_user_meta($user_id, 'sejoli_jv_data', true);

            if( 0 < count($jv_data)) :

                $product_ids = wp_list_pluck($jv_data, 'product_id');

                $products = \SejoliSA\Model\Post::set_args(array(
                                'post_type' => SEJOLI_PRODUCT_CPT,
                                'post__in'  => $product_ids
                            ))
                            ->set_total(30)
                            ->get();

                foreach($products as $product):
                    $response[$product->ID] = sprintf(
                                                '%s - %s',
                                                $product->post_title,
                                                ('percentage' === $jv_data[$product->ID]['type']) ?
                                                    $jv_data[$product->ID]['value'].'%' :
                                                    sejolisa_price_format($jv_data[$product->ID]['value'])
                                            );
                endforeach;

            endif;

        endif;

        echo wp_send_json($response);
    }

    /**
     * set data for table
     * @since 1.0.0
     */
    public function set_for_table() {

        $table = $this->set_table_args($_POST);

		$data    = [];

        if(
            current_user_can('manage_sejoli_jv_data') &&
            isset($_POST['nonce']) &&
            wp_verify_nonce($_POST['nonce'], 'sejoli-jv-set-for-table')
        ) :

            $jv_products = array();

            if(!array_key_exists('product_id', $table['filter'])) :
                $table['filter']['product_id'] = 0;
            endif;

            $table['filter']['product_id'] = $this->set_products( $table['filter']['product_id']);

    		$respond = sejolisa_get_orders($table['filter'], $table);

    		if(false !== $respond['valid']) :
    			$data = $respond['orders'];
    		endif;

        endif;

		echo wp_send_json([
			'table'           => $table,
			'draw'            => $table['draw'],
			'data'            => $data,
			'recordsTotal'    => count($data),
			'recordsFiltered' => count($data)
		]);

    }

    /**
     * Prepare for export data
     * Hooked via action wp_ajax_sejoli-jv-order-export-prepare, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function prepare_export() {

        $response = [
            'url'   => admin_url('/'),
            'data'  => [],
        ];

        $post_data = wp_parse_args($_POST,[
            'data'    => array(),
            'nonce'   => NULL,
            'backend' => false
        ]);

        if(
            current_user_can('manage_sejoli_jv_data') &&
            wp_verify_nonce($post_data['nonce'], 'sejoli-jv-order-export-prepare')
        ) :

            $request          = array();

            foreach($post_data['data'] as $_data) :
                if(!empty($_data['val'])) :
                    $request[$_data['name']]    = $_data['val'];
                endif;
            endforeach;

            if(false !== $post_data['backend']) :
                $request['backend'] = true;
            endif;

            $response['data'] = $request;
            $response['url']  = wp_nonce_url(
                                    add_query_arg(
                                        $request,
                                        site_url('/sejoli-ajax/sejoli-jv-order-export')
                                    ),
                                    'sejoli-jv-order-export',
                                    'nonce'
                                );
        endif;

        echo wp_send_json($response);
        exit;

    }

    /**
     * Do JV order export
     * @since   1.0.0
     * @return  void
     */
    public function export_order() {

        $post_data = wp_parse_args($_GET,[
			'nonce'   => NULL,
			'backend' => false
		]);

		if(
            wp_verify_nonce($post_data['nonce'], 'sejoli-jv-order-export') &&
            current_user_can('manage_sejoli_jv_data')
        ) :

			$filename = 'export-jv-orders-' . strtoupper( sanitize_title( get_bloginfo('name') ) ) . '-' . date('Y-m-d-H-i-s', current_time('timestamp'));

            if(!isset($post_data['product_id'])) :
                $post_data['product_id'] = 0;
            endif;

            $post_data['product_id'] = $this->set_products( $post_data['product_id'] );

			unset($post_data['backend'], $post_data['nonce']);;

			$response   = sejolisa_get_orders($post_data);

			$csv_data = [];
			$csv_data[0]	= array(
				'INV', 'product', 'created_at', 'name', 'email', 'phone', 'price', 'status', 'affiliate', 'affiliate_id',
				'address', 'courier', 'variant',
			);

			$i = 1;
			foreach($response['orders'] as $order) :

				$address = $courier = $variant = '-';

				if( isset( $order->meta_data['shipping_data'] ) ) :

					$shipping_data = wp_parse_args( $order->meta_data['shipping_data'], array(
						'courier'     => NULL,
						'service'     => NULL,
						'district_id' => 0,
						'cost'        => 0,
						'receiver'    => NULL,
						'phone'       => NULL,
						'address'     => NULL
					));

					if( !empty($shipping_data['courier']) ) :

						$courier = $shipping_data['courier'];

						$courier = $shipping_data['service'] ? $courier . ' - ' . $shipping_data['service'] : $courier;
						$courier = $shipping_data['service'] ? $courier . ' ' . sejolisa_price_format( $shipping_data['cost'] ) : $courier;

					endif;

					if( isset( $shipping_data['address'] ) ) :

						$address = $shipping_data['receiver'] . ' ('.$shipping_data['phone'].')' . PHP_EOL . $shipping_data['address'];

						$subdistrict = sejolise_get_subdistrict_detail( $shipping_data['district_id']);

						if( is_array($subdistrict) && isset($subdistrict['subdistrict_name']) ) :

							$address = $address . PHP_EOL .
										sprintf( __('Kota %s', 'sejoli'), $subdistrict['city'] ) . PHP_EOL .
										sprintf( __('Kecamatan %s', 'sejoli'), $subdistrict['subdistrict_name'] ) . PHP_EOL .
										sprintf( __('Provinsi %s', 'sejoli'), $subdistrict['province'] );
						endif;

					endif;

				endif;

				if( isset($order->meta_data['variants']) && 0 < count($order->meta_data['variants']) ) :

					$variant_data = array();

					foreach((array) $order->meta_data['variants'] as $variant ) :
						$variant_data[] = strtoupper($variant['type']) . ' : ' . $variant['label'];
					endforeach;

					$variant = implode(PHP_EOL, $variant_data);

				endif;

				$csv_data[$i] = array(
					$order->ID,
					$order->product->post_title,
					$order->created_at,
					$order->user_name,
					$order->user_email,
					get_user_meta($order->user_id, '_phone', true),
					$order->grand_total,
					$order->status,
					$order->affiliate_id,
					$order->affiliate_name,
					$address,
					$courier,
					$variant
				);

				$i++;

			endforeach;

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

			$fp = fopen('php://output', 'wb');
			foreach ($csv_data as $line) :
			    fputcsv($fp, $line, ',');
			endforeach;
			fclose($fp);

		endif;
		exit;

    }

    /**
     * Set for earning table
     * Hooked via action wp_ajax_sejoli-jv-earning-table, priority 1
     * @since 1.0.0
     */
    public function set_for_earning_table() {

        $table = $this->set_table_args($_POST);

		$data    = [];

        if(
            current_user_can('manage_sejoli_jv_data') &&
            isset($_POST['nonce']) &&
            wp_verify_nonce($_POST['nonce'], 'sejoli-render-jv-earning-table')
        ) :

            $jv_products = array();

    		$respond = sejoli_jv_get_earning_data($table['filter'], $table);

    		if(false !== $respond['valid']) :

                foreach( $respond['jv'] as $i => $jv) :

                    $data[$i] = (array) $jv;
                    $data[$i]['earning_value']     = sejolisa_price_format( $data[$i]['earning_value'] );
                    $data[$i]['expenditure_value'] = sejolisa_price_format( $data[$i]['expenditure_value'] );
                    $data[$i]['total_value']       = sejolisa_price_format( $data[$i]['total_value'] );

                endforeach;

    		endif;

        endif;

		echo wp_send_json([
			'table'           => $table,
			'draw'            => $table['draw'],
			'data'            => $data,
			'recordsTotal'    => count($data),
			'recordsFiltered' => count($data),
		]);

    }
}
