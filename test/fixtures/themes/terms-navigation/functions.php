<?php

require_once 'post-types/employee.php';

// Enqueue Scripts
add_action('wp_enqueue_scripts', function() {
  wp_register_style('twentyseventeen-style', get_template_directory_uri(). '/style.css');
  wp_enqueue_style('twentyseventeen-style', get_template_directory_uri(). '/style.css');
  wp_enqueue_style( 'childtheme-style', get_stylesheet_directory_uri().'/style.css', array('twentyseventeen-style') );


  wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.css');

  wp_enqueue_style('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
  wp_enqueue_script('popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ), '', true);
  wp_enqueue_script('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array( 'jquery' ), '', true);
});
