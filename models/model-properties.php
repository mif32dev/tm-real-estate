<?php
/**
 * Properties
 * @package    Cherry_Framework
 * @subpackage Model
 * @author     Cherry Team <cherryframework@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016, Cherry Team
 * @link       http://www.cherryframework.com/
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Model properties
 */
class Model_Properties {
	/**
	 * Get all properties
	 *
	 * @param  integer $posts_per_page count.
	 * @return array properties
	 */
	public static function get_properties( $posts_per_page = 5 ) {
		 $args = array(
			'posts_per_page'   => $posts_per_page,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'property',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	       => '',
			'post_status'      => 'publish',
			'suppress_filters' => true,
		);
		$properties = (array) get_posts( $args );
		if ( count( $properties ) ) {
			foreach ( $properties as &$property ) {
				$property->meta    = get_metadata( 'post', $property->ID, '', true );
				$property->images  = self::get_all_post_images( $property->ID );
				$property->image   = self::get_image( $property->ID );
				$property->gallery = self::get_gallery( $property->ID );
				$property->status  = self::get_property_status( $property->ID );
			}
		}
		return $properties;
	}

	/**
	 * Shortcode properties
	 *
	 * @param  [type] $atts attributes.
	 * @return html code.
	 */
	public static function shortcode_properties( $atts ) {
		$posts_per_page = 5;
		if ( is_array( $atts ) && array_key_exists( 'posts_per_page', $atts ) ) {
			$posts_per_page = $atts['posts_per_page'];
		}

		$properties = (array) self::get_properties( $posts_per_page );

		return Cherry_Core::render_view(
			TM_REAL_ESTATE_DIR . '/views/property.php',
			array( 'properties' => $properties )
		);
	}

	/**
	 * Get property status
	 *
	 * @param  [type] $post_id id.
	 * @return string property status.
	 */
	public static function get_property_status( $post_id ) {
		$allowed = self::get_allowed_property_statuses();
		$type    = (string) get_post_meta( $post_id, 'status', true );
		if ( array_key_exists( $type, $allowed ) ) {
			return $type;
		}
		return end( $allowed );
	}

	/**
	 * Get allowed property statuses
	 *
	 * @return array property statuses.
	 */
	public static function get_allowed_property_statuses() {
		return array(
			'rent' => __( 'Rent', 'tm-real-estate' ),
			'sale' => __( 'Sale', 'tm-real-estate' ),
		);
	}

	/**
	 * Get gallery
	 *
	 * @param  [type] $post_id id.
	 * @return property gallery.
	 */
	public static function get_gallery( $post_id ) {
		$gallery = get_post_meta( $post_id, 'gallery', true );
		if ( array_key_exists( 'image', (array) $gallery ) ) {
			foreach ( $gallery['image'] as &$image ) {
				$image = self::get_all_images( $image );
			}
		}
		return $gallery;
	}

	/**
	 * Get property image
	 *
	 * @param  [type] $post_id property id.
	 * @return string image.
	 */
	public static function get_image( $post_id ) {
		$images = self::get_all_post_images( $post_id );
		if ( array_key_exists( 'medium', $images ) ) {
			return $images['medium'][0];
		}
		return TM_REAL_ESTATE_URI.'assets/image/placehold.png';
	}

	/**
	 * Get all post images
	 *
	 * @param  [type] $post_id post id.
	 * @return array all post images.
	 */
	public static function get_all_post_images( $post_id ) {
		if ( has_post_thumbnail( $post_id ) ) {

			$attachment_id = get_post_thumbnail_id( $post_id );
			return self::get_all_images( $attachment_id );

		}
		return array();
	}

	/**
	 * Get all images by attachmen id
	 *
	 * @param  [type] $attachment_id id.
	 * @return array size => image
	 */
	public static function get_all_images( $attachment_id ) {
		$result = array();
		$sizes  = get_intermediate_image_sizes();

		if ( is_array( $sizes ) && count( $sizes ) ) {
			foreach ( $sizes as $size ) {
				$result[ $size ] = wp_get_attachment_image_src( $attachment_id, $size );
			}
		}
		return $result;
	}
}