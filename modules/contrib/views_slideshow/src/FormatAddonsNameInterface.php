<?php

/**
 * @file
 * Contains \Drupal\views_slideshow\FormatAddonsNameInterface.
 */

namespace Drupal\views_slideshow;


/**
 * Provides a class for CRUD operations on path aliases.
 */
interface FormatAddonsNameInterface {

  /**
   * Format callback manipulate addons names.
   */
  public function format($subject);
}