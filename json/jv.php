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
                    $response[$product->ID] = $product->post_title;
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
            $jv_data     = (array) get_user_meta( get_current_user_id(), 'sejoli_jv_data', true);
            $jv_products = wp_list_pluck($jv_data, 'product_id');


            if( !array_key_exists('product_id', $table['filter']) || empty($table['filter']['product_id'])) :

                if( 0 === count($jv_products)) :
                    $table['filter']['product_id'] = -999;
                else :
                    $table['filter']['product_id'] = $jv_products;
                endif;

            else :

                if( !in_array($table['filter']['product_id'], $jv_products) ) :
                    $table['filter']['product_id'] = -999;
                endif;

            endif;

    		$respond = sejolisa_get_orders($table['filter'], $table);

    		if(false !== $respond['valid']) :
    			$data = $respond['orders'];
    		endif;

        endif;

		echo wp_send_json([
			'table'           => $table,
			'draw'            => $table['draw'],
			'data'            => $data,
			'recordsTotal'    => $respond['recordsTotal'],
			'recordsFiltered' => $respond['recordsTotal'],
		]);

    }
}
