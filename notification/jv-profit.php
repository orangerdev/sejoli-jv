<?php
namespace Sejoli_JV\Notification;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class JVProfitNotification extends \SejoliSA\Notification\Main {

    /**
     * Recipient data
     * @since   1.0.0
     * @var     array
     */
    protected $recipiens;

    /**
     * Set user data
     * @var array
     */
    protected $user_data;

    /**
     * Set point data
     * @var array
     */
    protected $profit_data;

    /**
     * Attachment for file
     * @since   1.0.0
     * @var     bool|array
     */
    public $attachments = false;

    /**
     * Construction
     */
    public function __construct() {

        add_filter('sejoli/notification/fields',    [$this, 'add_setting_fields'], 122);

    }

    /**
     * Add notification setting fields
     * Hooked via filter, sejoli/notification/fields priority 122
     * @since   1.0.0
     * @param   array $fields All fields for notification setting form
     */
    public function add_setting_fields( array $fields ) {

        $fields['jv-profit'] = [
			'title'  => __('JV Pendapatan', 'sejoli-jv'),
			'fields' => [

                // Untuk jv partner
				Field::make('separator'	,'sep_jv_profit_email', 	__('Email' ,'sejoli-jv'))
					->set_help_text(__('Pengaturan konten untuk media email', 'sejoli-jv')),

				Field::make('text', 	'jv_profit_email_title',	 __('Judul' ,'sejoli-jv'))
					->set_required(true)
					->set_default_value(__('{{user-name}}, Pendapatan untuk anda sudah dibayarkan.', 'sejoli-jv')),

				Field::make('rich_text', 'jv_profit_email_content', __('Konten', 'sejoli-jv'))
					->set_required(true)
					->set_default_value(sejoli_get_notification_content('jv-profit-user')),

				Field::make('separator'	,'sep_jv_profit_sms', 	__('SMS' ,'sejoli-jv'))
					->set_help_text(__('Pengaturan konten untuk media sms', 'sejoli-jv')),

				Field::make('textarea', 'jv_profit_sms_content', __('Konten', 'sejoli-jv'))
                    ->set_default_value(sejoli_get_notification_content('jv-profit-user', 'sms')),

				Field::make('separator'	,'sep_jv_profit_whatsapp', 	__('WhatsApp' ,'sejoli-jv'))
					->set_help_text(__('Pengaturan konten untuk media whatsapp', 'sejoli-jv')),

				Field::make('textarea', 'jv_profit_whatsapp_content', __('Konten', 'sejoli-jv'))
                    ->set_default_value(sejoli_get_notification_content('jv-profit-user', 'whatsapp')),
			
			]
		];

        return $fields;

    }

    /**
     * Prepare content for notification
     * @since   1.0.0
     * @return  void
     */
    protected function set_content() {

        // ***********************
		// Setup content for buyer
		// ***********************

		$this->set_recipient_title  ('buyer', 'email', carbon_get_theme_option('jv_profit_email_title'));
		$this->set_recipient_content('buyer', 'email', $this->set_notification_content(
												carbon_get_theme_option('jv_profit_email_content'),
												'email',
                                                'buyer'
											 ));

		if( !empty(carbon_get_theme_option('jv_profit_whatsapp_content')) ) :

            $this->set_enable_send('whatsapp', 'buyer', true);
			$this->set_recipient_content('buyer', 'whatsapp', $this->set_notification_content(
		                                                carbon_get_theme_option('jv_profit_whatsapp_content'),
		                                                'whatsapp',
                                                        'buyer'
                                                    ));
        
        endif;

		if( !empty(carbon_get_theme_option('jv_profit_sms_content')) ) :
        
            $this->set_enable_send('sms', 'buyer', true);
			$this->set_recipient_content('buyer', 'sms', $this->set_notification_content(
                                    				carbon_get_theme_option('jv_profit_sms_content'),
                                    				'sms',
                                                    'buyer'
                                    			));
        
        endif;
    
    }

    /**
     * Check current media recipients, the data will be stored in $this->recipients
     * @since   1.0.0
     * @param   string  $media
     * @param   string  $role
     * @return  void
     */
    protected function check_recipients( $media = 'email', $role = 'admin' ) {

        $recipients       = carbon_get_theme_option('jv_profit_' . $role . '_' . $media . '_recipient');
        $this->recipients = explode(',', $recipients);
    
    }

    /**
     * Add user data to shortcodes
     * Hooked via filter sejoli/notification/shortcodes, priority 10
     * @param array $shortcodes
     * @return array
     */
    public function add_shortcode_detail( array $shortcodes ) {

        $shortcodes['{{site-url}}']     = home_url('/');
        $shortcodes['{{user-name}}']    = $this->user_data['user_name'];
        $shortcodes['{{buyer-name}}']   = $this->user_data['user_name'];
        $shortcodes['{{profit-value}}'] = $this->profit_data['unpaid_commission'];

        return $shortcodes;

    }

    /**
     * Trigger to send notification
     * @since   1.0.0
     * @param   array   $point_data   Point data
     * @param   array   $user_data
     * @return  void
     */
    public function trigger( $profit_data, $user_data ) {

        $this->profit_data = $profit_data;
        $this->user_data   = $user_data;
        $media_libraries   = $this->get_media_libraries();

        $this->shortcode_data = $this->add_shortcode_detail([]);
        $this->set_content();

        $this->trigger_email( $user_data, $media_libraries );
        $this->trigger_whatsapp( $user_data, $media_libraries );
        $this->trigger_sms( $user_data, $media_libraries );

    }

    /**
     * Trigger to send email
     * @since   1.0.0
     * @param   array   $user_data          Array of recipient data
     * @param   array   $media_libraries    Array of available media libraries
     * @return  void
     */
    protected function trigger_email( $user_data, $media_libraries ) {

        // send email for buyer
		$media_libraries['email']->set_data([
			'user_data' => $user_data,
		]);

		$media_libraries['email']->send(
			array($user_data['user_email']),
			$this->render_shortcode( $this->get_recipient_content('buyer', 'email') ),
			$this->render_shortcode( $this->get_recipient_title('buyer', 'email') )
		);

    }

    /**
     * Trigger to send whatsapp
     * @since   1.0.0
     * @param   array   $user_data          Array of recipient data
     * @param   array   $media_libraries    Array of available media libraries
     * @return  void
     */
    protected function trigger_whatsapp( $user_data, $media_libraries ) {

        // send whatsapp for buyer
        if( false !== $this->is_able_to_send('whatsapp', 'buyer') ) :

    		$media_libraries['whatsapp']->set_data([
                'user_data' => $user_data,
    		]);

            $media_libraries['whatsapp']->send(
    			array( $user_data['user_phone'] ),
    			$this->render_shortcode($this->get_recipient_content('buyer', 'whatsapp'))
    		);

        endif;

    }

    /**
     * Trigger to SMS whatsapp
     * @since   1.0.0
     * @param   array   $user_data          Array of recipient data
     * @param   array   $media_libraries    Array of available media libraries
     * @return  void
     */
    protected function trigger_sms( $user_data, $media_libraries ) {

        // send sms for buyer
        if( false !== $this->is_able_to_send('sms', 'buyer') ) :

    		$media_libraries['sms']->set_data([
                'user_data' => $user_data,
    		]);

            $media_libraries['sms']->send(
    			array( $user_data['user_phone'] ),
    			$this->render_shortcode( $this->get_recipient_content('buyer', 'sms') )
    		);
        
        endif;

    }

}