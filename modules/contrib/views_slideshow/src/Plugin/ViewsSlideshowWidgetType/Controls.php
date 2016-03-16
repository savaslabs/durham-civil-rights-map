<?php

/**
 * @file
 * Contains Drupal\views_slideshow\ViewsSlideshowWidgetType\Controls.
 */

namespace Drupal\views_slideshow\Plugin\ViewsSlideshowWidgetType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views_slideshow\ViewsSlideshowWidgetTypeBase;

/**
 * Provides a control widget type.
 *
 * @ViewsSlideshowWidgetType(
 *   id = "views_slideshow_controls",
 *   label = @Translation("Controls"),
 *   accepts = {"pause" = {"required" = TRUE}, "play" = {"required" = TRUE}},
 *   calls = {"nextSlide", "pause", "play", "previousSlide"}
 * )
 */
class Controls extends ViewsSlideshowWidgetTypeBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'hide_on_single_slide' => array('default' => 0),
      'type' => array('default' => 0),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface */
    $widgetManager = \Drupal::service('plugin.manager.views_slideshow.widget');

    $widgets = $widgetManager->getDefinitions($this->getPluginId());

    if (!empty($widgets)) {
      $options = array();
      foreach ($widgets as $widgetId => $widgetInfo) {
        $options[$widgetId] = $widgetInfo['label'];
      }

      // Need to wrap this so it indents correctly.
      $form['views_slideshow_controls_wrapper'] = array(
        '#markup' => '<div class="vs-dependent">',
      );

      // Add field to see if they would like to hide controls if there is only one
      // slide.
      $form['hide_on_single_slide'] = array(
        '#type' => 'checkbox',
        '#title' => t('Hide controls if there is only one slide'),
        '#default_value' => $this->getConfiguration()['hide_on_single_slide'],
        '#description' => t('Should the controls be hidden if there is only one slide.'),
        '#states' => array(
          'visible' => array(
            ':input[name="' . $this->getConfiguration()['dependency'] . '[enable]"]' => array('checked' => TRUE),
          ),
        ),
      );

      // Create the widget type field.
      $form['type'] = array(
        '#type' => 'select',
        '#title' => t('Controls Type'),
        '#description' => t('Style of the controls'),
        '#default_value' => $this->getConfiguration()['type'],
        '#options' => $options,
        '#states' => array(
          'visible' => array(
            ':input[name="' . $this->getConfiguration()['dependency'] . '[enable]"]' => array('checked' => TRUE),
          ),
        ),
      );

      foreach ($widgets as $widget_id => $widget_info) {
        // Get the current configuration of this widget.
        $configuration = [];
        if (!empty($this->getConfiguration()[$widget_id])) {
          $configuration = $this->getConfiguration()[$widget_id];
        }
        $configuration['dependency'] = $this->getConfiguration()['dependency'];
        $instance = $widgetManager->createInstance($widget_id, $configuration);

        // Get the configuration form of this widget type.
        $form[$widget_id] = isset($form[$widget_id]) ? $form[$widget_id] : [];
        $form[$widget_id] = $instance->buildConfigurationForm($form[$widget_id], $form_state);
      }

      $form['controls_wrapper_close'] = array(
        '#markup' => '</div>',
      );
    }
    else {
      $form['enable_controls'] = array(
        '#markup' => 'There are no controls available.',
      );
    }

    return $form;
  }
}