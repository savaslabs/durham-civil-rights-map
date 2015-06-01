<?php

/**
 * @file
 * Contains \Drupal\beta2beta\PluginOverride\Field\FieldFormatter\TaxonomyFormatter.
 */

namespace Drupal\beta2beta\PluginOverride\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin override for the 'taxonomy_term_reference_plain',
 * 'taxonomy_term_reference_link' and 'taxonomy_term_reference_rss_category'
 * formatters which were removed from core.
 */
class TaxonomyFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    drupal_set_message('The Taxonomy term reference field has been removed in Drupal 8.0.0-beta10. You should run the database update script immediately in order to automatically convert to Entity reference.', 'error');
    return [];
  }

}
