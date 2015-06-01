<?php

/**
 * @file
 * Contains \Drupal\beta2beta\PluginOverride\Field\FieldWidget\TaxonomyWidget.
 */

namespace Drupal\beta2beta\PluginOverride\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin override for the 'taxonomy_autocomplete' widget which was removed from
 * core.
 */
class TaxonomyWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    drupal_set_message('The Taxonomy term reference field has been removed in Drupal 8.0.0-beta10. You should run the database update script immediately in order to automatically convert to Entity reference.', 'error');
    return [];
  }

}
