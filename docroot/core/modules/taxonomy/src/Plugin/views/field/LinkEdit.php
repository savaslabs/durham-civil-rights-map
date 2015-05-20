<?php

/**
 * @file
 * Definition of Drupal\taxonomy\Plugin\views\field\LinkEdit.
 */

namespace Drupal\taxonomy\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Field handler to present a term edit link.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("term_link_edit")
 */
class LinkEdit extends FieldPluginBase {

  use RedirectDestinationTrait;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->additional_fields['tid'] = 'tid';
    $this->additional_fields['vid'] = 'vid';
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['text'] = array('default' => '');

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Text to display'),
      '#default_value' => $this->options['text'],
    );
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $this->addAdditionalFields();
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // Check there is an actual value, as on a relationship there may not be.
    if ($tid = $this->getValue($values, 'tid')) {
      // Mock a term object for taxonomy_term_access(). Use machine name and
      // vid to ensure compatibility with vid based and machine name based
      // access checks. See http://drupal.org/node/995156
      $term = entity_create('taxonomy_term', array(
        'vid' => $values->{$this->aliases['vid']},
      ));
      if ($term->access('update')) {
        $text = !empty($this->options['text']) ? $this->options['text'] : $this->t('Edit');
        return \Drupal::l($text, new Url('entity.taxonomy.edit_form', ['taxonomy_term' => $tid], array('query' => $this->getDestinationArray())));
      }
    }
  }

}
