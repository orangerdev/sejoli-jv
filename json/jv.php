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

            if( in_array($product_requested, $jv_products)) :
                return $product_requested;
            else :
                return $jv_products;
            endif;

        endif;

        return -999;
    }

    /**
     * Set JV note data
     * @since   1.0.0
     * @param   object   $jv [description]
     */
    protected function set_note( object $jv ) {

        $note = '';

        if( 'in' === $jv->type ) :

            $product_name = '';
            $product      = sejolisa_get_product($jv->product_id);

            if( is_a($product, 'WP_Post') ) :
                $product_name = $product->post_title;
            else :
                $product_name = 'ID '.$jv->product_id.' '. __('(telah dihapus)', 'sejoli');
            endif;

            $note = sprintf( __('Penjualan produk %s dari INV %s', 'sejoli-jv'), $product_name, $jv->order_id);

        else :

            $meta_data = maybe_unserialize( $jv->meta_data );
            $note      = $meta_data['note'];

        endif;

        return $note;
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

            $respond = sejoli_jv_get_orders($table['filter'], $table);

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
     * Prepare for earning export data
     * Hooked via action wp_ajax_sejoli-jv-earning-export-prepare, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function prepare_earning_export() {

        $response = [
            'url'   => admin_url('/'),
            'data'  => [],
        ];

        $post_data = wp_parse_args($_POST,[
            'data'    => array(),
            'nonce'   => NULL
        ]);

        if(
            wp_verify_nonce($post_data['nonce'], 'sejoli-jv-earning-export-prepare') &&
            (
                current_user_can('manage_sejoli_jv_data') ||
                current_user_can('manage_options')
            )
        ) :

            $request          = array();

            foreach($post_data['data'] as $_data) :
                if(!empty($_data['val'])) :
                    $request[$_data['name']]    = $_data['val'];
                endif;
            endforeach;

            $response['data'] = $request;
            $response['url']  = wp_nonce_url(
                                    add_query_arg(
                                        $request,
                                        site_url('/sejoli-ajax/sejoli-jv-earning-export')
                                    ),
                                    'sejoli-jv-earning-export',
                                    'nonce'
                                );

        endif;

        echo wp_send_json($response);
        exit;

    }

    /**
     * Prepare for multi earning export data
     * Hooked via action wp_ajax_sejoli-jv-earning-export-prepare, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function prepare_multi_earning_export() {

        $response = [
            'url'   => admin_url('/'),
            'data'  => [],
        ];

        $post_data = wp_parse_args($_POST,[
            'data'    => array(),
            'nonce'   => NULL
        ]);

        if(
            wp_verify_nonce($post_data['nonce'], 'sejoli-jv-multi-earning-export-prepare') &&
            current_user_can('manage_options')
        ) :

            $request          = array();

            foreach($post_data['data'] as $_data) :
                if(!empty($_data['val'])) :
                    $request[$_data['name']]    = $_data['val'];
                endif;
            endforeach;

            $response['data'] = $request;
            $response['url']  = wp_nonce_url(
                                    add_query_arg(
                                        $request,
                                        site_url('/sejoli-ajax/sejoli-jv-multi-earning-export')
                                    ),
                                    'sejoli-jv-multi-earning-export',
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
            (
                current_user_can('manage_sejoli_jv_data') ||
                current_user_can('manage_options')
            )
        ) :

            $filename = 'export-jv-orders-' . strtoupper( sanitize_title( get_bloginfo('name') ) ) . '-' . date('Y-m-d-H-i-s', current_time('timestamp'));

            if(!isset($post_data['product_id'])) :
                $post_data['product_id'] = 0;
            endif;

            $post_data['product_id'] = $this->set_products( $post_data['product_id'] );

            unset($post_data['backend'], $post_data['nonce']);;

            $response   = sejoli_jv_get_orders($post_data);

            $csv_data = [];
            $csv_data[0]    = array(
                'INV', 'product', 'created_at', 'name', 'email', 'phone', 'price', 'earning', 'status', 'affiliate', 'affiliate_id',
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
                    $order->earning,
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
     * Export single earning order
     * Hooked via action sejoli_ajax_sejoli-jv-earning-export, priority 1
     * @since   1.0.0
     * @return  void
     */
    public function export_single_earning() {

        $post_data = wp_parse_args($_GET,[
            'nonce'      => NULL,
            'backend'    => false,
            'date-range' => NULL,
            'user_id'    => 0
        ]);

        if(
            wp_verify_nonce($post_data['nonce'], 'sejoli-jv-earning-export') &&
            (
                current_user_can('manage_sejoli_jv_data') ||
                current_user_can('manage_options')
            )
        ) :

            $user_id = ( empty($user_id) || !current_user_can('manage_options') ) ? get_current_user_id() : $post_data['user_id'];

            $filename = sprintf(
                            'export-jv-earning-%s-%s-user-%s',
                            strtoupper( sanitize_title( get_bloginfo('name') ) ),
                            date('Y-m-d-H-i-s', current_time('timestamp') ),
                            $user_id
                        );

            // if(!isset($post_data['product_id'])) :
            //     $post_data['product_id'] = '';
            // endif;

            // $post_data['product_id'] = $this->set_products( $post_data['product_id'] );

            unset($post_data['backend'], $post_data['nonce']);
            
            if(intval($post_data['user_id']) <= 0) :

                unset($post_data['backend'], $post_data['nonce'], $post_data['user_id']);

            endif;

            $response  = sejoli_jv_get_single_user_data( $user_id, $post_data);

            $csv_data = [];
            $csv_data[0]    = array(
                'date',
                'note',
                'value',
                'raw_value',
                'typess'
            );

            $i = 1;

            foreach($response['jv'] as $jv) :

                $date = ('0000-00-00 00:00:00' === $jv->updated_at ) ? $jv->updated_at : $jv->created_at;

                $csv_data[$i] = array(
                    $date,
                    $this->set_note( $jv ),
                    sejolisa_price_format( $jv->value ),
                    $jv->value,
                    $jv->type
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
     * Export multi earning order
     * Hooked via action wp_ajax_sejoli-jv-multi-earning-table, priority 1
     * @since   1.0.0
     * @return  void
     */
    public function export_multi_earning() {

        $post_data = wp_parse_args($_GET,[
            'nonce'      => NULL,
            'date-range' => NULL,
        ]);

        if(
            wp_verify_nonce($post_data['nonce'], 'sejoli-jv-multi-earning-export') &&
            current_user_can('manage_options')
        ) :

            $filename = sprintf(
                            'export-jv-multi-earning-%s-%s',
                            strtoupper( sanitize_title( get_bloginfo('name') ) ),
                            date('Y-m-d-H-i-s', current_time('timestamp') )
                        );

            $jv_products = array();

            unset( $post_data['nonce'] );

            $respond = sejoli_jv_get_earning_data($post_data);

            $csv_data = [];
            $csv_data[0]    = array(
                'user',
                'sale',
                'expenditure',
                'earning'
            );

            if(false !== $respond['valid']) :

                $i = 1;

                foreach( $respond['jv'] as $jv) :

                    $csv_data[$i] = array(
                        $jv->display_name,
                        sejolisa_price_format( $jv->earning_value ),
                        sejolisa_price_format( $jv->expenditure_value ),
                        sejolisa_price_format( $jv->total_value )
                    );

                    $i++;

                endforeach;

            endif;

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

        $data  = [];

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

    /**
     * Set for single user table
     * Hooked via action wp_ajax_sejoli-jv-single-table, priority 1
     * @since 1.0.0
     */
    public function set_for_single_table() {

        $table = $this->set_table_args($_POST);

        $data    = [];

        if(
            current_user_can('manage_sejoli_jv_data') &&
            isset($_POST['nonce']) &&
            wp_verify_nonce($_POST['nonce'], 'sejoli-render-jv-single-table')
        ) :

            $jv_products = array();

            $user_id     = (
                                !current_user_can('manage_options') ||
                                !isset($_POST['user'])
                           ) ? get_current_user_id() :intval($_POST['user']);

            $respond     = sejoli_jv_get_single_user_data( $user_id, $table['filter'], $table);

            if(false !== $respond['valid']) :

                foreach( $respond['jv'] as $i => $jv) :

                    $data[$i] = (array) $jv;

                    $data[$i]['note']        = $this->set_note( $jv );
                    $data[$i]['created_at']  = date('Y M d', strtotime($jv->created_at));
                    $data[$i]['value']       = sejolisa_price_format( $jv->value );
                    $data[$i]['raw_value']   = floatval($jv->value);

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

    /**
     * Add expenditure data
     * Hooked via action wp_ajax_sejoli-jv-add-data, priority 10
     * @since   1.0.0
     */
    public function add_expenditure() {

        $response = array(
            'valid'     => true,
            'message'   =>__('Terjadi kesalahan di sistem. Coba lain kali', 'sejoli-jv')
        );

        $messages = array();

        $post_data = wp_parse_args($_POST, array(
            'nonce'      => NULL,
            'note'       => NULL,
            'amount'     => NULL,
            'product_id' => 0
        ));

        if( wp_verify_nonce($post_data['nonce'], 'sejoli-jv-add-data') ) :

            $amount = floatval( preg_replace('~\D~', '', $post_data['amount']) );

            if( 0 >= $amount ) :
                $messages[]        = __('Nilai harus diisi', 'sejoli-jv');
                $response['valid'] = false;
            endif;

            if( empty($post_data['note']) ) :
                $messages[]        = __('Catatan harus diisi', 'sejoli-jv');
                $response['valid'] = false;
            endif;

            if( 0 >= intval($post_data['product_id']) ) :

                $messages[]        = __('Produk harus dipilih', 'sejoli-jv');
                $response['valid'] = false;

            else :

                $product_id  = absint( $post_data['product_id'] );
                $jv_setup    = sejoli_jv_get_product_setup( $product_id );

                if( false === $jv_setup ) :

                    $messages[]        = __('Produk yang dipilih tidak memiliki pengaturan JV', 'sejoli-jv');
                    $response['valid'] = false;

                endif;

            endif;

            if( true === $response['valid'] ) :

                $timestamp = current_time('timestamp');

                foreach( $jv_setup as $setup ) :

                    $value = floatval( $setup['value_portion'] );

                    if( 'percentage' === $setup['value_type'] ) :
                        $value = floor( $amount * $value / 100 );
                    endif;

                    sejoli_jv_add_expend_data( array(
                        'expend_id'  => $timestamp,
                        'product_id' => $product_id,
                        'user_id'    => $setup['user'],
                        'value'      => $value,
                        'meta_data'  => array(
                                'note'  => $post_data['note']
                            )
                        )
                    );

                endforeach;


                $response['message'] = __('Penambahan nilai sudah berhasil', 'sejoli-jv');
            endif;

        endif;

        if(false === $response['valid']) :
            $response['message'] = implode( '<br />', $messages);
        endif;

        wp_send_json( $response );
        exit;
    }

    /**
     * Delete expenditure data
     * @since   1.0.0
     * @return  json
     */
    public function delete_expenditure() {

        $response = array(
            'valid'   => false,
            'message' => __('Anda tidak diizinkan untuk melakukan proses ini', 'sejoli-jv')
        );

        $post = wp_parse_args( $_POST, array(
            'expend_id' => NULL,
            'nonce'     => NULL
        ));

        if(
            wp_verify_nonce( $post['nonce'], 'sejoli-jv-delete-expenditure') &&
            (
                current_user_can('manage_options') ||
                current_user_can('manage_sejoli_jv_data')
            )
        ) :
            $valid = true;

            $delete_respond = sejoli_jv_delete_expend_data( intval($post['expend_id']) );

            if( $delete_respond['valid' ])  :
                $response['message'] = implode('<br />', $delete_respond['messages']['success'] );
            else :
                $valid = false;
                $response['message'] = implode('<br />', $delete_respond['messages']['error'] );
            endif;

            $response['valid'] = $valid;
        endif;

        echo wp_send_json($response);
        exit;
    }
}
