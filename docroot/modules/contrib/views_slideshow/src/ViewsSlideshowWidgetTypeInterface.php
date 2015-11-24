<?php
/**
 * @file
 * Provides Drupal\views_slideshow\ViewsSlideshowWidgetTypeInterface.
 */

namespace Drupal\views_slideshow;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

interface ViewsSlideshowWidgetTypeInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Check if the widget type is compatible with the selected slideshow.
   * @return bool
   */
  public function checkCompatiblity($slideshow);
}
