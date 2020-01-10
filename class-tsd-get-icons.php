<?php
/**
 * Retrive and output the SVG icons for WordPress theme and Plugins
 *
 * @package GetIcons
 * @version 0.1.0
 * @author Tremi DKhar
 * @copyright Copyright (c) 2020, Tremi Dkhar
 * @license GPL-2.0+
 * @link https://github.com/TremiDkhar/TSD-Get-Icons
 */

if ( ! class_exists( 'TSD_Get_Icons ' ) ) {

	/**
	 * Place each <svg> source in the /icons/{group}/ directory, without adding
	 * both `width` and `height` attributes, since these are added dynamically,
	 * before rendering the SVG code.
	 *
	 * All icons are assumed to have equal width and height, hence the option
	 * to only specify a `$size` parameter in the svg methods.
	 *
	 * @since 0.1.0
	 */
	class TSD_Get_Icons {


		private static $instance = null;

		private $default_atts = array(
			'icon'  => false,
			'group' => 'utility',
			'size'  => '32',
			'class' => false,
			'label' => false,
		);

		private $class = 'svg-icon';

		private $default_path = '';

		private $icon_path = '';

		private $icon = '';

		private $svg = '';

		private function __construct( $atts = array() ) {

			$this->default_path = dirname( __FILE__ );
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
		public static function get_instance( $atts = array() ) {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
				self::$instance = new self( $atts );
			}

			return self::$instance;
		}

		public function get_svg() {
			return $this->svg;
		}

	}

}

function tsd_get_icons( $atts = array() ) {
	$instance = call_user_func( array( 'TSD_Get_Icons', 'get_instance' ), $atts );
	return $instance->get_svg();
}

function tsd_the_icons( $atts = array() ) {
	echo tsd_get_icons( $atts );
}
