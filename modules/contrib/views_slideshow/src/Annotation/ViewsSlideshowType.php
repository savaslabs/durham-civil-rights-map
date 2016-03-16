<?php

/**
 * @file
 * Contains Drupal\views_slideshow\Annotation\ViewsSlideshowType.
 */

namespace Drupal\views_slideshow\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a slideshow type annotation object.
 *
 * @Annotation
 */
class ViewsSlideshowType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the slideshow type.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * A list of actions this slideshow type accepts.
   *
   * @var string[]
   */
  public $accepts;

  /**
   * A list of actions this slideshow type implements.
   *
   * @var string[]
   */
  public $calls;
}
