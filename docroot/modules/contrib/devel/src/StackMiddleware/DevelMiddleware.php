<?php

/**
 * @file
 * Contains \Drupal\devel\StackMiddleware\DevelMiddleware.
 */

namespace Drupal\devel\StackMiddleware;

use Drupal\Component\Utility\Timer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Provides a HTTP middleware to collect performance related data for devel.
 */
class DevelMiddleware implements HttpKernelInterface {

  /**
   * The wrapped HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Constructs a new DevelMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The wrapped HTTP kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel) {
    $this->httpKernel = $http_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    // TODO Probably we can move here the database log start and the initial
    //   read of memory usage.
    Timer::start('devel_page');

    return $this->httpKernel->handle($request, $type, $catch);
  }

}
