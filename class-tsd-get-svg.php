<?php
/**
 * Retrive the required svg file and use for output along with the html file
 * within the WordPress theme and plugin.
 *
 * @package     TSD-Get-SVG
 * @version     0.1.2

 * @author      Tremi DKhar
 * @copyright   Copyright (c) 2020, Tremi Dkhar
 * @license     GPL-2.0+
 * @link        https://github.com/TremiDkhar/tsd-get-svg
 */

/*
	Copyright 2019 Tremi Dkhar (https://github.com/TremiDkhar)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'TSD_Get_SVG' ) ) {

	/**
	 * Main class To get the svg content.
	 * Inspired by the helper function of Bill Erickson
	 *
	 * @see https://github.com/billerickson/EA-Starter/blob/master/inc/helper-functions.php#L128
	 *
	 * @since 0.1.0
	 */
	class TSD_Get_SVG {

		/**
		 * Single instance of TSD_Get_SVG
		 *
		 * @static
		 * @since 0.1.0
		 * @var object TSD_Get_SVG
		 */
		private static $instance = null;

		/**
		 * Default attributes required for loading an icon
		 *
		 * @since 0.1.0
		 * @static
		 * @var array
		 */
		private static $default_atts = array(
			'icon'  => false,
			'size'  => '24',
			'class' => false,
			'label' => false,
			'color' => false,
		);

		/**
		 * Holds the requested icon attributes
		 *
		 * @since 0.2.1
		 * @var array
		 */
		private $icon_atts = array();

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
		 * Allow for accessing the single instance of class.
		 * Also to insure only one instance exists in memory at one time
		 *
		 * @since 0.1.0
		 * @param array $atts Attributes of icons supplied to the class for processing.
		 * @return object
		 */
		public static function get_instance( $atts = array() ) {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			// Set the path that contain the svg file.
			self::$instance->default_path = apply_filters( 'tsd_get_svg_path', dirname( __FILE__ ) );

			// Start building the svg content.
			self::$instance->build_svg( $atts );

			return self::$instance;
		}

		/**
		 * Method to rendered and build the svg icon
		 *
		 * @since 0.2.1
		 * @param array $atts Attributes of icons supplied to the class for processing.
		 * @return void
		 */
		private function build_svg( $atts = array() ) {

			// Reset the previous build icon.
			$this->svg = '';

			// Setting the default option.
			$this->icon_atts = shortcode_atts( self::$default_atts, $atts );

			if ( empty( $this->icon_atts['icon'] ) ) {
				return;
			}

			$this->icon_path = $this->default_path . '/' . $this->icon_atts['icon'] . '.svg';

			if ( ! file_exists( $this->icon_path ) ) {
				return;
			}

			$this->icon = file_get_contents( $this->icon_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The file is a local file

			if ( ! empty( $this->icon_atts['class'] ) ) {
				$this->class = 'svg-icon ' . esc_attr( $this->icon_atts['class'] );
			} else {
				$this->class = 'svg-icon ' . esc_attr( $this->icon_atts['icon'] );
			}

			if ( false !== $this->icon_atts['size'] ) {
				$repl      = sprintf( '<svg class="%1$s" width="%2$d" height="%2$d" aria-hidden="true" role="img" focusable="false"', $this->class, esc_attr( $this->icon_atts['size'], ) );
				$this->svg = preg_replace( '/^<svg /', $repl, trim( $this->icon ) );
			} else {
				$this->svg = preg_replace( '/^<svg /', '<svg class="' . $this->class . '" ', trim( $this->icon ) );
			}

			$this->svg = preg_replace( "/([\n\t]+)/", ' ', $this->svg ); // Remove newlines & tabs.
			$this->svg = preg_replace( '/>\s*</', '><', $this->svg ); // Remove white space between SVG tags.

			if ( ! empty( $this->icon_atts['label'] ) ) {
				$this->svg = str_replace( '<svg class', '<svg aria-label="' . esc_attr( $this->icon_atts['label'] ) . '" class', $this->svg );
				$this->svg = str_replace( 'aria-hidden="true"', '', $this->svg );
			}

			if ( false !== $this->icon_atts['color'] ) {
				// Check if the Hex color is valid.
				if ( preg_match( '/^#(([0-9a-fA-F]{2}){3}|[0-9a-fA-F]{3})$/', $this->icon_atts['color'] ) ) {
					$this->svg = str_replace( '<path fill-rule', '<path fill="' . esc_attr( $this->icon_atts['color'] ) . '" fill-rule', $this->svg );
				}
			}
		}

		/**
		 * Get the content of the svg
		 *
		 * @since 0.1.0
		 * @return mixed
		 */
		public function get_svg() {
			return $this->svg;
		}

	}

}

if ( ! function_exists( 'tsd_get_svg' ) ) {
	/**
	 * Return the content of the svg file
	 *
	 * @since 0.1.0
	 * @param array $atts Attributes requires to get the icon.
	 * @return mixed
	 */
	function tsd_get_svg( $atts = array() ) {
		$instance = call_user_func( array( 'TSD_Get_SVG', 'get_instance' ), $atts );
		return $instance->get_svg();
	}
}

if ( ! function_exists( 'tsd_the_svg' ) ) {
	/**
	 * Echo the content of the svg file
	 *
	 * @since 0.1.0
	 * @param array $atts Attributes requires to get the icon.
	 * @return void
	 */
	function tsd_the_svg( $atts = array() ) {
		echo tsd_get_svg( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output SVG
	}
}

add_shortcode( 'tsd_get_svg', 'tsd_get_svg_shortcode' );
/**
 * Shortcode to output the svg content
 *
 * @param array $atts The parameter requires to generate the svg content.
 * @return mixed $svg The final svg content.
 */
function tsd_get_svg_shortcode( $atts ) {

	$svg          = '';
	$default_atts = array(
		'icon' => 'error',
		'size' => 32,
	);

	$atts = shortcode_atts( $default_atts, $atts );

	$svg = tsd_get_svg( $atts );

	return $svg;
}
