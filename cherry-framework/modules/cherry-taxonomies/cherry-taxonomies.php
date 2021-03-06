<?php
/**
 * Create taxonomy
 *
 * @package    Cherry_Framework
 * @subpackage Class
 * @author     Cherry Team <cherryframework@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016, Cherry Team
 * @link       http://www.cherryframework.com/
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Cherry Taxonomy class
 *
 * Example usage:
 * $this->core->modules['cherry-taxonomies']->create( 'Property', 'property', 'Properties' )->init();
 */
class Cherry_Taxonomies implements I_Module {
	/**
	 * Module version
	 *
	 * @var string
	 */
	public $module_version = '1.0.0';

	/**
	 * Module slug
	 *
	 * @var string
	 */
	public $module_slug = 'cherry-taxonomies';

	/**
	 * Default post type arguments
	 *
	 * @var null
	 */
	private $defaults = null;

	/**
	 * Module directory
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $module_directory = '';

	/**
	 * Cherry_Post_Type class constructor
	 */
	public function __construct( $core, $args = array() ) {
		$this->defaults = $args;
		$this->module_directory = $core->settings['base_dir'] . '/modules/cherry-taxonomies';

		if ( ! class_exists( 'Cherry_Taxonomy' ) ) {
			require_once( $this->module_directory . '/cherry-taxonomy.php' );
		}
	}

	/**
	 * Create new Post Type.
	 *
	 * @param  [type] $single         name.
	 * @param  [type] $post_type_slug post types slug.
	 * @param  [type] $plural         name.
	 * @return Cherry_Post_Type
	 */
	public function create( $single, $post_type_slug = 'post', $plural = '' ) {
		$tax = new Cherry_Taxonomy( $single, $post_type_slug, $plural );

		$this->defaults = array_merge(
			$this->defaults,
			$this->get_default_arguments(
				$tax->get_single(),
				$tax->get_plural(),
				$tax->get_post_type_slug()
			)
		);
		$tax->set_arguments( $this->defaults );

		return $tax;
	}

	/**
	 * Get the taxonomy default arguments.
	 *
	 * @param [type] $plural The post type plural display name.
	 * @param [type] $singular The post type singular display name.
	 * @return array
	 */
	public function get_default_arguments( $plural, $singular, $post_type_slug ) {
		$labels = array(
			'name'              => __( $plural, 'tm-real-estate' ),
			'singular_name'     => __( $singular, 'tm-real-estate' ),
			'search_items'      => __( 'Search ' . $plural, 'tm-real-estate' ),
			'all_items'         => __( 'All ' . $plural, 'tm-real-estate' ),
			'parent_item'       => __( 'Parent ' . $singular, 'tm-real-estate' ),
			'parent_item_colon' => __( 'Parent ' . $singular . ' :', 'tm-real-estate' ),
			'edit_item'         => __( 'Edit ' . $singular, 'tm-real-estate' ),
			'update_item'       => __( 'Update ' . $singular, 'tm-real-estate' ),
			'add_new_item'      => __( 'Add New ' . $singular, 'tm-real-estate' ),
			'new_item_name'     => __( 'New ' . $singular . ' Name', 'tm-real-estate' ),
			'menu_name'         => __( $plural, 'tm-real-estate' ),
		);

		return array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => $post_type_slug ),
		);
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance( $core, $args ) {
		return new self( $core, $args );
	}
}
