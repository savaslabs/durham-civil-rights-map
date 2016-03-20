<?php

namespace Drupal\views_geojson\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\Display\DisplayPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Style plugin to render view as GeoJSON code.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "geojson",
 *   title = @Translation("GeoJSON"),
 *   theme = "views_view_geojson",
 *   description = @Translation("Displays field data in GeoJSON data format."),
 *   display_types = {"data"}
 * )
 */
class GeoJson extends StylePluginBase {

  /**
   * Overrides \Drupal\views\Plugin\views\style\StylePluginBase::$usesRowPlugin.
   */
  protected $usesRowPlugin = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\style\StylePluginBase::$usesFields.
   */
  protected $usesFields = TRUE;

  /**
   * Overrides \Drupal\views\Plugin\views\style\StylePluginBase::$usesRowClass.
   */
  protected $usesRowClass = FALSE;

  /**
   * Overrides Drupal\views\Plugin\views\style\StylePluginBase::$usesGrouping.
   */
  protected $usesGrouping = FALSE;

  /**
   * The serializer which serializes the views result.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('serializer'),
      $container->getParameter('serializer.formats')
    );
  }

  /**
   * Constructs a Plugin object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SerializerInterface $serializer, array $serializer_formats) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->definition = $plugin_definition + $configuration;
    $this->serializer = $serializer;
    $this->formats = array('json', 'html');
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['data_source'] = array(
      'contains' => array(
        'value' => array('default' => 'asc'),
        'latitude' => array('default' => 0),
        'longitude' => array('default' => 0),
        'geofield' => array('default' => 0),
        'wkt' => array('default' => 0),
        'name_field' => array('default' => 0),
        'description_field' => array('default' => 0),
      ),
    );
    $options['attributes'] = array('default' => NULL, 'translatable' => FALSE);
    $options['jsonp_prefix'] = array(
      'default' => NULL,
      'translatable' => FALSE
    );
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $fields = array();
    $fields_info = array();

    // Get list of fields in this view & flag available geodata fields.
    $handlers = $this->displayHandler->getHandlers('field');
    $field_definition = $this->displayHandler->getOption('fields');

    // Check for any fields, as the view needs them.
    if (empty($handlers)) {
      $form['error_markup'] = array(
        '#value' => t('You need to enable at least one field before you can configure your field settings'),
        '#prefix' => '<div class="error form-item description">',
        '#suffix' => '</div>',
      );
      return;
    }

    // Go through fields, fill $fields and $fields_info arrays.
    foreach ($this->displayHandler->getHandlers('field') as $field_id => $handler) {
      $fields[$field_id] = $handler->definition['title'];
      $fields_info[$field_id]['type'] = $field_definition[$field_id]['type'];
    }

    // Default data source.
    $data_source_options = array(
      'latlon' => t('Other: Lat/Lon Point'),
      'geofield' => t('Geofield'),
      'wkt' => t('WKT'),
    );

    // Data Source options.
    $form['data_source'] = array(
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => t('Data Source'),
    );

    $form['data_source']['value'] = array(
      '#type' => 'select',
      '#multiple' => FALSE,
      '#title' => t('Map Data Sources'),
      '#description' => t('Choose which sources of data that the map will provide features for.'),
      '#options' => $data_source_options,
      '#default_value' => $this->options['data_source']['value'],
    );

    // Other Lat and Lon data sources.
    if (count($fields) > 0) {
      $form['data_source']['latitude'] = array(
        '#type' => 'select',
        '#title' => t('Latitude Field'),
        '#description' => t('Choose a field for Latitude.  This should be a field that is a decimal or float value.'),
        '#options' => $fields,
        '#default_value' => $this->options['data_source']['latitude'],
        '#states' => array(
          'visible' => array(
            ':input[name="style_options[data_source][value]"]' => array('value' => 'latlon'),
          ),
        ),
        );

      $form['data_source']['longitude'] = array(
        '#type' => 'select',
        '#title' => t('Longitude Field'),
        '#description' => t('Choose a field for Longitude.  This should be a field that is a decimal or float value.'),
        '#options' => $fields,
        '#default_value' => $this->options['data_source']['longitude'],
        '#states' => array(
          'visible' => array(
            ':input[name="style_options[data_source][value]"]' => array('value' => 'latlon'),
          ),
        ),
        );

      // Get Geofield-type fields.
      $geofield_fields = array();
      foreach ($fields as $field_id => $field) {
        // @TODO We need to check if the field type is `geofield_default`. But
        // at the moment this information is missing from the array, due to a
        // bug with Geofield 8.x-1.x-dev. When the bug is fixed, we can add a
        // check here again.
        $geofield_fields[$field_id] = $field;
      }

      // Geofield.
      $form['data_source']['geofield'] = array(
        '#type' => 'select',
        '#title' => t('Geofield'),
        '#description' => t("Choose a Geofield field. Any formatter will do; we'll access Geofield's underlying WKT format."),
        '#options' => $geofield_fields,
        '#default_value' => $this->options['data_source']['geofield'],
        '#states' => array(
          'visible' => array(
            ':input[name="style_options[data_source][value]"]' => array('value' => 'geofield'),
          ),
        ),
        );

      // WKT.
      $form['data_source']['wkt'] = array(
        '#type' => 'select',
        '#title' => t('WKT'),
        '#description' => t('Choose a WKT format field.'),
        '#options' => $fields,
        '#default_value' => $this->options['data_source']['wkt'],
        '#states' => array(
          'visible' => array(
            ':input[name="style_options[data_source][value]"]' => array('value' => 'wkt'),
          ),
        ),
        );
    }

    $form['data_source']['name_field'] = array(
      '#type' => 'select',
      '#title' => t('Title Field'),
      '#description' => t('Choose the field to appear as title on tooltips.'),
      '#options' => array_merge(array('' => ''), $fields),
      '#default_value' => $this->options['data_source']['name_field'],
    );

    $form['data_source']['description_field'] = array(
      '#type' => 'select',
      '#title' => t('Description'),
      '#description' => t('Choose the field or rendering method to appear as
          description on tooltips.'),
      '#required' => FALSE,
      '#options' => array_merge(array('' => ''), $fields),
      '#default_value' => $this->options['data_source']['description_field'],
    );

    // Attributes and variable styling description.
    $form['attributes'] = array(
      '#type' => 'fieldset',
      '#title' => t('Attributes and Styling'),
      '#description' => t('Attributes are field data attached to each feature.  This can be used with styling to create Variable styling.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['jsonp_prefix'] = array(
      '#type' => 'textfield',
      '#title' => t('JSONP prefix'),
      '#default_value' => $this->options['jsonp_prefix'],
      '#description' => t('If used the JSON output will be enclosed with parentheses and prefixed by this label, as in the JSONP format.'),
    );

    // Make array of attributes.
    $variable_fields = array();
    // Add name and description.
    if (!empty($this->options['data_source']['name_field'])) {
      $variable_fields['name'] = '${name}';
    }
    if (!empty($this->options['data_source']['description_field'])) {
      $variable_fields['description'] = '${description}';
    }

    // Go through fields again to ID variable fields.
    // TODO: is it necessary to call getHandlers twice or can we reuse data from $fields?
    foreach ($this->displayHandler->getHandlers('field') as $field => $handler) {
      if (($field != $this->options['data_source']['name_field']) && ($field != $this->options['data_source']['description_field'])) {
        $variable_fields[$field] = '${' . $field . '}';
      }
    }

    // TODO: Figure out what will work here. This syntax is probably wrong!
    $variables_list = array(
      '#theme' => 'item_list',
      '#items' => $variable_fields,
      '#attributes' => array('class' => array('description'))
    );

    $markup = '<p class="description">' .
      t('Fields added to this view will be attached to their respective feature, (point, line, polygon,) as attributes.
      These attributes can then be used to add variable styling to your themes. This is accomplished by using the %syntax
      syntax in the values for a style.  The following is a list of formatted variables that are currently available;
      these can be placed right in the style interface.', array('%syntax' => '${field_name}'))
      . '</p>';

    // TODO: Replace this with the dedicated renderer access call (if one exists). See: https://api.drupal.org/api/drupal/core%21lib%21Drupal.php/function/Drupal%3A%3Aservice/8
    $markup .= \Drupal::service('renderer')->render($variables_list);
    $markup .= '<p class="description">' . t('Please note that this does not apply to Grouped Displays.') . '</p>';

    $form['attributes']['styling'] = array(
      '#type' => 'markup',
      '#markup' => $markup,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $features = array(
      'type' => 'FeatureCollection',
      'features' => array(),
    );

    // Render each row.
    foreach ($this->view->result as $i => $row) {
      $this->view->row_index = $i;
      if ($feature = _views_geojson_render_fields($this->view, $row, $i)) {
        $features['features'][] = $feature;
      }
    }
    unset($this->view->row_index);

    // Render the collection to JSON.
    $json = \Drupal\Component\Serialization\Json::encode($features);

    if (!empty($this->options['jsonp_prefix'])) {
      $json = $this->options['jsonp_prefix'] . "($json)";
    }

    if (!empty($this->view->live_preview)) {
      // Pretty-print the JSON.
      $json = _views_geojson_encode_formatted($features);
      if (!empty($this->options['jsonp_prefix'])) {
        $json = $this->options['jsonp_prefix'] . "($json)";
      }
    }

    // Everything else returns output.
    return $json;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return ['json', 'html'];
  }

}
