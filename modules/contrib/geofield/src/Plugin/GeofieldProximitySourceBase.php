<?php

namespace Drupal\geofield\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\geofield\Exception\HaversineUnavailableException;
use Drupal\geofield\Exception\InvalidPointException;
use Drupal\geofield\Exception\ProximityUnavailableException;
use Drupal\views\Plugin\views\HandlerBase;

/**
 * Base class for Geofield Proximity Source plugins.
 */
abstract class GeofieldProximitySourceBase extends PluginBase implements GeofieldProximitySourceInterface {

  use StringTranslationTrait;

  /**
   * The name of the constant defining the measurement unit.
   *
   * @var string
   */
  protected $units;

  /**
   * The view handler which uses this proximity plugin.
   *
   * @var \Drupal\views\Plugin\views\HandlerBase
   */
  protected $viewHandler;

  /**
   * The origin point to measure proximity from.
   *
   * @var array
   */
  protected $origin;

  /**
   * Check if Origin is empty.
   *
   * @param array $origin
   *   The origin array.
   *
   * @return bool
   *   The bool result.
   */
  protected function originIsValidButEmpty(array $origin) {
    return (isset($origin['lat']) && isset($origin['lon']) && empty($origin['lat']) && empty($origin['lon']));
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(array &$form, FormStateInterface $form_state, array $options_parents, $is_exposed = FALSE) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(array &$form, FormStateInterface $form_state, array $options_parents) {
  }

  /**
   * {@inheritdoc}
   */
  public function getOrigin() {
    return $this->origin;
  }

  /**
   * {@inheritdoc}
   */
  public function setOrigin(array $origin) {
    return $this->origin = $origin;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnits($units) {

    // If the given value is not a valid option, throw an error.
    if (!in_array($units, $this->getUnitsOptions())) {
      $message = t('Invalid units supplied.');
      \Drupal::logger('geofield')->error($message);
      return FALSE;
    }

    // Otherwise set units to the given value.
    else {
      $this->units = $units;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnits() {
    return $this->units;
  }

  /**
   * Get the list of valid options for units.
   *
   * @return array
   *   The list of available unit types.
   */
  public function getUnitsOptions() {
    return array_keys(geofield_radius_options());
  }

  /**
   * {@inheritdoc}
   */
  public function setViewHandler(HandlerBase $view_handler) {
    $this->viewHandler = $view_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getProximity($lat, $lon) {
    if (!$this->validLocation($lat, $lon)) {
      throw new InvalidPointException($this->t('@proximity_handler reports Invalid Point coordinates', [
        '@proximity_handler' => get_class($this),
      ]));
    }

    // Fetch the value of the units that have been set for this class. The
    // constants are defined in the module file.
    $radius = constant($this->units);

    $origin = $this->getOrigin();
    if (empty($origin) || !is_numeric($origin['lat']) || !is_numeric($origin['lon'])) {
      throw new ProximityUnavailableException($this->t('@proximity_handler not able to get the Proximity value, due to invalid Origin', [
        '@proximity_handler' => get_class($this),
      ]));
    }

    // Convert degrees to radians.
    $origin_latitude = deg2rad($origin['lat']);
    $origin_longitude = deg2rad($origin['lon']);
    $destination_latitude = deg2rad($lat);
    $destination_longitude = deg2rad($lon);

    // Calculate proximity.
    $proximity = $radius * acos(
        cos($origin_latitude)
        * cos($destination_latitude)
        * cos($destination_longitude - $origin_longitude)
        + sin($origin_latitude)
        * sin($destination_latitude)
      );

    if (!is_numeric($proximity) || !abs($proximity) > 0) {
      throw new ProximityUnavailableException($this->t('@proximity_handler not able to calculate valid Proximity value', [
        '@proximity_handler' => get_class($this),
      ]));;
    }

    return $proximity;

  }

  /**
   * {@inheritdoc}
   */
  public function getHaversineOptions() {

    $origin = $this->getOrigin();
    if ($this->originIsValidButEmpty($origin)) {
      return NULL;
    }
    if (!$origin || !is_numeric($origin['lat']) || !is_numeric($origin['lon'])) {
      throw new HaversineUnavailableException('Not able to calculate Haversine Options due to invalid Proximity origin location.');
    }

    return [
      'origin_latitude' => $origin['lat'],
      'origin_longitude' => $origin['lon'],
      'earth_radius' => constant($this->units),
    ];

  }

  /**
   * Test the given latitude and longitude values.
   *
   * @param float $lat
   *   The latitude value.
   * @param float $lon
   *   The longitude value.
   *
   * @return bool
   *   The flag indicates whether location is valid.
   *
   * @todo: add more tests, particularly around max/min values.
   */
  protected function validLocation($lat, $lon) {
    if (!is_numeric($lat) || !is_numeric($lon)) {
      $message = t('Invalid location supplied, latitude and longitude must be numerical values.');
      \Drupal::logger('geofield')->error($message);
      return FALSE;
    }
    return TRUE;
  }

}
