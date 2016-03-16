<?php

/**
 * @file
 * Contains Drupal\views_slideshow\Annotation\ViewsSlideshowWidgetType.
 */

namespace Drupal\views_slideshow\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a widget type annotation object.
 *
 * @Annotation
 */
class ViewsSlideshowWidgetType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the widget type.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * A list of actions this widget type accepts.
   *
   * @var array
   */
  public $accepts;

  /**
   * A list of actions this widget type implements.
   *
   * @var array
   */
  public $calls;
}
