<?php

/**
 * @file
 * Theme & preprocess functions for the Views Slideshow: cycle module.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Asset;

/**
 * Views Slideshow: Theme the main frame wrapper.
 *
 * @ingroup themeable
 */
function template_preprocess_views_slideshow_cycle_main_frame(&$vars) {
  $settings = $vars['settings'];
  $rows = $vars['rows'];
  $view = $vars['view'];
  $vss_id = $vars['vss_id'];

  // Cast the strings into int or bool as necessary.
  $new_settings = array();
  foreach ($settings as $key => $value) {
    if (is_string($value)) {
      $value = str_replace("\n", ' ', $value);

      $value = trim($value);

      if (is_numeric($value)) {
        $value = (int) $value;
      }
      elseif (strtolower($value) == 'true') {
        $value = TRUE;
      }
      elseif (strtolower($value) == 'false') {
        $value = FALSE;
      }
    }

    $new_settings[$key] = $value;
  }

  $settings = array_merge(
    array(
      'num_divs' => count($rows),
      'id_prefix' => '#views_slideshow_cycle_main_',
      'div_prefix' => '#views_slideshow_cycle_div_',
      'vss_id' => $vss_id,
    ),
    $new_settings
  );

  // We need to go through the current js setting values to make sure the one we
  // want to add is not already there. If it is already there then append -[num]
  // to the id to make it unique.
  $slideshow_count = 1;
  global $processedCycles;
  if (is_array($processedCycles)) {
    foreach ($processedCycles as $processedCycle) {
      if (stristr($processedCycle, '#views_slideshow_cycle_main_' . $vss_id)) {
        $slideshow_count++;
      }
    }
  }

  if ($slideshow_count > 1) {
    $vss_id .= '-' . $slideshow_count;
    $settings['vss_id'] = $vss_id;
  }

  // Load the json2 library.
  if (!empty($settings['advanced_options'])) {
    $vars['#attached']['library'][] = 'views_slideshow_cycle/json2';
  }

  // Load the pause library.
  if (!empty($settings['pause_in_middle'])) {
    $vars['#attached']['library'][] = 'views_slideshow_cycle/jquery_pause';
  }

  // Load jQuery Cycle library.
  $vars['#attached']['library'][] = 'views_slideshow_cycle/jquery_cycle';

  // Load our custom cycle library.
  $vars['#attached']['library'][] = 'views_slideshow_cycle/views_slideshow_cycle';
  $vars['#attached']['drupalSettings']['viewsSlideshowCycle']['#views_slideshow_cycle_main_' . $vss_id] = $settings;

  $processedCycles['#views_slideshow_cycle_main_' . $vss_id] = '#views_slideshow_cycle_main_' . $vss_id;

  // Add hover intent library.
  if ($settings['pause']) {
    $vars['#attached']['library'][] = 'views_slideshow/jquery_hoverIntent';
  }

  // Add the slideshow elements.
  $vars['attributes']['class'][] = 'views_slideshow_cycle_teaser_section';

  $vars['items_per_slide'] = $settings['items_per_slide'];

  $items = array();
  $index = 0;
  $vars['rendered_rows'] = '';
  foreach ($rows as $count => $item) {
    $items[] = $item;
    if (count($items) == $settings['items_per_slide'] || $count == (count($rows) - 1)) {
      $vars['rendered_rows'][] = array(
        '#theme' => $vars['view']->buildThemeFunctions('views_slideshow_cycle_main_frame_row'),
        '#vss_id' => $vss_id,
        '#items' => $items,
        '#count' => $index,
        '#view' => $vars['view'],
      );

      $items = array();
      $index++;
    }
  }
}

/**
 * Views Slideshow slideshow rows.
 *
 * @ingroup themeable
 */
