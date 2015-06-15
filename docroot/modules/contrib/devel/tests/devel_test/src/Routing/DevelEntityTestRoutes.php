<?php

/**
 * @file
 * Contains \Drupal\devel_test\Routing\RouteSubscriber.
 */

namespace Drupal\devel_test\Routing;

use Symfony\Component\Routing\Route;

/**
 * Subscriber for Entity Test routes.
 */
class DevelEntityTestRoutes {

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $routes = array();

    $routes["entity.devel_entity_test_canonical.canonical"] = new Route(
      'devel_entity_test_canonical/{devel_entity_test_canonical}',
      array('_entity_view' => 'devel_entity_test_canonical.full', '_title' => 'Test full view mode'),
      array('_access' => 'TRUE')
    );

    $routes["entity.devel_entity_test_edit.edit_form"] = new Route(
      'devel_entity_test_edit/manage/{devel_entity_test_edit}',
      array('_controller' => '\Drupal\entity_test\Controller\EntityTestController::testEdit', 'entity_type_id' => 'devel_entity_test_edit'),
      array('_permission' => 'administer entity_test content'),
      array('parameters' => array(
        'devel_entity_test_edit' => array('type' => 'entity:' . 'devel_entity_test_edit'),
      ))
    );

    return $routes;
  }

}
