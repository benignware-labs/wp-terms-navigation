<?php

function get_terms_navigation($post_type = null, $taxonomy = null, $options = array()) {
  global $wp_query;
  global $wp;

  $post_type = $post_type ?: get_post_type();
  $taxonomy = $taxonomy ?: get_object_taxonomies( $post_type )[0];
  $template = $options['template'] ?: 'terms-navigation';
  $format = $options['format'] ?: '';

  $selected_cat_query_arg = $post_type === 'post' && $taxonomy === 'category' ? 'cat' : $taxonomy;
  $query_value = $wp_query->query[$selected_cat_query_arg] ?: get_query_var($selected_cat_query_arg);

  $archive_base = get_post_type_archive_link($post_type);
  $query_string = http_build_query($_GET);
  $archive_link = $archive_base . ( $query_string ? '?' . $query_string : '');
  $archive_link = remove_query_arg($selected_cat_query_arg, $archive_link);

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

  $descendants_ids_map = array_reduce($terms, function($result, $current) use ($terms, $parent_ids_map) {
    $term_id = $current['term_id'];

    $descendant_terms = array_values(array_filter($terms, function($term) use ($term_id, $parent_ids_map) {
      $parent_ids = $parent_ids_map[$term['term_id']];
      $is_ancestor = in_array($term_id, $parent_ids);
      return $is_ancestor;
    }));

    $descendant_ids = array_map(function($term) {
      return $term['term_id'];
    }, $descendant_terms);

    $result[$term_id] = $descendant_ids;

    return $result;
  }, array());

  $parent_ids = array_reduce($parent_ids_map, function($result, $current) {
    $result = array_merge($result, $current);
    return $result;
  }, []);

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

  $terms = array_map(function($term) use ($archive_link, $selected_term_values, $selected_cat_query_arg) {
    $value = $selected_cat_query_arg === 'cat' ? $term['term_id'] : $term['slug'];
    $active = in_array($value, $selected_term_values);

    return array_merge($term, [
      'label' => $term['name'],
      'value' => $value,
      'active' => $active,
      'selected' => $active
    ]);
  }, $terms);

  $terms = array_map(function($term) use ($terms, $archive_link, $selected_cat_query_arg, $selected_term_ids, $parent_ids_map, $descendants_ids_map) {
    $term_id = $term['term_id'];
    $active = $term['active'];
    $value = $term['value'];

    $parent_ids = $parent_ids_map[$term_id];
    $descendant_ids = $descendants_ids_map[$term_id];
    $excluded_term_ids = array_merge($descendant_ids, $parent_ids);

    $link = $archive_link;

    $link_ids = array_values(array_filter($selected_term_ids, function($term_id) use ($excluded_term_ids) {
      return !in_array($term_id, $excluded_term_ids);
    }));

    if (!$active) {
      $link_ids[] = $term_id;
    } else {
      $link_ids = array_values(array_filter($link_ids, function($link_id) use ($term_id) {
        return $link_id != $term_id;
      }));
    }

    $link_values = array_map(function($link_id) use ($terms) {
      $term = array_values(array_filter($terms, function($term) use ($link_id) {
        return $term['term_id'] == $link_id;
      }))[0];

      return $term['value'];
    }, $link_ids);

    if (count($link_values) > 0) {
      $link = add_query_arg($selected_cat_query_arg, implode(',', $link_values), $link);
    } else {
      $link = $archive_link;
    }

    return array_merge($term, [
      'link' => $link,
      'link_values' => $link_values
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
      $link_values = array_unique(array_reduce($group['terms'], function($result, $current) {
        return array_merge($result, $current['link_values']);
      }, []));
      $link = $archive_link;

      if (count($link_values) > 0) {
        $link = add_query_arg($selected_cat_query_arg, implode(',', $link_values), $link);
      } else {
        $link = $archive_link;
      }

      $link = add_query_arg($selected_cat_query_arg, implode(',', $link_values), $link);

      return array_merge($group, [
        'link' => $link,
        'grouped' => count($values) > 1
      ]);
    }, array_values($groups));
  }

  if ($query_value) {

    $term_ids = array_reduce($terms, function($result, $term) use ($terms, $selected_term_ids, $parent_ids_map) {
      $term_id = $term['term_id'];

      if (in_array($term_id, $selected_term_ids)) {
        // Get siblings
        $parent_id = $term['parent'];
        $siblings = array_values(array_filter($terms, function($term) use ($parent_id) {
          return $term['parent'] == $parent_id;
        }));
        $sibling_ids = array_map(function($term) {
          return $term['term_id'];
        }, $siblings);
        // Get children
        $children = array_values(array_filter($terms, function($term) use ($term_id) {
          return $term['parent'] == $term_id;
        }));

        $children_ids = array_map(function($term) {
          return $term['term_id'];
        }, $children);

        $parent_ids = $parent_ids_map[$term_id];

        $result[] = $term_id;
        $result = array_merge($result, $children_ids);
        $result = array_merge($result, $sibling_ids);
        $result = array_merge($result, $parent_ids);
      } else if (!$term['parent']) {
        $result[] = $term_id;
      }
      return $result;
    }, []);

    $term_ids = array_unique($term_ids);
    $terms = array_values(array_filter($terms, function($term) use ($term_ids) {
      return in_array($term['term_id'], $term_ids);
    }));
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
