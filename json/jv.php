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
            wp_verify_nonce($get_data['nonce'], 'sejoli-set-for-userdata')
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
}
