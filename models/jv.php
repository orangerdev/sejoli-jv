<?php
namespace SejoliJV\Model;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * @since   1.5.4
 * @var [type]
 */
Class JV extends \SejoliJV\Model
{
    static protected $ids            = [];
    static protected $table          = 'sejolisa_jv';
    static protected $value          = 0.0;
    static protected $status         = 'pending';
    static protected $type           = 'out';
    static protected $expend_id      = NULL;
    static protected $paid_status    = 0;
    static protected $paid_time      = '0000-00-00 00:00:00';

    /**
     * Set jv value ID
     * @since   1.5.4
     * @var string
     */
    static public function set_id($id) {
        self::$ids = !is_array($id) ? array($id) : $id;
        return new static;
    }

    /**
     * Set multiple IDS
     * @since   1.5.4
     * @var array
     */
    static public function set_multiple_id($ids) {
        self::$ids = (array) $ids;
        return new static;
    }

    /**
     * Set jv value value
     * @since   1.5.4
     * @var float
     */
    static public function set_value($value) {
        self::$value = floatval($value);
        return new static;
    }

    /**
     * Set status
     * @since   1.5.4
     * @var string
     */
    static public function set_status($status) {
        self::$status = (empty($status)) ? 'pending' : $status;
        return new static;
    }

    /**
     * Set paid status
     * @var bool
     */
    static public function set_paid_status($paid_status) {
        self::$paid_status = boolval($paid_status);
        return new static;
    }

    /**
     * Set paid time
     * @since   1.5.1
     * @var string
     */
    static public function set_paid_time($paid_time) {
        self::$paid_time = $paid_time;
        return new static;
    }

    /**
     * Set type
     * @since   1.5.4
     * @var string
     */
    static public function set_type($type) {
        self::$type = (empty($type)) ? 'out' : $type;
        return new static;
    }

    /**
     * Set expend id
     * @since   1.5.4
     * @var string
     */
    static public function set_expend($expend_id) {
        self::$expend_id = absint( $expend_id );
        return new static;
    }

    /**
     * Reset properties
     * @since   1.5.4
     * @var [type]
     */
    static public function reset() {

        parent::reset();

        self::$value     = 0.0;
        self::$status    = 'pending';
        self::$ids       = NULL;
        self::$expend_id = NULL;
        self::$type      = 'out';


        return new static;
    }

    /**
     * Validate data
     * @since   1.5.4
     * @return void
     */
    static protected function validate() {

        if(in_array(self::$action, ['add-earning', 'update-status'])) :

            if(0 === self::$order_id) :
                self::set_valid(false);
                self::set_message( __('Order tidak valid', 'sejoli-jv'));
            endif;

        endif;

        if(in_array(self::$action, array('add-expend', 'delete-expend'))) :

            if(empty(self::$expend_id)) :
                self::set_valid(false);
                self::set_message( __('Expend ID tidak valid', 'sejoli-jv'));
            endif;

        endif;

        if(in_array(self::$action, ['add-earning', 'add-expend'])) :

            if(!is_a(self::$user, 'WP_User')) :
                self::set_valid(false);
                self::set_message( __('Affiliasi tidak valid', 'sejoli-jv'));
            endif;

            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Produk tidak valid', 'sejoli-jv'));
            endif;

            if(0 === self::$value) :
                self::set_valid(false);
                self::set_message( __('Nilai tidak boleh 0', 'sejoli-jv'));
            endif;

        endif;

        if( in_array(self::$action, array('add-earning', 'update-status')) ) :

            if(!in_array(self::$status, ['pending', 'added', 'cancelled'])) :
                self::set_valid(false);
                self::set_message( sprintf(__('Status nilai %s tidak valid', 'sejoli-jv'), self::$status));
            endif;

        endif;
    }

    /**
     * Get jv values by filter
     * @since   1.5.4
     * @return [type] [description]
     */
    static function get() {
        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS JV' ) )
                            ->select( Capsule::raw('JV.*, user.display_name AS affiliate_name, product.post_title AS product_name') )
                            ->join( $wpdb->posts . ' AS product', 'product.ID', '=', 'jv value.product_id')
                            ->join( $wpdb->users . ' AS user', 'user.ID', '=', 'jv value.user_id')
                            ->where('JV.deleted_at', '=', '0000-00-00 00:00:00');

        $query        = self::set_filter_query( $query );
        $recordsTotal = $query->count();
        $query        = self::set_length_query($query);
        $jv_values  = $query->get()->toArray();

        if ( $jv_values ) :
            self::set_respond('valid',          true);
            self::set_respond('jv_values',      $jv_values);
            self::set_respond('recordsTotal',   $recordsTotal);
            self::set_respond('recordsFiltered',$recordsTotal);
        else:
            self::set_respond('valid',          false);
            self::set_respond('jv_values',      []);
            self::set_respond('recordsTotal',   0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get only first jv value
     * @since   1.5.4
     */
    static function first() {
        parent::$table = self::$table;

        $data = Capsule::table( Capsule::raw( self::table() . ' AS JV' ) )
                    ->whereIn('JV.ID', self::$ids)
                    ->first();

        if( $data ) :
            self::set_valid(true);
            self::set_respond('jv_value', $data);
        else :
            self::set_valid(false);
        endif;

        return new static;
    }

    /**
     * Update order status
     * @since   1.5.4
     */
    static function update_status() {

        self::set_action('update-status');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            Capsule::table(self::table())
                ->where('order_id', self::$order_id)
                ->update([
                    'updated_at' => current_time('mysql'),
                    'status'     => self::$status
                ]);

            self::set_valid(true);
            self::set_message(
                sprintf(
                    __('JV earning %s status updated to %s successfully', 'sejoli-jv'),
                    self::$order_id,
                    self::$status
                ),
                'success');
        endif;

        return new static;
    }

    /**
     * Add JV earning
     * @since   1.0.0
     */
    static public function add_earning() {

        self::set_action('add-earning');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $earning = array(
                'created_at' => current_time('mysql'),
                'order_id'   => self::$order_id,
                'product_id' => self::$product_id,
                'user_id'    => self::$user_id,
                'type'       => 'in',
                'value'      => self::$value,
                'status'     => self::$status,
                'meta_data'  => serialize( self::$meta_data )
            );

            $earning['ID'] = Capsule::table(self::table())
                                ->insertGetId($earning);

            self::set_valid     (true);
            self::set_respond   ('earning', $earning);

        endif;

        return new static;
    }

    /**
     * Add JV expend
     * @since   1.0.0
     */
    static public function add_expend() {

        self::set_action('add-expend');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $expend = array(
                'created_at' => current_time('mysql'),
                'expend_id'  => self::$expend_id,
                'product_id' => self::$product_id,
                'user_id'    => self::$user_id,
                'type'       => 'out',
                'value'      => self::$value,
                'status'     => self::$status,
                'meta_data'  => serialize( self::$meta_data )
            );

            $expend['ID'] = Capsule::table(self::table())
                                ->insertGetId($expend);

            self::set_valid     (true);
            self::set_respond   ('expend', $expend);

        endif;

        return new static;
    }

    /**
     * delete JV expend
     * @since   1.0.0
     */
    static public function delete_expend() {

        self::set_action('delete-expend');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            Capsule::table(self::table())
                ->where('expend_id', self::$expend_id)
                ->update([
                    'deleted_at' => current_time('mysql'),
                    'status'     => 'cancelled'
                ]);

            self::set_valid(true);
            self::set_message(
                sprintf(
                    __('JV expend %s deleted successfully', 'sejoli-jv'),
                    self::$expend_id,
                    self::$status
                ),
                'success');

        endif;

        return new static;
    }

    /**
     * Get all JV earning
     * @since   1.0.0
     */
    static public function get_all_earning() {

        global $wpdb;

        parent::$table = self::$table;

        $query  = Capsule::table( Capsule::raw( self::table() . ' AS JV' ))
                    ->select(
                        'JV.user_id',
                        'user.display_name',
                        'user.user_email',
                        Capsule::raw(
                            'SUM(CASE WHEN type = "in" THEN value ELSE 0 END) AS earning_value'
                        ),
                        Capsule::raw(
                            'SUM(CASE WHEN type = "out" THEN value ELSE 0 END) AS expenditure_value'
                        ),
                        Capsule::raw('SUM(CASE WHEN type = "in" AND status = "added" AND paid_status = 0 THEN JV.value ELSE 0 END) AS unpaid_commission '),
                        Capsule::raw('SUM(CASE WHEN type = "in" AND status = "added" AND paid_status = 1 THEN JV.value ELSE 0 END) AS paid_commission '),
                        Capsule::raw(
                            'SUM(
                                CASE
                                    WHEN type = "in" THEN value
                                    ELSE -value
                                END
                             ) AS total_value'
                        )
                    )
                    ->join(
                        $wpdb->users . ' AS user', 'user.ID', '=', 'JV.user_id'
                    )
                    ->where('status', 'added')
                    ->where('JV.deleted_at', '=', '0000-00-00 00:00:00')
                    ->orderBy('total_value', 'DESC')
                    ->groupBy('user_id');

        $query  = self::set_filter_query( $query );

        $result = $query->get();

        if($result) :

            self::set_valid(true);
            self::set_respond('jv', $result);

        else :

            self::set_valid(false);
            self::set_message( __('No JV data', 'sejoli-jv'));

        endif;

        return new static;
    }

    /**
     * Get single user data
     * @since   1.0.0
     */
    static public function get_single_user() {

        global $wpdb;

        parent::$table = self::$table;

        $query  = Capsule::table( Capsule::raw( self::table() . ' AS JV ') )
                    ->where('status', 'added')
                    ->where('user_id', self::$user_id)
                    ->where('JV.deleted_at', '=', '0000-00-00 00:00:00');;

        $query  = self::set_filter_query( $query );
        $query  = self::set_length_query( $query );

        $result = $query->get();

        if($result) :

            self::set_valid(true);
            self::set_respond('jv', $result);

        else :

            self::set_valid(false);
            self::set_message( __('No JV data', 'sejoli-jv'));

        endif;

        return new static;
    }

    /**
     * Get all JV related orders
     * @since   1.0.0
     */
    static public function get_orders() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS JV ') )
                        ->select(
                            Capsule::raw('data_order.*, user.display_name AS user_name, user.user_email AS user_email , product.post_title AS product_name, coupon.code AS coupon_code, affiliate.display_name AS affiliate_name, JV.value AS earning')
                        )
                        ->join( Capsule::raw( $wpdb->prefix . 'sejolisa_orders AS data_order'), 'data_order.ID', '=', 'JV.order_id')
                        ->join( Capsule::raw( $wpdb->users . ' AS user '), 'user.ID', '=', 'data_order.user_id')
                        ->join( Capsule::raw( $wpdb->posts . ' AS product '), 'product.ID', '=', 'data_order.product_id')
                        ->leftJoin( Capsule::raw( $wpdb->prefix . 'sejolisa_coupons AS coupon'), 'coupon.ID', '=', 'data_order.coupon_id')
                        ->leftJoin( Capsule::raw( $wpdb->users . ' AS affiliate'), 'affiliate.ID', '=', 'data_order.affiliate_id')
                        ->where('JV.user_id', self::$user_id)
                        ->where( 'JV.deleted_at', '=', '0000-00-00 00:00:00');

        $query        = self::set_filter_query( $query );

        $recordsTotal = $query->count();

        $query        = self::set_length_query($query);

        $orders       = $query->get()->toArray();

        if ( $orders ) :
            self::set_respond('valid',true);
            self::set_respond('orders',$orders);
            self::set_respond('recordsTotal',$recordsTotal);
            self::set_respond('recordsFiltered',$recordsTotal);
        else:
            self::set_respond('valid', false);
            self::set_respond('orders', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Update all single jv profit paid status
     * @since   1.1.3
     * @since   1.5.2   Add added status to jv profit that has been paid
     */
    static public function update_single_jv_profit_paid_status() {

        global $wpdb;

        self::set_action( 'update-single-jv-profit' );
        self::validate();

        if( true === self::$valid ) :

            parent::$table = self::$table;

            $query = Capsule::table( self::table() )
                        ->where('user_id', self::$user_id)
                        ->where('updated_at', '<=', self::$paid_time)
                        ->where('type', 'in')
                        ->where('status', 'added');

            $query = self::set_filter_query( $query );

            $result = $query->update(array(
                        'paid_status' => self::$paid_status
                    ));

            if( $result ) :
                self::set_valid(true);
            else :
                self::set_valid(false);
            endif;

        endif;

        return new static;
    
    }

}
