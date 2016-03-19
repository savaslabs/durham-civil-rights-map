<?php

// TODO: Is this how this should be namespaced?
namespace Drupal\views_geojson\Plugin\views\argument_default;

// TODO: Which need to be included?
use Drupal\views\Plugin\views\argument_default\QueryParameter;
use \Drupal\core\form\FormStateInterface;

// TODO: What should be included in annotations?
/**
 * The BBOX query string argument default handler.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "bboxquery",
 *   title = @Translation("BBox Query"),
 * )
 */
class BBoxQuery extends QueryParameter {
  // TODO: Determine if this is being implemented correctly.
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['argument'] = array('default' => '');
    $options['arg_id'] = array('default' => 'bbox');

    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['info'] = array(
      '#markup' => '<p class="description">Attempt to pull bounding box info
      directly from the query string, bypassing Drupal\'s normal argument
      handling. If the argument does not exist, all values will be shown.</p>',
    );
    $form['arg_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Query argument ID'),
      '#size' => 60,
      '#maxlength' => 64,
      '#default_value' => $this->options['arg_id'] ? $this->options['arg_id'] : t('bbox'),
      '#description' => t('The ID of the query argument.<br />For OpenLayers use <em>bbox</em>, (as in "<em>?bbox=left,bottom,right,top</em>".)'),
    );
  }

  /**
   * Return the default argument.
   */
  public function getArgument() {
    $current_request = $this->view->getRequest();

    if ($current_request->query->has($this->options['arg_id'])) {
      $param = $current_request->query->get($this->options['arg_id']);
      return $param;
    }
    // TODO: What should be returned if arg not present, and empty result option is set or not?
    else {
      // Otherwise, use the fixed fallback value.
      return $this->options['fallback'];
    }
  }

}
