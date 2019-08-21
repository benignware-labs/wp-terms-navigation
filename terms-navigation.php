<?php

function get_terms_navigation($post_type = null, $taxonomy = null, $options = array()) {
  global $wp_query;
  global $wp;

  $post_type = $post_type ?: get_post_type();
  $taxonomy = $taxonomy ?: get_object_taxonomies( $post_type )[0];
  $template = $options['template'] ?: 'terms-navigation';
  $format = $options['format'] ?: '';

  $archive_base = get_post_type_archive_link($post_type);
  // $archive_base = add_query_arg( array(), $wp->request );
  $query_string = http_build_query($_GET);
  $archive_link = $archive_base . ( $query_string ? '?' . $query_string : '');

  $terms = get_terms(array(
		'taxonomy' => $taxonomy,
		'hide_empty' => true
	));

  $terms = array_map(function($term) {
    return get_object_vars($term);
  }, $terms);

  $parent_ids_map = array_reduce($terms, function($result, $current) use ($terms) {
    $term_id = $current['term_id'];
    $ids = array();

    while ($current && ($parent_id = $current['parent'])) {
      $ids[] = $parent_id;

      $current = array_values(array_filter($terms, function($term) use ($parent_id) {
        return $term['term_id'] == $parent_id;
      }))[0];
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

	$selected_term_values = array_filter(
    $term_ids = array_map(function($category) {
      return trim($category);
    }, explode(',', $query_value) ?: array()),
    function($category) {
  		return strlen($category) > 0;
  	}
  );

  $selected_term_ids = array_map(function($slug) use ($terms) {
    return array_values(array_filter($terms, function($term) use ($slug) {
      return $term['slug'] == $slug;
    }))[0]['term_id'];
  }, $selected_term_values);

  $selected_branches = array_reduce($selected_term_ids, function($result, $term_id) use ($parent_ids_map, $selected_term_ids) {
    $parent_ids = $parent_ids_map[$term_id];

    $selected_parent_ids = array_filter($parent_ids, function($term_id) use ($selected_term_ids) {
      return in_array($term_id, $selected_term_ids);
    });

    $branch = array_merge([ $term_id ], $selected_parent_ids);

    $result[] = $branch;

    return $result;
  }, []);

  // usort($selected_branches, function($a, $b) {
  //   return count($b) - count($a);
  // });
  //
  // $c = 0;
  // $filtered_selected_branches = array_reduce($selected_branches, function($result, $branch) use (&$c, $selected_branches) {
  //   $c++;
  //   $other_branches = array_slice($selected_branches, $c);
  //
  //   echo $c;
  //
  //   $is_unique = count(array_values(array_filter($other_branches, function($other_branch) use ($branch) {
  //     return array_filter($other_branch, function($term_id) use ($branch) {
  //       return in_array($term_id, $branch);
  //     });
  //   }))) > 0;
  //
  //   if ($is_unique) {
  //     $result[] = $branch;
  //   }
  //
  //   return $result;
  // });
  //
  // print_r($filtered_selected_branches);
  // exit;

  // Exclude parents of selected terms
  $excluded_parent_ids = array_reduce($selected_term_ids, function($result, $term_id) use ($parent_ids_map) {
    return array_merge($result, $parent_ids_map[$term_id]);
  }, []);

  $selected_term_ids = array_values(array_filter($selected_term_ids, function($term_id) use ($excluded_parent_ids) {
    return !in_array($term_id, $excluded_parent_ids);
  }));

  $selected_terms = array_map(function($term_id) use ($terms) {
    return array_values(array_filter($terms, function($term) use ($term_id) {
      return $term['term_id'] == $term_id;
    }))[0];
  }, $selected_term_ids);

  $selected_term_values = array_map(function($term) use ($selected_cat_query_arg) {
    return $selected_cat_query_arg === 'cat' ? $term['term_id'] : $term['slug'];
  }, $selected_terms);

  $has_selected_categories = count($selected_term_values) > 0;

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

  $terms = array_map(function($tag) use ($archive_link, $selected_term_values, $selected_cat_query_arg) {
    $link = $archive_link;
    $link_categories = array();

    $value = $selected_cat_query_arg === 'cat' ? $tag['term_id'] : $tag['slug'];
    $active = in_array($value, $selected_term_values);

    if ($active) {
      $link_categories = array_filter($selected_term_values, function($category) use ($value) {
        return $category != $value;
      });
    } else {
      $link_categories = array_merge($selected_term_values, array($value));
    }

    if (count($link_categories) > 0) {
      $link = add_query_arg($selected_cat_query_arg, implode(',', $link_categories), $link);
    } else {
      $link = remove_query_arg($selected_cat_query_arg, $link);
      $link = $archive_link;
    }

    $link = add_query_arg($selected_cat_query_arg, implode(',', $link_categories), $link);

    return array_merge($tag, [
      'link' => $link,
      'label' => $tag['name'],
      'value' => $value,
      'active' => $active,
      'selected' => $active
    ]);
  }, $terms);

  $active_terms = array_values(array_filter($terms, function($term) {
    return $term['active'];
  }));

  $active_term_ids = array_map(function($term) {
    return $term['term_id'];
  }, $active_terms);

  $active_parent_ids = array_unique(array_reduce($active_terms, function($result, $current) use ($parent_ids_map) {
    $term_id = $current['term_id'];
    $parent_ids = $parent_ids_map[$term_id];
    $result = array_merge($result, $parent_ids);

    return $result;
  }, []));

  $terms = array_map(function($term) use ($active_parent_ids) {
    if (in_array($term['term_id'], $active_parent_ids)) {
      $term['active_parent'] = true;
    }

    return $term;
  }, $terms);

  $active_ids = array_merge($active_parent_ids, $active_term_ids);

  if ($query_value) {
    $terms = array_values(array_filter($terms, function($term) use ($parent_ids_map, $active_ids) {
      if (!$term['parent']) {
        return true;
      }

      $parent_ids = $parent_ids_map[$term['term_id']];

      $in_branch = count(array_values(array_filter($parent_ids, function($term_id) use ($active_ids) {
        return in_array($term_id, $active_ids);
      }))) > 0;

      if ($in_branch) {
        return true;
      }

      return false;
    }));
  }

  // Grouping
  $is_grouped = true;

  if ($is_grouped) {
    $groups = array_reduce($terms, function($result, $current) {
      $label = $current['label'];
      $result[$label] = isset($result[$label]) ? $result[$label] : array_merge($current, [
        'terms' => [],
        'values' => [],
        'active' => false,
        'active_parent' => false
      ]);
      $result[$label]['terms'][] = $current;
      $result[$label]['values'][] = $current['value'];
      $result[$label]['active'] = $current['active'] ? true : $result[$label]['active'];
      $result[$label]['active_parent'] = $current['active_parent'] ? true : $result[$label]['active_parent'];

      return $result;
    }, []);

    $terms = array_map(function($group) use ($selected_cat_query_arg, $archive_link, $selected_term_values) {
      $values = $group['values'];
      $active = $group['active'];
      $link = $archive_link;

      if (!$active) {
        $link_values = array_merge($selected_term_values, $values);
      } else {
        $link_values = array_values(array_filter($selected_term_values, function($term_value) use ($values) {
          return !in_array($term_value, $values);
        }));
      }

      $link = add_query_arg($selected_cat_query_arg, implode(',', $link_values), $link);

      return array_merge($group, [
        'link' => $link,
        'grouped' => count($values) > 1
      ]);
    }, array_values($groups));
  }

  $nested_terms = wp_terms_navigation_convert_to_hierarchy($terms);

  $data = [
    'terms' => $nested_terms
  ];


  $flat = wp_terms_navigation_hierarchy_to_flat($nested_terms);
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

  $options = array_merge($options, [
    // 'terms' => $terms,
    'levels' => $levels,
    'level' => 0,
    'template' => $template,
    'format' => $format,
    'post_type' => $post_type,
    'taxonomy' => $taxonomy
  ]);

  $output = get_terms_menu($nested_terms, $options);

  return $output;
}


function get_terms_menu($terms = array(), $options = array()) {
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
