<?php

namespace Drupal\geofield\Plugin\GeofieldProximitySource;

use Drupal\Core\Form\FormStateInterface;
use Drupal\geofield\Plugin\GeofieldProximitySourceBase;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Defines 'Geofield Manual Origin' plugin.
 *
 * @package Drupal\geofield\Plugin
 *
 * @GeofieldProximitySource(
 *   id = "geofield_manual_origin",
 *   label = @Translation("Manual Origin (Default)"),
 *   description = @Translation("Allow the Manual input of Distance and Origin (as couple of Latitude and Longitude in decimal degrees.)"),
 *   exposedDescription = @Translation("Manual input of Distance and Origin (as couple of Latitude and Longitude in decimal degrees.)"),
 *   context = {},
 * )
 */
class ManualOriginDefault extends GeofieldProximitySourceBase {

  /**
   * Constructs a ManualOriginDefault object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->origin['lat'] = isset($configuration['origin']) && is_numeric($configuration['origin']['lat']) ? $configuration['origin']['lat'] : '';
    $this->origin['lon'] = isset($configuration['origin']) && is_numeric($configuration['origin']['lon']) ? $configuration['origin']['lon'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(array &$form, FormStateInterface $form_state, array $options_parents, $is_exposed = FALSE) {

    $lat = isset($this->configuration['origin']['lat']) ? $this->configuration['origin']['lat'] : $this->origin['lat'];
    $lon = isset($this->configuration['origin']['lon']) ? $this->configuration['origin']['lon'] : $this->origin['lon'];

    $form["origin"] = [
      '#title' => t('Origin Coordinates'),
      '#type' => 'geofield_latlon',
      '#description' => t('Value in decimal degrees. Use dot (.) as decimal separator.'),
      '#default_value' => [
        'lat' => $lat,
        'lon' => $lon,
      ],
    ];

    // If it is a proximity filter context and IS NOT exposed, render origin
    // hidden and origin_summary options.
    if ($this->viewHandler->configuration['id'] == 'geofield_proximity_filter' && !$is_exposed) {
      $form['origin_hidden_flag'] = [
        '#type' => 'checkbox',
        '#title' => t('Hide the Origin Input elements from the Exposed Form'),
        '#default_value' => isset($this->configuration['origin_hidden_flag']) ? $this->configuration['origin_hidden_flag'] : FALSE,
        '#states' => [
          'visible' => [
            ':input[name="options[expose_button][checkbox][checkbox]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['origin_summary_flag'] = [
        '#type' => 'checkbox',
        '#title' => t('Show (anyway) the Origin coordinates as summary in the Exposed Form'),
        '#default_value' => isset($this->configuration['origin_summary_flag']) ? $this->configuration['origin_summary_flag'] : TRUE,
        '#states' => [
          'visible' => [
            ':input[name="options[source_configuration][origin_hidden_flag]"]' => ['checked' => TRUE],
          ],
        ],
      ];

    }

    // If it IS exposed, eventually Hide the Origin components..
    if ($is_exposed && (isset($this->configuration['origin_hidden_flag']) && $this->configuration['origin_hidden_flag'])) {

      $form["origin"]['#attributes']['class'][] = 'visually-hidden';

      // Eventually Render the Origin Summary.
      if (isset($this->configuration['origin_summary_flag']) && $this->configuration['origin_summary_flag']) {
        $form['origin_summary'] = [
          "#type" => 'html_tag',
          "#tag" => 'div',
          '#value' => $this->t('from Latitude: @lat and Longitude: @lon.', [
            '@lat' => new FormattableMarkup('<span class="geofield-lat"> @lat</span>', [
              '@lat' => $lat,
            ]),
            '@lon' => new FormattableMarkup('<span class="geofield-lon"> @lon</span>', [
              '@lon' => $lon,
            ]),
          ]),
          '#attributes' => [
            'class' => ['proximity-origin-summary'],
          ],
        ];
      }
    }

  }

}