function template_preprocess_views_slideshow_cycle_main_frame_row(&$vars) {
  $current = $vars['count'] + 1;
  $vars['attributes']['class'][] = 'views_slideshow_cycle_slide';
  $vars['attributes']['class'][] = 'views_slideshow_slide views-row-' . $current;

  if ($vars['count']) {
    $vars['attributes']['class'][] = 'views_slideshow_cycle_hidden';
  }
  $vars['attributes']['class'][] = ($vars['count'] % 2) ? 'views-row-even' : 'views-row-odd';

  $vars['rendered_items'] = '';
  foreach ($vars['items'] as $item_count => $item) {
    $vars['rendered_items'][] = array(
      '#theme' => $vars['view']->buildThemeFunctions('views_slideshow_cycle_main_frame_row_item'),
      '#item' => $item,
      '#item_count' => $item_count,
      '#count' => $vars['count'],
      '#view' => $vars['view'],
      '#length' => count($vars['view']->result),
    );
  }
}

/**
 * Views Slideshow slideshow row items.
 *
 * @ingroup themeable
 */
function template_preprocess_views_slideshow_cycle_main_frame_row_item(&$vars) {
  $vars['attributes']['class'][] = 'views-row views-row-' . $vars['count'];
  $vars['attributes']['class'][] = ($vars['count'] % 2) ? 'views-row-even' : 'views-row-odd';
  if ($vars['count'] == 0) {
    $vars['attributes']['class'][] = 'views-row-first';
  }
  elseif ($vars['count'] == $vars['length'] - 1) {
    $vars['attributes']['class'][] = 'views-row-last';
  }

  // Support custom row classes.
  if ($row_class = $vars['view']->style_plugin->getRowClass($vars['count'])) {
    $vars['attributes']['class'][] = $row_class;
  }
}

/**
 * Implements hook_preprocess_views_slideshow_pager_fields().
 */
function _views_slideshow_cycle_preprocess_views_slideshow_pager_fields(&$vars) {
  $slide_count = count($vars['view']->result);
  $items_per_slide = $vars['view']->style_plugin->options['views_slideshow_cycle']['items_per_slide'];

  if ($vars['view']->style_plugin->options['slideshow_type'] == 'views_slideshow_cycle') {
    $slide_count = $slide_count / $items_per_slide;
    $slide_count = ceil($slide_count);
  }

  $vars['rendered_field_items'] = '';
  if (empty($vars['settings']['hide_on_single_slide']) || $slide_count > 1) {
    foreach ($vars['view']->result as $count => $node) {
      if ($count < $slide_count) {
        $rendered_fields = '';
        foreach ($vars['settings']['views_slideshow_pager_fields']['views_slideshow_pager_fields_fields'] as $field => $use) {
          if ($use !== 0 && is_object($vars['view']->field[$field])) {
            $rendered_fields[] = array(
              '#theme' => $vars['view']->buildThemeFunctions('views_slideshow_pager_field_field'),
              '#view' => $vars['view'],
              '#label' => $vars['view']->field[$field]->options['label'],
              '#output' => $vars['view']->style_plugin->getField($count, $field),
              '#css_identifier' => Html::cleanCssIdentifier($vars['view']->field[$field]->field),
            );
          }
        }

        $vars['rendered_field_items'][] = array(
          '#theme' => $vars['view']->buildThemeFunctions('views_slideshow_pager_field_item'),
          '#vss_id' => $vars['vss_id'],
          '#item' => $rendered_fields,
          '#count' => $count,
          '#location' => $vars['location'],
        );
      }
    }
  }
}

/**
 * Implements hook_preprocess_views_slideshow_slide_counter().
 */
function _views_slideshow_cycle_preprocess_views_slideshow_slide_counter(&$vars) {
  if ($vars['view']->style_plugin->options['slideshow_type'] == 'views_slideshow_cycle') {
    $vars['slide_count'] = $vars['slide_count'] / $vars['view']->style_plugin->options['views_slideshow_cycle']['items_per_slide'];
    $vars['slide_count'] = ceil($vars['slide_count']);
  }
}
