<?php

/**
 * @file
 * Contains \Drupal\devel\Access\SwitchAccess.
 */

namespace Drupal\devel\Access;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Routing\Access\AccessInterface as RoutingAccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Defines a special access checker for the user switch route.
 */
class SwitchAccess implements RoutingAccessInterface {

  /**
   * CSRF token validator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfToken;

  /**
   * Constructs a SwitchAccessCheck object.
   *
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrf_token
   *   CSRF token validator.
   */
  public function __construct(CsrfTokenGenerator $csrf_token) {
    $this->csrfToken = $csrf_token;
  }

  /**
   * {@inheritdoc}
   */
  public function appliesTo() {
    return array('_devel_switch_user_access');
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request, AccountInterface $account) {
    // Suppress notices when on other pages when menu system still checks access.
    $name = $request->attributes->get('name');
    $token = $request->query->get('token');
    $destination = $request->query->get('destination');
    return $account->hasPermission('switch users') && $this->csrfToken->validate($token, "devel/switch/$name", TRUE) ? static::ALLOW : static::DENY;
  }

}
