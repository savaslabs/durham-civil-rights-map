<?php

/**
 * @file
 * Contains \Drupal\views_slideshow\Plugin\views\style\Slideshow.
 */

namespace Drupal\views_slideshow\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Style plugin to render each item in a slideshow.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "slideshow",
 *   title = @Translation("Slideshow"),
 *   help = @Translation("Display the results as a slideshow."),
 *   theme = "views_view_slideshow",
 *   display_types = {"normal"}
 * )
 */
class Slideshow extends StylePluginBase {

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  /**
   * Does the style plugin support grouping of rows.
   *
   * @var bool
   */
  protected $usesGrouping = FALSE;

  /**
   * Does the style plugin for itself support to add fields to it's output.
   *
   * This option only makes sense on style plugins without row plugins, like
   * for example table.
   *
   * @var bool
   */
  protected $usesFields = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['row_class_custom'] = array('default' => '');
    $options['row_class_default'] = array('default' => TRUE);
    $options['slideshow_type'] = array('default' => 'views_slideshow_cycle');
    $options['slideshow_skin'] = array('default' => 'default');

    $typeManager = \Drupal::service('plugin.manager.views_slideshow.slideshow_type');
    foreach ($typeManager->getDefinitions() as $id => $definition) {
      $instance = $typeManager->createInstance($id, []);
      $options[$id] = $instance->defaultConfiguration();
    }

