<?php

/**
 * @file
 * Contains Drupal\views_slideshow\Annotation\ViewsSlideshowWidget.
 */

namespace Drupal\views_slideshow\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a widget annotation object.
 *
 * @Annotation
 */
class ViewsSlideshowWidget extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the widget.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The widget type used by the widget.
   *
   * @var string
   */
  public $type;
}
