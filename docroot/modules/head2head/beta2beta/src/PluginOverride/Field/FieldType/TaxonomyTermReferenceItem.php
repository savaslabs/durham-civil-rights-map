<?php

/**
 * @file
 * Contains \Drupal\beta2beta\PluginOverride\Field\FieldType\TaxonomyTermReferenceItem.
 */

namespace Drupal\beta2beta\PluginOverride\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Plugin override for the 'term_reference' field type which has been removed
 * from core.
 */
class TaxonomyTermReferenceItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    throw new \Exception('The Taxonomy term reference field has been removed in Drupal 8.0.0-beta10. You should run the database update script immediately in order to automatically convert to Entity reference.');
  }

}
