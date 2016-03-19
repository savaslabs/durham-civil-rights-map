<?php
/**
 * @file
 * Provides Drupal\views_slideshow\ViewsSlideshowWidgetInterface.
 */

namespace Drupal\views_slideshow;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

interface ViewsSlideshowWidgetInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Check if the widget is compatible with the current view configuration.
   * @return bool
   */
  public function checkCompatiblity($view);
}
