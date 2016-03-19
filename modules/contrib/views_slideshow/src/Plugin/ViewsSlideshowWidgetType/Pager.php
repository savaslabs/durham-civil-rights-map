<?php

/**
 * @file
 * Contains Drupal\views_slideshow\ViewsSlideshowWidgetType\Pager.
 */

namespace Drupal\views_slideshow\Plugin\ViewsSlideshowWidgetType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views_slideshow\ViewsSlideshowWidgetTypeBase;

/**
 * Provides a pager widget type.
 *
 * @ViewsSlideshowWidgetType(
 *   id = "views_slideshow_pager",
 *   label = @Translation("Pager"),
 *   accepts = {"transitionBegin" = {"required" = TRUE}, "goToSlide", "previousSlide", "nextSlide"},
 *   calls = {"goToSlide", "pause", "play"}
 * )
 */
class Pager extends ViewsSlideshowWidgetTypeBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $options = parent::defaultConfiguration() + [
      'hide_on_single_slide' => array('default' => 0),
      'type' => array('default' => 0),
      'views_slideshow_pager_numbered_hover' => array('default' => 0),
      'views_slideshow_pager_numbered_click_to_page' => array('default' => 0),
      'views_slideshow_pager_thumbnails_hover' => array('default' => 0),
      'views_slideshow_pager_thumbnails_click_to_page' => array('default' => 0),
    ];

    /** @var \Drupal\Component\Plugin\PluginManagerInterface */
    $widgetManager = \Drupal::service('plugin.manager.views_slideshow.widget');

    // Get default configuration of all Pager plugins.
    foreach ($widgetManager->getDefinitions($this->getPluginId()) as $widget_id => $widget_info) {
      $options += $widgetManager->createInstance($widget_id, [])->defaultConfiguration();
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $view = $form_state->get('view')->get('executable');

    /** @var \Drupal\Component\Plugin\PluginManagerInterface */
    $widgetManager = \Drupal::service('plugin.manager.views_slideshow.widget');

    // Determine if this widget type is compatible with any slideshow type.
    $widgets = [];
    foreach ($widgetManager->getDefinitions($this->getPluginId()) as $widget_id => $widget_info) {
      if ($widgetManager->createInstance($widget_id, [])->checkCompatiblity($view)) {
        $widgets[$widget_id] = $widget_info['label'];
      }
    }

    if (!empty($widgets)) {

      // Need to wrap this so it indents correctly.
      $form['views_slideshow_pager_wrapper'] = array(
        '#markup' => '<div class="vs-dependent">',
      );

      // Add field to see if they would like to hide pager if there is only one
      // slide.
      $form['hide_on_single_slide'] = array(
        '#type' => 'checkbox',
        '#title' => t('Hide pager if there is only one slide'),
        '#default_value' => $this->getConfiguration()['hide_on_single_slide'],
        '#description' => t('Should the pager be hidden if there is only one slide.'),
        '#states' => array(
          'visible' => array(
            ':input[name="' . $this->getConfiguration()['dependency'] . '[enable]"]' => array('checked' => TRUE),
          ),
        ),
      );

      // Create the widget type field.
      $form['type'] = array(
        '#type' => 'select',
        '#title' => t('Pager Type'),
        '#description' => t('Style of the pager'),
        '#default_value' => $this->getConfiguration()['type'],
        '#options' => $widgets,
        '#states' => array(
          'visible' => array(
            ':input[name="' . $this->getConfiguration()['dependency'] . '[enable]"]' => array('checked' => TRUE),
          ),
        ),
      );

      foreach ($widgetManager->getDefinitions() as $widget_id => $widget_info) {
        // Get the current configuration of this widget.
        $configuration = [];
        if (!empty($this->getConfiguration()[$widget_id])) {
          $configuration = $this->getConfiguration()[$widget_id];
        }
        $configuration['dependency'] = $this->getConfiguration()['dependency'];
        $configuration['view'] = $view;
        $instance = $widgetManager->createInstance($widget_id, $configuration);

        // Get the configuration form of this widget type.
        $form[$widget_id] = isset($form[$widget_id]) ? $form[$widget_id] : [];
        $form[$widget_id] = $instance->buildConfigurationForm($form[$widget_id], $form_state);
      }

      $form['views_slideshow_pager_wrapper_close'] = array(
        '#markup' => '</div>',
      );
    }
    else {
      $form['enable_pager'] = array(
        '#markup' => 'There are no pagers available.',
      );
    }

    return $form;
  }
}