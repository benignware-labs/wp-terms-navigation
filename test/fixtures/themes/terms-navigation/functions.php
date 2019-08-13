<?php

// Enqueue Scripts
add_action('wp_enqueue_scripts', function() {
  wp_register_style('twentyseventeen-style', get_template_directory_uri(). '/style.css');
  wp_enqueue_style('twentyseventeen-style', get_template_directory_uri(). '/style.css');
  wp_enqueue_style( 'childtheme-style', get_stylesheet_directory_uri().'/style.css', array('twentyseventeen-style') );
});
