<?php

/**
 * @file
 * Contains \Drupal\devel\Form\SwitchUserForm.
 */

namespace Drupal\devel\Form;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that allows privileged users to generate entities.
 */
class SwitchUserForm extends FormBase {

  /**
   * The csrf token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfTokenGenerator;

  /**
   * Constructs a new SwitchUserForm object.
   *
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrf_token_generator
   */
  public function __construct(CsrfTokenGenerator $csrf_token_generator) {
    $this->csrfTokenGenerator = $csrf_token_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('csrf_token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'devel_switchuser_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['username'] = array(
      '#type' => 'textfield',
      '#description' => t('Enter username'),
      '#autocomplete_route_name' => 'user.autocomplete',
      '#maxlength' => USERNAME_MAX_LENGTH,
      '#size' => 16,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Switch'),
      '#button_type' => 'primary',
    );
    $form['#attributes'] = array('class' => array('clearfix'));
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$account = user_load_by_name($form_state->getValue('username'))) {
      $form_state->setErrorByName('username', t('Username not found'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('username');
    $path = 'devel/switch/' . $name;
    $form_state->setRedirect('devel.switch', array('name' => $name), array('query' => array( 'destination' => '', 'token' => $this->csrfTokenGenerator->get($path))));
  }
}
