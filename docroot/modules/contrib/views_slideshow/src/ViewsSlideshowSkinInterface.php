<?php
/**
 * @file
 * Contains Drupal\views_slideshow\ViewsSlideshowSkinInterface.
 */

namespace Drupal\views_slideshow;

/**
 * Interface ViewsSlideshowSkinInterface
 * @package Drupal\views_slideshow
 */
interface ViewsSlideshowSkinInterface {

  /**
   * Returns a array of libraries to attach when the skin is used.
   * @return array
   */
  public function getLibraries();

  /**
   * Returns a class to be added to templates.
   * @return string
   */
  public function getClass();

}
