<?php


function wp_terms_navigation_render_template($template, $format = '', $data = array()) {
	$is_absolute_path = $template[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $template) > 0;
	$path_parts = pathinfo($template);
  $template_name = $format ? $path_parts['filename'] . '-' . $format : $path_parts['filename'];
	$template_ext = isset($path_parts['extension']) ? $path_parts['extension'] : 'php';
	$template_base = $template_name . '.' . $template_ext;
	$template_dir = $path_parts['dirname'];

	if (!$is_absolute_path) {
		// Resolve template
		$directories = array(
			get_template_directory(),
      get_stylesheet_directory(),
			realpath(plugin_dir_path( __FILE__ ) . '../templates')
		);

		$template_dir = array_values(array_filter($directories, function($dir) use ($template_base) {
			return file_exists($dir . DIRECTORY_SEPARATOR . $template_base);
		}))[0];
	}

	$template_file = $template_dir . DIRECTORY_SEPARATOR . $template_base;

  foreach($data as $key => $value) {
    $$key = $data[$key];
  }
  ob_start();
  include $template_file;

  $output = ob_get_contents();

  ob_end_clean();
  return $output;
}


function wp_terms_navigation_convert_to_hierarchy($results, $idField='term_id', $parentIdField='parent', $childrenField='children') {
	$hierarchy = array(); // -- Stores the final data
	$itemReferences = array(); // -- temporary array, storing references to all items in a single-dimention
	foreach ( $results as $item ) {
		// $item = get_object_vars($item);
		$id       = $item[$idField];
		$parentId = $item[$parentIdField];
		if (isset($itemReferences[$parentId])) { // parent exists
			$itemReferences[$parentId][$childrenField][$id] = $item; // assign item to parent
			$itemReferences[$id] =& $itemReferences[$parentId][$childrenField][$id]; // reference parent's item in single-dimentional array
		} elseif (!$parentId || !isset($hierarchy[$parentId])) { // -- parent Id empty or does not exist. Add it to the root
			$hierarchy[$id] = $item;
			$itemReferences[$id] =& $hierarchy[$id];
		}
	}
	unset($results, $item, $id, $parentId);
	// -- Run through the root one more time. If any child got added before it's parent, fix it.
	foreach ( $hierarchy as $id => &$item ) {
		$parentId = $item[$parentIdField];
		if ( isset($itemReferences[$parentId] ) ) { // -- parent DOES exist
			$itemReferences[$parentId][$childrenField][$id] = $item; // -- assign it to the parent's list of children
			unset($hierarchy[$id]); // -- remove it from the root of the hierarchy
		}
	}
	unset($itemReferences, $id, $item, $parentId);

	return $hierarchy;
}

/*
function wp_terms_navigation_convert_to_hierarchy($source) {
	$nested = array();


	foreach ( $source as $s ) {
		$s = get_object_vars($source);

		if ( is_null($s['parent']) ) {
			// no parent_id so we put it in the root of the array
			$nested[] = $s;
		}
		else {
			$pid = $s['parent'];
			if ( isset($source[$pid]) ) {
				// If the parent ID exists in the source array
				// we add it to the 'children' array of the parent after initializing it.

				if ( !isset($source[$pid]['children']) ) {
					$source[$pid]['children'] = array();
				}

				$source[$pid]['children'][] = &$s;
			}
		}
	}

	return $nested;
}
*/
/*
function wp_terms_navigation_convert_to_hierarchy (&$list, $parentId = null, $level = 0) {
  $tree = array();
  foreach ($list as $key => $item) {
		$item = get_object_vars($item);

    if ($item['parent'] === $parentId) {
			$item['level'] = $level;
      $item['children'] = wp_terms_navigation_convert_to_hierarchy ($list, $item['term_id'], $level + 1);
      $tree[] = $item;
      unset($list[$key]);
    }
  }
  return $tree;
}*/
