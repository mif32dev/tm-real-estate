<?php
/**
 * Plugin Name: TM Real Estate
 * Plugin URI:  http://www.templatemonster.com/
 * Description: Plugin for adding real estate functionality to the site.
 * Version:     1.0.0
 * Author:      Guriev Eugen & Sergyj Osadchij
 * Author URI:  http://www.templatemonster.com/
 * Text Domain: tm-real-estate
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @package  TM Real Estate
 * @author   Guriev Eugen & Sergyj Osadchij
 * @license  GPL-2.0+
 */

/**
 * TemplateMonster real estate class plugin
 */
class TM_Real_Estate {

	/**
	 * A reference to an instance of this class.
	 * Singleton pattern implementation.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * A reference to an instance of cherry framework core class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private $core = null;

	/**
	 * Default options
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private static $default_options = array();

	/**
	 * TM_REAL_ESTATE class constructor
	 */
	private function __construct() {

		// Set the constants needed by the plugin.
		$this->constants();

		// Load all models
		$this->load_models();

		// Require cherry core
		if ( ! class_exists( 'Cherry_Core' ) ) {
			require_once( TM_REAL_ESTATE_DIR . '/cherry-framework/cherry-core.php' );
		}

		// Launch our plugin.
		add_action( 'after_setup_theme', array( $this, 'launch' ), 10 );

		// Add tm-re-properties shortcode
		add_shortcode( 'tm-re-properties', array( 'Model_Properties', 'shortcode_properties' ) );

		// Add tm-submit-form shortcode
		add_shortcode( 'tm-submit-form', array( 'Model_Submit_Form', 'shortcode_submit_form' ) );
		
		// Add ajax action
		add_action( 'after_setup_theme', array( 'Model_Submit_Form', 'register_ajax' ), 20 );

		// Scripts and Styles
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_and_styles' ) );

		// Register taxonomy Properties Types
		add_action( 'init', array( $this, 'taxonomy_properties_types' ) );

		// After activated plugin
		register_activation_hook( __FILE__, array( $this, 'plugin_activated' ) );

		// Custom assets
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Add Scripts and Styles
	 */
	public function scripts_and_styles() {
		// Styles
		wp_enqueue_style( 'tm-real-estate', TM_REAL_ESTATE_URI.'assets/css/tm-real-estate.css' );
	}

	/**
	 * Load plugin models
	 */
	public function load_models() {
		$models = array(
			'Model_Main',
			'Model_Properties',
			'Model_Submit_Form',
		);

		foreach ( $models as $model ) {
			if ( ! class_exists( $model ) ) {
				$path = 'models'.DIRECTORY_SEPARATOR.str_replace( '_', '-', strtolower ( $model ) ).'.php';
				require_once( $path );
			}
		}
	}

	/**
	 * Defines constants for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function constants() {
		/*
		 * Set constant path to the plugin directory.
		 *
		 * @since 1.0.0
		 */
		define( 'TM_REAL_ESTATE_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

		/**
		 * Set constant path to the plugin URI.
		 *
		 * @since 1.0.0
		 */
		define( 'TM_REAL_ESTATE_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
	}

	/**
	 * Loads the core functions. These files are needed before loading anything else in the
	 * theme because they have required functions for use.
	 *
	 * @since  1.0.0
	 */
	public function launch() {
		if ( is_admin() ) {
			if ( null !== $this->core ) {
				return $this->core;
			}

			$this->core = new Cherry_Core(
				array(
					'base_dir'	=> TM_REAL_ESTATE_DIR . 'cherry-framework',
					'base_url'	=> TM_REAL_ESTATE_URI . 'cherry-framework',
					'modules'	=> array(
						'cherry-js-core'	=> array(
							'priority'	=> 999,
							'autoload'	=> true,
						),
						'cherry-page-builder'	=> array(
							'priority'	=> 999,
							'autoload'	=> true,
						),
						'cherry-ui-elements' => array(
							'priority'	=> 999,
							'autoload'	=> true,
							'args'		=> array(
								'ui_elements' => array(
									'text',
									'select',
									'switcher',
									'collection',
									'media',
								),
							),
						),
						'cherry-post-meta'	=> array(
							'priority'	=> 999,
							'autoload'	=> true,
							'args'      => array(
								'title' => __( 'Settings', 'cherry' ),
								'page'  => array( 'property' ),
								'fields' => array(
									'price' => array(
										'type'       => 'text',
										'id'         => 'price',
										'name'       => 'property_price',
										'value'      => 0,
										'left_label' => __( 'Price', 'tm-real-estate' ),
									),
									'status' => array(
										'type'       => 'select',
										'id'         => 'status',
										'name'       => 'status',
										'value'      => 'rent',
										'left_label' => __( 'Property status', 'tm-real-estate' ),
										'options'    => array(
											'rent' => __( 'Rent', 'tm-real-estate' ),
											'sale' => __( 'Sale', 'tm-real-estate' ),
										),
									),
									'type' => array(
										'type'       => 'select',
										'id'         => 'type',
										'name'       => 'type',
										'value'      => 'rent',
										'left_label' => __( 'Property type', 'tm-real-estate' ),
										'options'    => array(
											'rent' => __( 'Rent', 'tm-real-estate' ),
											'sale' => __( 'Sale', 'tm-real-estate' ),
										),
									),
									'bathrooms' => array(
										'type'    => 'number',
										'id'      => 'bathrooms',
										'name'    => 'bathrooms',
										'value'   => 0,
										'left_label' => __( 'Bathrooms', 'tm-real-estate' ),
									),
									'bedrooms' => array(
										'type'    => 'number',
										'id'      => 'bedrooms',
										'name'    => 'bedrooms',
										'value'   => 0,
										'left_label' => __( 'Bedrooms', 'tm-real-estate' ),
									),
									'area' => array(
										'type'    => 'number',
										'id'      => 'area',
										'name'    => 'area',
										'value'   => 0,
										'left_label' => __( 'Area', 'tm-real-estate' ),
									),
									'gallery' => array(
										'type'	  => 'collection',
										'id'      => 'gallery',
										'name'    => 'gallery',
										'left_label' => __( 'Gallery', 'tm-real-estate' ),
										'controls' => array(
											'UI_Text' => array(
												'type'    => 'text',
												'id'      => 'title',
												'class'   => 'large_text',
												'name'    => 'title',
												'value'   => '',
												'left_label' => __( 'Title', 'tm-real-estate' ),
											),
											'UI_Media' => array(
												'id'           => 'image',
												'name'         => 'image',
												'value'        => '',
												'multi_upload' => false,
												'left_label'   => __( 'Image', 'tm-real-estate' ),
											),
										),
									),
									'tag' => array(
										'type'        => 'select',
										'id'          => 'tag',
										'name'        => 'tag',
										'multiple'	  => true,
										'value'       => '',
										'left_label'  => __( 'Tag', 'tm-real-estate' ),
										'options'     => Model_Main::get_tags(),
									),
									'categories' => array(
										'type'        => 'select',
										'id'          => 'categories',
										'name'        => 'categories',
										'multiple'	  => false,
										'value'       => '',
										'left_label'  => __( 'Categories', 'tm-real-estate' ),
										'options'     => Model_Main::get_categories(),
									),
									'agent' => array(
										'type'        => 'select',
										'id'          => 'agent',
										'name'        => 'agent',
										'multiple'	  => false,
										'value'       => '',
										'left_label'  => __( 'Agent', 'tm-real-estate' ),
										'options'     => Model_Main::get_agents(),
									),
								),
							),
						),
						'cherry-post-types' => array(
							'priority'	=> 999,
							'autoload'	=> true,
						),
					),
				)
			);

			$this->add_admin_menu_page();
			$this->add_post_type();
			$this->add_user_role();
		}
	}

	/**
	 * Get pages list
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_pages_list() {
		$args = array(
			'sort_order'  => 'asc',
			'sort_column' => 'post_title',
			'post_type'   => 'page',
			'post_status' => 'publish',
		);

		$pages = get_pages( $args );

		$pages_list = array();
		foreach ( $pages as $page ) {
			$pages_list[ $page->ID ] = $page->post_title;
		}

		return $pages_list;
	}

	/**
	 * Registry taxonomy
	 *
	 * @since 1.0
	 * @return void
	 */
	public function taxonomy_properties_types() {

		// Create taxonomy Property Type
		$labels = array(
			'name'              => __( 'Properties Types', 'tm-real-estate' ),
			'singular_name'     => __( 'Property Type', 'tm-real-estate' ),
			'search_items'      => __( 'Search Properties Types' ),
			'all_items'         => __( 'All Properties Types' ),
			'parent_item'       => __( 'Parent Property Type' ),
			'parent_item_colon' => __( 'Parent Property Type:' ),
			'edit_item'         => __( 'Edit Property Type' ),
			'update_item'       => __( 'Update Property Type' ),
			'add_new_item'      => __( 'Add New Property Type' ),
			'new_item_name'     => __( 'New Property Type Name' ),
			'menu_name'         => __( 'Properties Types' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'property' ),
		);

		register_taxonomy( 'property-type', array( 'property' ), $args );

		// Create terms for taxonomy Property Type
		$terms = array(
			'Commercial'	=> array( 'slug' => 'commercial', 'parent' => null ),
			'Shop'			=> array( 'slug' => 'shop', 'parent' => 'commercial' ),
			'Office'		=> array( 'slug' => 'office', 'parent' => 'commercial' ),

			'Residential'			=> array( 'slug' => 'residential', 'parent' => null ),
			'Appartment'			=> array( 'slug' => 'appartment', 'parent' => 'residential' ),
			'Appartment Building'	=> array( 'slug' => 'appartment-building', 'parent' => 'residential' ),
			'Villa'					=> array( 'slug' => 'villa', 'parent' => 'residential' ),
		);
		foreach ( $terms as $title => &$term ) {
			if ( ! term_exists( $term['slug'], 'property-type', $term['parent'] ) ) {
				if ( $term['parent'] ) {
					$term_data = get_term_by( 'slug', ucfirst( $term['parent'] ), 'property-type' );
					$parent = $term_data->term_id;
				} else {
					$parent = null;
				}
				$result = wp_insert_term(
					$title,
					'property-type',
						array(
							'slug'   => $term['slug'],
							'parent' => $parent,
						)
					);
				$term['id'] = $result['term_id'];
			}
		}
	}

	/**
	 * Set default options
	 *
	 * @since 1.0
	 * @return void
	 */
	public function plugin_activated() {

		// List default options
		self::$default_options = array(
			'tm-properties-main-settings'	=> array(
				'properties-list-page'			=> null,
				'property-item-page'			=> null,
				'properties-list-page'			=> null,
				'properties-submission-page'	=> null,
				'reset-default-page'					=> 'Reset to default page',
				'area-unit'						=> 'meters',
				'сurrency-sign'					=> '$',
			),
			'tm-properties-contact-form'	=> array(
				'mail-subject'		=> __( 'New mail', 'tm-real-estate' ),
				'success-message'	=> __( 'Message send', 'tm-real-estate' ),
				'failed-message'	=> __( 'Message don`t send', 'tm-real-estate' ),
			),
			'tm-properties-submission-form'	=> array(
				'mail-subject'		=> __( 'New mail', 'tm-real-estate' ),
				'success-message'	=> __( 'Message send', 'tm-real-estate' ),
				'failed-message'	=> __( 'Message don`t send', 'tm-real-estate' ),
			),
		);

		// Page parameter
		$page_parameter = array(
			'post_title'	=> __( 'Properties list', 'tm-real-estate' ),
			'post_content'	=> '[tm-real-estate-list]', // must be change
			'post_status'	=> 'publish',
			'post_author'	=> 1,
			'post_type'		=> 'page',
		);

		// Insert page
		self::$default_options['tm-properties-main-settings']['properties-list-page']			= wp_insert_post( $page_parameter );

		$page_parameter['post_title']	= __( 'Property item', 'tm-real-estate' );
		$page_parameter['post_content']	= '[tm-real-estate-item]'; // must be change
		self::$default_options['tm-properties-main-settings']['property-item-page']	= wp_insert_post( $page_parameter );

		$page_parameter['post_title']	= __( 'Search result', 'tm-real-estate' );
		$page_parameter['post_content']	= '[tm-real-estate-search]'; // must be change
		self::$default_options['tm-properties-main-settings']['properties-search-result-page']	= wp_insert_post( $page_parameter );

		$page_parameter['post_title']	= __( 'Search result', 'tm-real-estate' );
		$page_parameter['post_content']	= '[tm-real-estate-search]'; // must be change
		self::$default_options['tm-properties-main-settings']['properties-submission-page']		= wp_insert_post( $page_parameter );

		foreach ( self::$default_options as $section => $settings ) {
			add_option( $section, $settings );
		}
	}

	/**
	 * Add some admin menu
	 *
	 * @since 1.0
	 */
	public function add_admin_menu_page() {

		$sections = array(

			'tm-properties-main-settings' => array(
				'slug'			=> 'tm-properties-main-settings',
				'name'			=> __( 'Main', 'tm-real-estate' ),
				'description'	=> '',
			),

			'tm-properties-contact-form' => array(
				'slug'			=> 'tm-properties-contact-form',
				'name'			=> __( 'Contact form', 'tm-real-estate' ),
			),

			'tm-properties-submission-form' => array(
				'slug'			=> 'tm-properties-submission-form',
				'name'			=> __( 'Submission form', 'tm-real-estate' ),
			),

		);

		$settings['tm-properties-main-settings'][] = array(
			'type'			=> 'select',
			'slug'			=> 'properties-list-page',
			'title'			=> __( 'Properties list page', 'tm-real-estate' ),
			'field'			=> array(
				'id'			=> 'properties-list-page',
				'size'			=> 1,
				'value'			=> '',
				'options'		=> $this->get_pages_list(),
			),
		);

		$settings['tm-properties-main-settings'][] = array(
			'type'			=> 'select',
			'slug'			=> 'property-item-page',
			'title'			=> __( 'Property item page', 'tm-real-estate' ),
			'field'			=> array(
				'id'			=> 'property-item-page',
				'size'			=> 1,
				'value'			=> '',
				'options'		=> $this->get_pages_list(),
			),
		);

		$settings['tm-properties-main-settings'][] = array(
			'type'			=> 'select',
			'slug'			=> 'properties-search-result-page',
			'title'			=> __( 'Search result page', 'tm-real-estate' ),
			'field'			=> array(
				'id'			=> 'properties-search-result',
				'size'			=> 1,
				'value'			=> '',
				'options'		=> $this->get_pages_list(),
			),
		);

		$settings['tm-properties-main-settings'][] = array(
			'type'			=> 'select',
			'slug'			=> 'properties-submission-page',
			'title'			=> __( 'Submission property page', 'tm-real-estate' ),
			'field'			=> array(
				'id'			=> 'properties-submission-page',
				'size'			=> 1,
				'value'			=> '',
				'options'		=> $this->get_pages_list(),
			),
		);

		$settings['tm-properties-main-settings'][] = array(
			'slug'	=> 'reset-default-page',
			'title'	=> '',
			'type'	=> 'text',
			'field'	=> array(
				'type'		=> 'button',
				'id'		=> 'reset-default-page',
				'class'		=> 'button button-warning pull-right',
			),
		);

		$settings['tm-properties-main-settings'][] = array(
			'type'			=> 'select',
			'slug'			=> 'area-unit',
			'title'			=> __( 'Area unit', 'tm-real-estate' ),
			'field'			=> array(
				'id'			=> 'area-unit',
				'size'			=> 1,
				'value'			=> 'feets',
				'options'		=> array(
					'feets'	=> 'feets',
					'meters'	=> 'meters',
				),
			),
		);

		$settings['tm-properties-main-settings'][] = array(
			'slug'	=> 'сurrency-sign',
			'title'	=> __( 'Currency sign', 'tm-real-estate' ),
			'type'	=> 'text',
			'field'	=> array(
				'id'			=> 'сurrency-sign',
				'value'			=> '$',
				'placeholder'	=> '$',
			),
		);

		$settings['tm-properties-contact-form'][] = array(
			'slug'	=> 'mail-subject',
			'title'	=> __( 'Subject of email', 'tm-real-estate' ),
			'type'	=> 'text',
			'field'	=> array(
				'id'			=> 'mail-subject',
				'value'			=> '',
				'placeholder'	=> 'subject',
			),
		);

		$settings['tm-properties-contact-form'][] = array(
			'slug'	=> 'success-message',
			'title'	=> __( 'Success message', 'tm-real-estate' ),
			'type'	=> 'text',
			'field'	=> array(
				'id'			=> 'success-message',
				'value'			=> '',
				'placeholder'	=> 'successfully',
			),
		);

		$settings['tm-properties-contact-form'][] = array(
			'slug'	=> 'failed-message',
			'title'	=> __( 'Failed message', 'tm-real-estate' ),
			'type'	=> 'text',
			'field'	=> array(
				'id'			=> 'failed-message',
				'value'			=> '',
				'placeholder'	=> 'failed',
			),
		);

		$settings['tm-properties-submission-form'][] = array(
			'slug'	=> 'mail-subject',
			'title'	=> __( 'Subject of email', 'tm-real-estate' ),
			'type'	=> 'text',
			'field'	=> array(
				'id'			=> 'mail-subject',
				'value'			=> '',
				'placeholder'	=> 'subject',
			),
		);

		$settings['tm-properties-submission-form'][] = array(
			'slug'	=> 'success-message',
			'title'	=> __( 'Success message', 'tm-real-estate' ),
			'type'	=> 'text',
			'field'	=> array(
				'id'			=> 'success-message',
				'value'			=> '',
				'placeholder'	=> 'successfully',
			),
		);

		$settings['tm-properties-submission-form'][] = array(
			'slug'	=> 'failed-message',
			'title'	=> __( 'Failed message', 'tm-real-estate' ),
			'type'	=> 'text',
			'field'	=> array(
				'id'			=> 'failed-message',
				'value'			=> '',
				'placeholder'	=> 'failed',
			),
		);

		$page = new Cherry_Page_Builder( $this->core );

		$page->make( 'cherry-property-settings', 'Property Settings', null )->set(
			array(
				'capability'	=> 'manage_options',
				'position'		=> 22,
				'icon'			=> 'dashicons-admin-site',
				'sections'		=> $sections,
				'settings'		=> $settings,
				'before'		=> 'Before page description',
				'after'			=> 'After page description',
			)
		);
	}

	/**
	 * Include assets files
	 *
	 * @since 1.0
	 * @return void
	 */
	public function assets() {

		wp_enqueue_script(
			'tm-real-state-settings-page',
			plugins_url( 'tm-real-estate' ) . '/assets/js/page-settings.min.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_enqueue_style(
			'tm-real-state-settings-page',
			plugins_url( 'tm-real-estate' ) . '/assets/css/page-settings.min.css',
			array(),
			'1.0.0',
			'all'
		);
	}

	/**
	 * Add property post type
	 */
	public function add_post_type() {
		$this->core->modules['cherry-post-types']->create(
			'property',
			'Property',
			'Properties',
			array(
				'supports' => array(
					'title',
					'editor',
					'author',
					'thumbnail',
					'excerpt',
					'comments',
				),
			)
		)->font_awesome_icon( 'f1ad' );
	}

	/**
	 * Add RE Agent role
	 *
	 * @return  WP_Role / NULL
	 */
	public function add_user_role() {
		return add_role(
			're_agent',
			__( 'RE Agent', 'tm-real-estate' ),
			array(
				'read'              => true, // true allows this capability
				'edit_posts'        => true, // Allows user to edit their own posts
				'edit_pages'        => true, // Allows user to edit pages
				'edit_others_posts' => true, // Allows user to edit others posts not just their own
				'create_posts'      => true, // Allows user to create new posts
				'manage_categories' => true, // Allows user to manage post categories
				'publish_posts'     => true, // Allows the user to publish, otherwise posts stays in draft mode
				'edit_themes'       => false, // false denies this capability. User can’t edit your theme
				'install_plugins'   => false, // User cant add new plugins
				'update_plugin'     => false, // User can’t update any plugins
				'update_core'       => false, // user cant perform core updates
			)
		);
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}

TM_Real_Estate::get_instance();
