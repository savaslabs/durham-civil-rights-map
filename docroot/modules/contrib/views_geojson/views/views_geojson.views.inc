<?php

/**
 * @file
 * Views plugin definition.
 */

/**
 * Implements hook_views_data().
 *
 * Adds bounding box contextual filter.
 */
function views_geojson_views_data() {
  $data = array();

  $data['views']['bbox_argument'] = array(
    'group' => t('Custom'),
    'real field' => 'bbox_argument',
    'title' => t('Bounding box'),
    'help' => t('Filter locations within a bounding box.'),
    'argument' => array(
      'handler' => 'views_geojson_bbox_argument',
    ),
  );

  return $data;
}
