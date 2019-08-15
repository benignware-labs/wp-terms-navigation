<?php

add_action( 'init', function() {
  // Register custom taxonomy
  register_taxonomy( 'department', 'employee', array(
    'hierarchical' => true,
    'label' => __('Department'),
    'labels' => array(
      'name' => __('Departments'),
      'singular_name' => __('Department')
    ),
    'query_var' => 'department',
    'rewrite' => array('slug' => 'department')
  ));

  // Register custom post type
  register_post_type( 'employee',
    array(
      'labels' => array(
        'name' => __('Employees'),
        'singular_name' => __('Employee')
      ),
      'has_archive' => true,
      'supports' => array( 'title', 'thumbnail', 'editor', 'page-attributes', 'excerpt' ),
      'public' => true,
      'taxonomies' => array('department')
    )
  );

});
