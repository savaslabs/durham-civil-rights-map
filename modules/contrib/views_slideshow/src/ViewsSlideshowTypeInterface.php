<?php
/**
 * @file
 * Provides Drupal\views_slideshow\ViewsSlideshowTypeInterface.
 */
namespace Drupal\views_slideshow;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

interface ViewsSlideshowTypeInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {}