    $widgetTypeManager = \Drupal::service('plugin.manager.views_slideshow.widget_type');
    $widgetTypes = $widgetTypeManager->getDefinitions();
    foreach (array('top', 'bottom') as $location) {
      foreach ($widgetTypes as $widgetTypeId => $widgetTypeDefinition) {
        $options['widgets']['contains'][$location]['contains'][$widgetTypeId]['contains'] = $widgetTypeManager->createInstance($widgetTypeId, [])->defaultConfiguration();
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Wrap all the form elements to help style the form.
    $form['views_slideshow_wrapper'] = array(
      '#markup' => '<div id="views-slideshow-form-wrapper">',
    );

    // Skins.
    $form['slideshow_skin_header'] = array(
      '#markup' => '<h2>' . t('Style') . '</h2>',
    );

    /** @var \Drupal\Component\Plugin\PluginManagerInterface */
    $skinManager = \Drupal::service('plugin.manager.views_slideshow.slideshow_skin');

    // Get all skins to create the option list.
    $skins = [];
    foreach ($skinManager->getDefinitions() as $id => $definition) {
      $skins[$id] = $definition['label'];
    }
    asort($skins);

    // Create the drop down box so users can choose an available skin.
    $form['slideshow_skin'] = array(
      '#type' => 'select',
      '#title' => t('Skin'),
      '#options' => $skins,
      '#default_value' => $this->options['slideshow_skin'],
      '#description' => t('Select the skin to use for this display.  Skins allow for easily swappable layouts of things like next/prev links and thumbnails.  Note that not all skins support all widgets, so a combination of skins and widgets may lead to unpredictable results in layout.'),
    );

    // Slides.
    $form['slides_header'] = array(
      '#markup' => '<h2>' . t('Slides') . '</h2>',
    );

    // Get all slideshow types.
    $typeManager = \Drupal::service('plugin.manager.views_slideshow.slideshow_type');
    $types = $typeManager->getDefinitions();

    if ($types) {

      // Build our slideshow options for the form.
      $slideshow_options = array();
      foreach ($types as $id => $definition) {
        $slideshow_options[$id] = $definition['label'];
      }

      $form['slideshow_type'] = array(
        '#type' => 'select',
        '#title' => t('Slideshow Type'),
        '#options' => $slideshow_options,
        '#default_value' => $this->options['slideshow_type'],
      );

      // @todo: check if default values are properly passed to the buildConfigurationForm().
      foreach ($types as $id => $definition) {
        $configuration = [];
        if (!empty($this->options[$id])) {
          $configuration = $this->options[$id];
        }
        $instance = $typeManager->createInstance($id, $configuration);

        $form[$id] = array(
          '#type' => 'fieldset',
          '#title' => t('@module options', array('@module' => $definition['label'])),
          '#collapsible' => TRUE,
          '#attributes' => array('class' => array($id)),
          '#states' => array(
            'visible' => array(
              ':input[name="style_options[slideshow_type]"]' => array('value' => $id),
            ),
          ),
        );

        $form = $instance->buildConfigurationForm($form, $form_state);
      }
    }
    else {
      $form['enable_module'] = array(
        '#markup' => t('There is no Views Slideshow plugin enabled. Go to the @modules and enable a Views Slideshow plugin module. For example Views Slideshow Singleframe.', array('@modules' => Link::fromTextAndUrl(t('Modules Page'), Url::fromRoute('system.modules_list')))),
      );
    }

    // Widgets.
    // @todo: Improve the UX by using Ajax.
    $form['widgets_header'] = array(
      '#markup' => '<h2>' . t('Widgets') . '</h2>',
    );

    // Define the available locations.
    $location = array('top' => t('Top'), 'bottom' => t('Bottom'));

    // Loop through all locations so we can add header for each location.
    foreach ($location as $location_id => $location_name) {
      $form['widgets'][$location_id]['header'] = array(
        '#markup' => '<h3>' . t('@location Widgets', array('@location' => $location_name)) . '</h3>',
      );
    }

    /** @var \Drupal\Component\Plugin\PluginManagerInterface */
    $widgetTypeManager = \Drupal::service('plugin.manager.views_slideshow.widget_type');

    // Get all widgets types that are registered.
    $widgets = $widgetTypeManager->getDefinitions();
    if (!empty($widgets)) {

      // Build our weight values by number of widgets
      $weights = [];
      for ($i = 1; $i <= count($widgets); $i++) {
        $weights[$i] = $i;
      }

      // Loop through our widgets and locations to build our form values for
      // each widget.
      foreach ($widgets as $widget_id => $widget_info) {

        // Determine if this widget type is compatible with any slideshow type.
        $compatible_slideshows = [];
        foreach ($types as $slideshow_id => $slideshow_info) {
          if ($widgetTypeManager->createInstance($widget_id, [])->checkCompatiblity($slideshow_info)) {
            $compatible_slideshows[] = $slideshow_id;
          }
        }

        // Display the widget config form only if the widget type is compatible
        // with at least one slideshow type.
        if (!empty($compatible_slideshows)) {
          foreach ($location as $location_id => $location_name) {
            // Use Widget Checkbox.
            $form['widgets'][$location_id][$widget_id]['enable'] = array(
              '#type' => 'checkbox',
              '#title' => $widget_info['label'],
              '#default_value' => $this->options['widgets'][$location_id][$widget_id]['enable'],
              '#description' => t('Should @name be rendered at the @location of the slides.', array('@name' => $widget_info['label'], '@location' => $location_name)),
              '#dependency' => array(
                'edit-style-options-slideshow-type' => $compatible_slideshows,
              ),
            );

            // Need to wrap this so it indents correctly.
            $form['widgets'][$location_id][$widget_id]['wrapper'] = [
              '#markup' => '<div class="vs-dependent">',
            ];

            // Widget weight.
            // We check to see if the default value is greater than the number
            // of widgets just in case a widget has been removed and the form
            // hasn't been saved again.
            $weight = (isset($this->options['widgets'][$location_id][$widget_id]['weight'])) ? $this->options['widgets'][$location_id][$widget_id]['weight'] : 0;
            if ($weight > count($widgets)) {
              $weight = count($widgets);
            }
            $form['widgets'][$location_id][$widget_id]['weight'] = [
              '#type' => 'select',
              '#title' => t('Weight of the @name', ['@name' => $widget_info['label']]),
              '#default_value' => $weight,
              '#options' => $weights,
              '#description' => t('Determines in what order the @name appears. A lower weight will cause the @name to be above higher weight items.', ['@name' => $widget_info['label']]),
              '#prefix' => '<div class="vs-dependent">',
              '#suffix' => '</div>',
              '#states' => [
                'visible' => [
                  ':input[name="style_options[widgets][' . $location_id . '][' . $widget_id . '][enable]"]' => ['checked' => TRUE],
                ],
              ],
            ];

            // Build the appropriate array for the states API.
            $widget_dependency = 'style_options[widgets][' . $location_id . '][' . $widget_id . ']';

            // Get the current configuration of this widget type.
            $configuration = [];
            if (!empty($this->options['widgets'][$location_id][$widget_id])) {
              $configuration = $this->options['widgets'][$location_id][$widget_id];
            }
            $configuration['dependency'] = $widget_dependency;
            $instance = $widgetTypeManager->createInstance($widget_id, $configuration);

            // Get the configuration form of this widget type.
            $form['widgets'][$location_id][$widget_id] = $instance->buildConfigurationForm($form['widgets'][$location_id][$widget_id], $form_state);

            // Close the vs-dependent wrapper.
            $form['widgets'][$location_id][$widget_id]['wrapper_close'] = [
              '#markup' => '</div>',
            ];
          }
        }
      }
    }


    // Browse locations and remove the header if no widget is available.
    foreach ($location as $location_id => $location_name) {
      // If no widget is available, the only key is "header".
      if (count(array_keys($form['widgets'][$location_id])) == 1) {
        unset($form['widgets'][$location_id]);
      }
    }

    // Remove the widget section header if there is no widget available.
    if (empty($form['widgets'])) {
      unset($form['widgets']);
      unset($form['widgets_header']);
    }

    $form['views_slideshow_wrapper_close'] = [
      '#markup' => '</div>',
    ];

    // Add a library to style the form.
    $form['#attached']['library'] = ['views_slideshow/form'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    // Validate all slideshow type plugins values.
    $typeManager = \Drupal::service('plugin.manager.views_slideshow.slideshow_type');
    foreach ($typeManager->getDefinitions() as $id => $definition) {
      $type = $typeManager->createInstance($id);
      $type->validateConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    // Submit all slideshow type plugins values.
    $typeManager = \Drupal::service('plugin.manager.views_slideshow.slideshow_type');
    foreach ($typeManager->getDefinitions() as $id => $definition) {
      $type = $typeManager->createInstance($id);
      $type->submitConfigurationForm($form, $form_state);
    }
  }

}
