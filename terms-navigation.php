<?php

function get_terms_navigation($post_type = null, $taxonomy = null, $options = array()) {
  global $wp_query;

  $post_type = $post_type ?: get_post_type();
  $taxonomy = get_object_taxonomies( $post_type )[0];
  $template = $options['template'] ?: 'terms-navigation';
  $format = $options['format'] ?: '';

  $archive_link = get_post_type_archive_link($post_type);

  $terms = get_terms(array(
		'taxonomy' => $taxonomy,
		'hide_empty' => true
	));

  $terms = array_map(function($term) {
    return get_object_vars($term);
  }, $terms);



  $parent_ids_map = array_reduce($terms, function($result, $current) {
    $term_id = $current['term_id'];

    $ids = array();
    while (($current = $current['parent'])) {
      $ids[] = $current;
    }
    $result[$term_id] = $ids;

    return $result;
  }, array());

  $parent_ids = array_reduce($parent_ids_map, function($result, $current) {
    $result = array_merge($result, $current);
    return $result;
  }, []);


  $selected_cat_query_arg = $post_type === 'post' && $taxonomy === 'category' ? 'cat' : $taxonomy;
  $query_value = $wp_query->query[$selected_cat_query_arg] ?: get_query_var($selected_cat_query_arg);

	$selected_term_ids = array_filter(
    $term_ids = array_map(function($category) {
      return trim($category);
    }, explode(',', $query_value) ?: array()),
    function($category) {
  		return strlen($category) > 0;
  	}
  );

  $selected_terms = array_map(function($term_id) use ($terms) {
    $term = array_values(array_filter($terms, function($term) use ($term_id) {
      return $term['term_id'] == $term_id;
    }))[0];

    if ($term) {
      return $term;
    }

    return null;
  }, $selected_term_ids);

  $has_selected_categories = count($selected_term_ids) > 0;

  $selected_sibling_ids = array_reduce($selected_terms, function($result, $current) use ($terms) {
    $parent_id = $current['parent'];

    $sibling_terms = array_values(array_filter($terms, function($term) use ($parent_id) {
      return $term['parent'] == $parent_id;
    }));

    $sibling_ids = array_map(function($term) {
      return $term['term_id'];
    }, $sibling_terms);

    $result = array_merge($result, $sibling_ids);

    return $result;
  }, []);

  $terms = array_map(function($tag) use ($archive_link, $selected_term_ids, $selected_cat_query_arg) {

    $link = $archive_link;
    $link_categories = array();

    $value = $selected_cat_query_arg === 'cat' ? $tag['term_id'] : $tag['slug'];
    $active = in_array($value, $selected_term_ids);

    if ($active) {
      $link_categories = array_filter($selected_term_ids, function($category) use ($value) {
        return $category != $value;
      });

      // $link_categories = array_reduce($link_categories, function($result, $current) {
      //   return array_merge([
      //     $current['term_id']
      //   ], $result, $parent_ids[$current['term_id']]);
      // }, []);
      //
      //
      // print_r($link_categories);


    } else {
      $link_categories = array_merge($selected_term_ids, array($value));
    }

    if (count($link_categories) > 0) {
      $link = add_query_arg($selected_cat_query_arg, implode(',', $link_categories), $link);
    } else {
      $link = remove_query_arg($selected_cat_query_arg, $link);
      $link = $archive_link;
    }

    $link = add_query_arg( $selected_cat_query_arg, implode(',', $link_categories), $link);

    return array_merge($tag, [
      'link' => $link,
      'label' => $tag['name'],
      'value' => $value,
      'active' => $active,
      'selected' => $active
    ]);
  }, $terms);

  $terms = array_map(function($term) use ($active_parent_ids) {
    if (in_array($term['term_id'], $active_parent_ids)) {
      $term['active'] = true;
    }

    return $term;
  }, $terms);



  $active_parent_ids = array_reduce(array_filter($terms, function($term) {
    return $term['active'] && $term['parent'];
  }), function($result, $current) {
    while (($current = $current['parent'])) {
      $result[] = $current;
    }
    return $result;
  }, []);

  $terms = array_map(function($term) use ($active_parent_ids) {
    if (in_array($term['term_id'], $active_parent_ids)) {
      $term['active_parent'] = true;
    }

    return $term;
  }, $terms);


  // if ($query_value) {
  //   echo 'FILTER TERMS' . $query_value;
  //   $terms = array_values(array_filter($terms, function($term) use ($parent_ids, $selected_term_ids, $selected_sibling_ids) {
  //     if (!$term['term_id']['parent']) {
  //       return true;
  //     }
  //     return in_array($term['term_id'], $parent_ids) || in_array($term['term_id'], $selected_term_ids) || in_array($term['term_id'], $selected_sibling_ids);
  //   }));
  // };

  // print_r($terms);

  // foreach ($terms as $term) {
  //   if ($term['name'] === 'Grandchild Category') {
  //     echo 'TERM: ' . $term['name'] . ' <br/>';
  //     echo 'parent: ' . $term['parent'] . ' <br/>';
  //   }
  // }


  $nested_terms = wp_terms_navigation_convert_to_hierarchy($terms);

  $data = [
    'terms' => $nested_terms
  ];


  $flat = wp_terms_navigation_hierarchy_to_flat($nested_terms);
  // print_r($terms);
  $levels = array();

  foreach ($flat as $term) {
    $term_level = $term['level'] ?: 0;

    $levels[$term_level] = $levels[$term_level] ?: [
      'active' => false,
      'terms' => []
    ];
    $levels[$term_level]['terms'][] = $term;

    if ($term['active'] || $term['active_parent']) {
      $levels[$term_level]['active'] = true;
    }
  }


  /*
  echo '<pre><code>';
  echo var_dump($nested_terms);
  echo '</code></pre>';
  */

  $options = array_merge($options, [
    // 'terms' => $terms,
    'levels' => $levels,
    'level' => 0,
    'template' => $template,
    'format' => $format
  ]);

  $output = get_terms_menu($nested_terms, $options);

  return $output;
}


function get_terms_menu($terms = array(), $options) {
  $template = $options['template'] ?: '';
  $format = $options['format'] ?: '';

  $params = array_merge($options, [
    'terms' => $terms,
    // 'levels' => $levels,
    // 'template' => $template,
    // 'format' => $format
    'options' => $options
  ]);

  $output = wp_terms_navigation_render_template($template, $format, $params);

  return $output;
}
