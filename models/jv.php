<?php
namespace SejoliJV\Model;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * @since   1.5.4
 * @var [type]
 */
Class JV extends \SejoliJV\Model
{
    static protected $ids         = [];
    static protected $table       = 'sejolisa_jv';
    static protected $value       = 0.0;
    static protected $status      = 'pending';
    static protected $type        = 'out';
    static protected $paid_status = 0;
    static protected $paid_time   = '0000-00-00 00:00:00';

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
     * Set type
     * @since   1.5.4
     * @var string
     */
    static public function set_type($type) {
        self::$type = (empty($type)) ? 'out' : $type;
        return new static;
    }


    /**
     * Reset properties
     * @since   1.5.4
     * @var [type]
     */
    static public function reset() {

        parent::reset();

        self::$value  = 0.0;
        self::$status = 'pending';
        self::$ids    = NULL;
        self::$type   = 'out';

        return new static;
    }

    /**
     * Validate data
     * @since   1.5.4
     * @return void
     */
    static protected function validate() {

        if(in_array(self::$action, ['create'])) :

            if(!is_a(self::$user, 'WP_User')) :
                self::set_valid(false);
                self::set_message( __('Affiliasi tidak valid', 'sejoli'));
            endif;

            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Produk tidak valid', 'sejoli'));
            endif;

            if(0 === self::$value) :
                self::set_valid(false);
                self::set_message( __('Nilai tidak boleh 0', 'sejoli'));
            endif;

            if( !in_array(self::$type, array('in', 'out') ) )  :
                self::set_valid(false);
                self::set_message( __('Tipe nilai tiak valid', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['create', 'update-status', 'update-paid-status'])) :

            if(!in_array(self::$status, ['pending', 'added', 'cancelled'])) :
                self::set_valid(false);
                self::set_message( sprintf(__('Status nilai %s tidak valid', 'sejoli'), self::$status));
            endif;

        endif;

    }

    /**
     * Save jv value data to database
     * @since   1.5.4
     */
    static function create() {

        self::set_action('create');
        self::validate();

        if(false !== self::$valid) :
            parent::$table = self::$table;

            $jv_value = [
                'created_at' => current_time('mysql'),
                'updated_at' => '0000-00-00 00:00:00',
                'deleted_at' => '0000-00-00 00:00:00',
                'product_id' => self::$product->ID,
                'user_id'    => self::$user->ID,
                'type'       => self::$type,
                'value'      => self::$value,
                'status'     => self::$status,
                'meta_data'  => serialize( self::$meta_data )
            ];

            $jv_value['ID'] = Capsule::table(self::table())
                                 ->insertGetId($jv_value);

            self::set_valid(true);
            self::set_respond('jv_value',$jv_value);
        endif;

        return new static;
    }

    /**
     * Get jv values by filter
     * @since   1.5.4
     * @return [type] [description]
     */
    static function get() {
        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS jv value' ) )
                            ->select( Capsule::raw('jv value.*, user.display_name AS affiliate_name, product.post_title AS product_name') )
                            ->join( $wpdb->posts . ' AS product', 'product.ID', '=', 'jv value.product_id')
                            ->join( $wpdb->users . ' AS user', 'user.ID', '=', 'jv value.user_id');

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

        $data = Capsule::table(self::$table())
                    ->whereIn('ID', self::$ids)
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
                ->whereIn('ID', self::$ids)
                ->update([
                    'updated_at' => current_time('mysql'),
                    'status'     => self::$status
                ]);

            self::set_valid(true);
            self::set_message(
                sprintf(
                    __('Commission %s status updated to %s successfully', 'sejoli'),
                    implode(", ", self::$ids),
                    self::$status
                ),
                'success');
        endif;

        return new static;
    }

    /**
     * Set data for chart purpose
     * @since   1.5.4
     */
    static function set_for_chart($type = 'total-order') {

        parent::$table = self::$table;

        self::calculate_chart_range_date();
        $columns = [];

        switch ($type) :
            case 'total-order':
                $columns[] = Capsule::raw('count(ID) AS total');
                break;

            case 'total-paid':
                $columns[] = Capsule::raw('sum(jv value) AS total');
                break;

        endswitch;

        $columns[] ='status';

        $groups = ['status'];

        if('year' === self::$chart['type']) :
            $columns[] = Capsule::raw('YEAR(created_at) AS year');
            $groups[]  = 'year';
        elseif('month' === self::$chart['type']) :
            $columns[] = Capsule::raw('DATE_FORMAT(created_at, "%Y-%m") AS month');
            $groups[]  = 'month';
        elseif('date' === self::$chart['type']) :
            $columns[] = Capsule::raw('DATE(created_at) AS date');
            $groups[]  = 'date';
        endif;

        $query = Capsule::table(self::table())
                    ->select($columns);

        $query = self::set_filter_query($query);
        $data  = $query->groupBy($groups)
                    ->get();

        self::set_respond('data' ,$data);
        self::set_respond('chart',self::$chart);

        return new static;
    }

}
