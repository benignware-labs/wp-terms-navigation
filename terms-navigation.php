<?php

function get_terms_navigation($post_type = null, $taxonomy = null, $options = array()) {
  global $wp_query;

  $post_type = $post_type ?: get_post_type();
  $taxonomy = get_object_taxonomies( $post_type )[0];
  $template = $options['template'] ?: 'terms-navigation';

  $archive_link = get_post_type_archive_link($post_type);

  $terms = get_terms(array(
		'taxonomy' => $taxonomy,
		'hide_empty' => true
	));

  $selected_cat_query_arg = $post_type === 'post' && $taxonomy === 'category' ? 'cat' : $taxonomy;
  $query_value = $wp_query->query[$selected_cat_query_arg] ?: get_query_var($selected_cat_query_arg);

	$selected_categories = array_filter(
    array_map(function($category) {
  		return trim($category);
  	}, explode(',', $query_value) ?: array()),
    function($category) {
  		return strlen($category) > 0;
  	}
  );

  $has_selected_categories = count($selected_categories) > 0;

  $terms = array_map(function($tag) use ($archive_link, $selected_categories, $selected_cat_query_arg) {

    $link = $archive_link;
    $link_categories = array();

    $value = $selected_cat_query_arg === 'cat' ? $tag->term_id : $tag->slug;
    $active = in_array($value, $selected_categories);

    if ($active) {
      $link_categories = array_filter($selected_categories, function($category) use ($value) {
        return $category != $value;
      });
    } else {
      $link_categories = array_merge($selected_categories, array($value));
    }

    if (count($link_categories) > 0) {
      $link = add_query_arg($selected_cat_query_arg, implode(',', $link_categories), $link);
    } else {
      $link = remove_query_arg($selected_cat_query_arg, $link);
      $link = $archive_link;
    }

    $link = add_query_arg( $selected_cat_query_arg, implode(',', $link_categories), $link);

    return array_merge(get_object_vars($tag), [
      'link' => $link,
      'label' => $tag->name,
      'value' => $value,
      'active' => $active,
      'active_parent' => false
    ]);
  }, $terms);

  $nested_terms = wp_terms_navigation_convert_to_hierarchy($terms);

  $data = [
    'terms' => $nested_terms
  ];

  /*
  echo '<pre><code>';
  echo var_dump($nested_terms);
  echo '</code></pre>';
  */

  $output = get_terms_menu($nested_terms, $template);

  return $output;
}


function get_terms_menu($terms = array(), $template = null, $format = '') {
  $output = wp_terms_navigation_render_template($template, $format, [
    'terms' => $terms,
    'template' => $template,
    'format' => $format
  ]);

  return $output;
}
