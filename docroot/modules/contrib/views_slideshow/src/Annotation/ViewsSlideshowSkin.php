<?php

/**
 * @file
 * Contains Drupal\views_slideshow\Annotation\ViewsSlideshowSkin.
 */

namespace Drupal\views_slideshow\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a slideshow skin annotation object.
 *
 * @Annotation
 */
class ViewsSlideshowSkin extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the slideshow skin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * A list of libraries this slideshow skin needs to attach.
   *
   * @var string[]
   */
  public $libraries;

}
