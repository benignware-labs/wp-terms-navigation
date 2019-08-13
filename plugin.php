<?php

/**
 * Plugin Name: Terms Navigation
 * Plugin URI: https://github.com/benignware-labs/wp-terms-navigation
 * Description: Hierarchical filter navigation
 * Author: Rafael Nowrotek
 * Author URI: http://benignware.com/
 * Version: 0.0.1
 *
 */

require_once plugin_dir_path( __FILE__ ) . 'lib/utils.php';
require_once plugin_dir_path( __FILE__ ) . 'terms-navigation.php';

add_action('wp_enqueue_scripts', function() {
  wp_enqueue_script( 'terms-navigation-js', plugin_dir_url( __FILE__ ) . '/dist/terms-navigation.js');
  wp_enqueue_script( 'mdn-polyfills-closest-js', plugin_dir_url( __FILE__ ) . '/dist/polyfill/Element.prototype.closest.js');
  wp_enqueue_script( 'mdn-polyfills-classlist-js', plugin_dir_url( __FILE__ ) . '/dist/polyfill/Element.prototype.classList.js');
  wp_enqueue_style( 'terms-navigation-css', plugin_dir_url( __FILE__ ) . '/dist/terms-navigation.css');
}, 10);
