<?php
/**
 * Retrive and output the SVG icons for WordPress theme and Plugins
 *
 * @package GetIcons
 * @version 0.1.0
 * @author Tremi DKhar
 * @copyright Copyright (c) 2020, Tremi Dkhar
 * @license GPL-2.0+
 * @link https://github.com/TremiDkhar/TSD-Get-Icon
 */

if ( ! class_exists( 'TSD_Get_Icons ' ) ) {

	/**
	 * Main class To get the icon
	 * 
	 * Place each <svg> source in the /icons/{group}/ directory, without adding
	 * both `width` and `height` attributes, since these are added dynamically,
	 * before rendering the SVG code.
	 *
	 * All icons are assumed to have equal width and height, hence the option
	 * to only specify a `$size` parameter in the svg methods.
	 *
	 * @since 0.1.0
	 */
	class TSD_Get_Icon {

		/**
		 * Single instance of TSD_Get_Icon
		 *
		 * @since 0.1.0
		 * @var object TSD_Get_Icon
		 */
		private static $instance = null;

		/**
		 * Default attributes required for loading an icon
		 *
		 * @since 0.1.0
		 * @var array
		 */
		private $default_atts = array(
			'icon'  => false,
			'group' => 'utility',
			'size'  => '32',
			'class' => false,
			'label' => false,
		);

		/**
		 * Default class for the icon
		 *
		 * @since 0.1.0
		 * @var string
		 */
		private $class = 'svg-icon';
		
		/**
		 * Will hold the default path where all the icon is stored
		 *
		 * @since 0.1.0
		 * @var string
		 */
		private $default_path = '';

		/**
		 * The Absolute path to the icon
		 *
		 * @since 0.1.0
		 * @var string
		 */
		private $icon_path = '';

		/**
		 * Raw svg of the icon
		 *
		 * @since 0.1.0
		 * @var string
		 */
		private $icon = '';

		/**
		 * The pure icon store in svg format
		 *
		 * @since 0.1. 0
		 * @var string
		 */
		private $svg = '';

		/**
		 * Class constructor
		 *
		 * @since 0.1.0
		 * @param array $atts Attributes of icons supplied to the class for processing
		 */
		private function __construct( $atts = array() ) {

			$this->default_path = apply_filter( 'tsd_icon_path', dirname( __FILE__ ) );
			$atts               = shortcode_atts( $this->default_atts, $atts );

			if ( empty( $atts['icon'] ) ) {
				return;
			}

			$this->icon_path = $this->default_path . '/icons/' . $atts['group'] . '/' . $atts['icon'] . '.svg';

			if ( ! file_exists( $this->icon_path ) ) {
				return;
			}

			$this->icon = file_get_contents( $this->icon_path );

			if ( ! empty( $atts['class'] ) ) {
				$this->class .= ' ' . esc_attr( $atts['class'] . ' ' . esc_attr( $atts['icon'] ) );
			} else {
				$this->class .= ' ' . esc_attr( $atts['icon'] );
			}

			if ( false !== $atts['size'] ) {
				$repl      = sprintf( '<svg class="%1$s" width="%2$d" height="%2$d" aria-hidden="true" role="img" focusable="false"', $this->class, esc_attr( $atts['size'], ) );
				$this->svg = preg_replace( '/^<svg /', $repl, trim( $this->icon ) );
			} else {
				$this->svg = preg_replace( '/^<svg /', '<svg class="' . $this->class . '" ', trim( $this->icon ) );
			}

			$this->svg = preg_replace( "/([\n\t]+)/", ' ', $this->svg ); // Remove newlines & tabs.
			$this->svg = preg_replace( '/>\s*</', '><', $this->svg ); // Remove white space between SVG tags.

			if ( ! empty( $atts['label'] ) ) {
				$this->svg = str_replace( '<svg class', '<svg aria-label="' . esc_attr( $atts['label'] ) . '" class', $this->svg );
				$this->svg = str_replace( 'aria-hidden="true"', '', $this->svg );
			}

		}

		/**
		 * Allow for accessing the single instance of class.
		 * Also to insure only one instance exists in memory at one time
		 *
		 * @since 0.1.0
		 * @param array $atts Attributes of icons supplied to the class for processing.
		 * @return object
		 */
		public static function get_instance( $atts = array() ) {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
				self::$instance = new self( $atts );
			}

			return self::$instance;
		}

		/**
		 * Get the complete svg of the icon
		 *
		 * @since 0.1.0
		 * @return mixed
		 */
		public function get_svg() {
			return $this->svg;
		}

	}

}

/**
 * Get the required icon
 *
 * @since 0.1.0
 * @param array $atts Attributes requires to get the icon.
 * @return mixed
 */
function tsd_get_icon( $atts = array() ) {
	$instance = call_user_func( array( 'TSD_Get_Icon', 'get_instance' ), $atts );
	return $instance->get_svg();
}

/**
 * Echo the required icon
 *
 * @since 0.1.0
 * @param array $atts Attributes requires to get the icon.
 * @return void
 */
function tsd_the_icon( $atts = array() ) {
	echo tsd_get_icon( $atts );
}