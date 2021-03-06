<?php

namespace Sejoli_JV\Front;

class Member {

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
     * Menu position
     * @since   1.0.0
     * @var     integer
     */
    protected $menu_position = 1;

    /**
     * Registered point member menu list
     * @since   1.0.0
     * @var     array
     */
    protected $member_menu = array();

    /**
     * JV-related products
     * @since   1.0.0
     * @var     array
     */
    protected $products = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->member_menu = array(
                                'link'    => 'javascript::void(0)',
                                'label'   => __('Data JV', 'sejoli'),
                                'icon'    => 'users icon',
                                'class'   => 'item',
                                'submenu' => array(
                                    'jv-earning' => array(
                                        'link'    => site_url('member-area/jv-earning'),
                                        'label'   => __('Mutasi', 'sejoli'),
                                        'icon'    => '',
                                        'class'   => 'item',
                                        'submenu' => array()
                                    ),
                                    'jv-order' => array(
                                        'link'    => site_url('member-area/jv-order'),
                                        'label'   => __('Order ', 'sejoli'),
                                        'icon'    => '',
                                        'class'   => 'item',
                                        'submenu' => array()
                                    )
                                )
                            );
	}

	/**
	 * Set jv product based on current user
	 * Hooked via action wp_loaded, priority 1010
	 * @since 	1.0.0
	 */
	public function set_jv_products() {

		if(current_user_can('manage_sejoli_jv_data') || current_user_can('manage_options')) :

			$this->products = (array) get_user_meta( get_current_user_id(), 'sejoli_jv_data', true);

		endif;
	}

    /**
     * Set local js variables
     * Hooked via action wp_enqueue_scripts, priority 1111
     * @since   1.0.0
     */
    public function set_localize_js_var() {

        if(!current_user_can('manage_sejoli_jv_data')) :
			return;
		endif;

        wp_localize_script( 'sejoli-member-area', 'sejoli_jv', array(
            'order' => array(
                'link' => add_query_arg(array(
                    'action'    => 'sejoli-jv-order-table',
                ), admin_url('admin-ajax.php')),

				'nonce'	=> wp_create_nonce('sejoli-jv-set-for-table')
            ),
			'earning' => array(
                'link' => add_query_arg(array(
                    'action'    => 'sejoli-jv-user-earning-table',
                ), admin_url('admin-ajax.php')),

				'nonce'	=> wp_create_nonce('sejoli-render-jv-single-table')
            ),
			'export_prepare' => array(
				'link'	=> add_query_arg(array(
						'action' => 'sejoli-jv-order-export-prepare'
				), admin_url('admin-ajax.php')),

				'nonce' => wp_create_nonce('sejoli-jv-order-export-prepare')
			),
			'export_earning_prepare' => array(
				'link'	=> add_query_arg(array(
						'action' => 'sejoli-jv-earning-export-prepare'
				), admin_url('admin-ajax.php')),

				'nonce' => wp_create_nonce('sejoli-jv-earning-export-prepare')
			),
			'products' => $this->products
        ));
    }

    /**
     * Register member area menu
     * Hooked via filter sejoli/member-area/menu, priority 11
     * @since   1.0.0
     * @param   array  $menu
     * @return  array
     */
    public function register_menu( array $menu ) {

        $menu = array_slice($menu, 0, $this->menu_position, true) +
                array( 'jv-page' => $this->member_menu )+
                array_slice($menu, $this->menu_position, count($menu) - 1, true);

        return $menu;
    }

    /**
     * Add point menu to menu backend area
     * Hooked via filter sejoli/member-area/backend/menu, priority 1111
     * @since   1.0.0
     * @param   array   $menu
     * @return  array
     */
    public function add_menu_in_backend(array $menu) {

        $point_menu = array(
            'title'  => __('Data JV', 'sejoli'),
            'object' => 'sejoli-user-jv',
            'url'    => site_url('member-area/jv-earning')
        );

        // Add point menu in selected position
        $menu   =   array_slice($menu, 0, $this->menu_position, true) +
                    array('jv-page' => $point_menu) +
                    array_slice($menu, $this->menu_position, count($menu) - 1, true);

        return $menu;
    }

    /**
     * Display link list for point member link
     * Hooked via filter sejoli/member-area/menu-link, priority 1
     * @since   1.0.0
     * @param   string  $output
     * @param   object  $object
     * @param   array   $args
     * @param   array   $setup
     * @return  string
     */
    public function display_link_list_in_menu($output, $object, $args, $setup) {

        if('sejoli-user-jv' === $object->object) :
            // YES IM LAZY
            extract($args);

            ob_start();
            ?>
            <div class="master-menu">
                <a href="javascript:void(0)" class='item'>
                    <i class='users icon'></i>
                    <?php echo $object->post_title; ?>
                </a>
                <ul class="menu">
                <?php foreach( $this->member_menu['submenu'] as $submenu ) : ?>
                    <li>
                        <a href="<?php echo $submenu['link']; ?>" class="<?php echo $submenu['class']; ?>">
                        <?php if( !empty( $submenu['icon'] ) ) : ?>
                        <i class="<?php echo $submenu['icon']; ?>"></i>
                        <?php endif; ?>
                        <?php echo $submenu['label']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php

            $item_output = ob_get_contents();
            ob_end_clean();

            return $item_output;
        endif;

        return $output;
    }

    /**
     * Set template file for point menu template
     * Hooked via sejoli/template-file, priority 111
     * @since   1.0.0
     * @param   string  $file
     * @param   string  $view_request
     */
    public function set_template_file(string $file, string $view_request) {

        if( in_array( $view_request, array('jv-earning', 'jv-order')) ) :

            if(
                !current_user_can('manage_options') &&
                !current_user_can('manage_sejoli_jv_data')
            ) :
                return SEJOLI_JV_DIR . 'template/no-jv-user.php';
            endif;

            if('jv-earning' === $view_request) :

				if( 0 < count($this->products)) :
                	return SEJOLI_JV_DIR . 'template/jv-earning.php';
				else :
	                return SEJOLI_JV_DIR . 'template/no-jv-product.php';
	            endif;

            elseif('jv-order' === $view_request) :

                if( 0 < count($this->products)) :
                    return SEJOLI_JV_DIR . 'template/jv-order.php';
                else :
                    return SEJOLI_JV_DIR . 'template/no-jv-product.php';
                endif;

            endif;

        endif;

        return $file;
    }
}
