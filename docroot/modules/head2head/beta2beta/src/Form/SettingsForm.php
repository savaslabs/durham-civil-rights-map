<?php

/**
 * @file
 * Contains \Drupal\beta2beta\Form\SettingsForm.
 */

namespace Drupal\beta2beta\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configures beta2beta settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'beta2beta_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['beta2beta.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['heading'] = array(
      '#markup' => '<p>' . $this->t('We’ve automatically determined that you are using <strong>Drupal @version</strong>.', array('@version' => \Drupal::VERSION)) . '</p>',
    );

    $form['version'] = array(
      '#type' => 'select',
      //'#disabled' => !beta2beta_determine_beta_version(),
      '#title' => $this->t('Allow updates from this older version of core'),
      '#default_value' => beta2beta_get_beta_version(),
      '#options' => array(
        '0' => $this->t('unknown beta version'),
        '9' => '8.0.0-beta9',
      ),
      '#description' => $this->t('To upgrade from an old beta version of core to the latest beta version, select the older version from the list.'),
    );

    if ($this->config('beta2beta.settings')->get('forced_update')) {
      $form['warning'] = array(
        '#markup' => '<p>' . $this->t('<strong>Warning:</strong> You have submitted this form before. While you can safely use this form once, repeated use of this form can cause problems since it forces the module to do updates it thinks may not be necessary.') . '</p>',
      );
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($version = $form_state->getValue('version')) {
      if (beta2beta_set_beta_version($version)) {
        $this->config('beta2beta.settings')
          ->set('version', $form_state->getValue('version'))
          ->save();
//        drupal_set_message($this->t('You can now <a href="@url">update Drupal</a> from Drupal 8.0.0-beta@version', array('!url' => Url::fromRoute('system.db_update')->toString(), '@version' => $version)));
        drupal_set_message($this->t('You can now update Drupal from Drupal 8.0.0-beta@version', array('@version' => $version)));
      }
      else {
        $this->config('beta2beta.settings')->clear('version')->save();
        drupal_set_message($this->t('No update is needed for Drupal 8.0.0-beta@version', array('@version' => $version)));
      }
      // Note that one forced update has already been requested.
      $this->config('beta2beta.settings')
        ->set('forced_update', TRUE)
        ->save();
    }
  }

}
