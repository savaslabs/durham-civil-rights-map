<?php

namespace Drupal\simple_sitemap\Form;

use Drupal\Core\Datetime\DateFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\Core\Database\Connection;

/**
 * Class SimplesitemapSitemapsForm
 * @package Drupal\simple_sitemap\Form
 */
class SimplesitemapSitemapsForm extends SimplesitemapFormBase {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * SimplesitemapSitemapsForm constructor.
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   * @param \Drupal\simple_sitemap\Form\FormHelper $form_helper
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   */
  public function __construct(
    Simplesitemap $generator,
    FormHelper $form_helper,
    Connection $database,
    DateFormatter $date_formatter
  ) {
    parent::__construct(
      $generator,
      $form_helper
    );
    $this->db = $database;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('simple_sitemap.generator'),
      $container->get('simple_sitemap.form_helper'),
      $container->get('database'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_sitemap_sitemaps_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['simple_sitemap_settings']['#prefix'] = FormHelper::getDonationText();
    $form['simple_sitemap_settings']['#attached']['library'][] = 'simple_sitemap/sitemaps';
    $queue_worker = $this->generator->getQueueWorker();

    $form['simple_sitemap_settings']['status'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Sitemap status'),
      '#markup' => '<div class="description">' . $this->t('Sitemaps can be regenerated on demand here.') . '</div>',
      '#description' => $this->t('Variants can be configured <a href="@url">here</a>.', ['@url' => $GLOBALS['base_url'] . '/admin/config/search/simplesitemap/variants']),
    ];

    $form['simple_sitemap_settings']['status']['actions'] = [
      '#prefix' => '<div class="clearfix"><div class="form-item">',
      '#suffix' => '</div></div>',
    ];

    $form['simple_sitemap_settings']['status']['actions']['rebuild_queue_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Rebuild queue'),
      '#submit' => ['::rebuildQueue'],
      '#validate' => [],
    ];

    $form['simple_sitemap_settings']['status']['actions']['regenerate_submit'] = [
      '#type' => 'submit',
      '#value' => $queue_worker->generationInProgress()
        ? $this->t('Resume generation')
        : $this->t('Rebuild queue & generate'),
      '#submit' => ['::generateSitemap'],
      '#validate' => [],
    ];

    $form['simple_sitemap_settings']['status']['progress'] = [
      '#prefix' => '<div class="clearfix">',
      '#suffix' => '</div>',
    ];

    $form['simple_sitemap_settings']['status']['progress']['title']['#markup'] = $this->t('Progress of sitemap regeneration');

    $total_count = $queue_worker->getInitialElementCount();
    if (!empty($total_count)) {
      $indexed_count = $queue_worker->getProcessedElementCount();
      $percent = round(100 * $indexed_count / $total_count);

      // With all results processed, there still may be some stashed results to be indexed.
      $percent = $percent === 100 && $queue_worker->generationInProgress() ? 99 : $percent;

      $index_progress = [
        '#theme' => 'progress_bar',
        '#percent' => $percent,
        '#message' => $this->t('@indexed out of @total items have been processed.<br>Each sitemap variant is published after all of its items have been processed.', ['@indexed' => $indexed_count, '@total' => $total_count]),
      ];
      $form['simple_sitemap_settings']['status']['progress']['bar']['#markup'] = render($index_progress);
    }
    else {
      $form['simple_sitemap_settings']['status']['progress']['bar']['#markup'] = '<div class="description">' . $this->t('There are no items to be indexed.') . '</div>';
    }

    $sitemap_manager = $this->generator->getSitemapManager();
    $sitemap_settings = [
      'base_url' => $this->generator->getSetting('base_url', ''),
      'default_variant' => $this->generator->getSetting('default_variant', NULL),
    ];
    $sitemap_statuses = $this->fetchSitemapInstanceStatuses();
    $published_timestamps = $this->fetchSitemapInstancePublishedTimestamps();
    foreach ($sitemap_manager->getSitemapTypes() as $type_name => $type_definition) {
      if (!empty($variants = $sitemap_manager->getSitemapVariants($type_name, FALSE))) {
        $sitemap_generator = $sitemap_manager
          ->getSitemapGenerator($type_definition['sitemapGenerator'])
          ->setSettings($sitemap_settings);

        $form['simple_sitemap_settings']['status']['types'][$type_name] = [
          '#type' => 'details',
          '#title' => '<em>' . $type_definition['label'] . '</em> ' . $this->t('sitemaps'),
          '#open' => !empty($variants) && count($variants) <= 5,
          '#description' => !empty($type_definition['description']) ? '<div class="description">' . $type_definition['description'] . '</div>' : '',
        ];
        $form['simple_sitemap_settings']['status']['types'][$type_name]['table'] = [
          '#type' => 'table',
          '#header' => [$this->t('Variant'), $this->t('Status')],
          '#attributes' => ['class' => ['form-item', 'clearfix']],
        ];
        foreach ($variants as $variant_name => $variant_definition) {
          $row = [];
          $row['name']['data']['#markup'] = '<span title="' . $variant_name . '">' . $this->t($variant_definition['label']) . '</span>';
          if (!isset($sitemap_statuses[$variant_name])) {
            $row['status'] = $this->t('pending');
          }
          else {
            switch ($sitemap_statuses[$variant_name]) {
              case 0:
                $row['status'] = $this->t('generating');
                break;
              case 1:
                $row['status']['data']['#markup'] = $this->t('<a href="@url" target="_blank">published on @time</a>',
                  ['@url' => $sitemap_generator->setSitemapVariant($variant_name)->getSitemapUrl(), '@time' => $this->dateFormatter->format($published_timestamps[$variant_name])]
                );
                break;
              case 2:
                $row['status'] = $this->t('<a href="@url" target="_blank">published on @time</a>, regenerating',
                  ['@url' => $sitemap_generator->setSitemapVariant($variant_name)->getSitemapUrl(), '@time' => $this->dateFormatter->format($published_timestamps[$variant_name])]
                );
                break;
            }
          }
          $form['simple_sitemap_settings']['status']['types'][$type_name]['table']['#rows'][$variant_name] = $row;
          unset($sitemap_statuses[$variant_name]);
        }
      }
    }
    if (empty($form['simple_sitemap_settings']['status']['types'])) {
      $form['simple_sitemap_settings']['status']['types']['#markup'] = $this->t('No variants have been defined');
    }

/*    if (!empty($sitemap_statuses)) {
      $form['simple_sitemap_settings']['status']['types']['&orphans'] = [
        '#type' => 'details',
        '#title' => $this->t('Orphans'),
        '#open' => TRUE,
      ];

      $form['simple_sitemap_settings']['status']['types']['&orphans']['table'] = [
        '#type' => 'table',
        '#header' => [$this->t('Variant'), $this->t('Status'), $this->t('Actions')],
      ];
      foreach ($sitemap_statuses as $orphan_name => $orphan_info) {
        $form['simple_sitemap_settings']['status']['types']['&orphans']['table']['#rows'][$orphan_name] = [
          'name' => $orphan_name,
          'status' => $this->t('orphaned'),
          'actions' => '',
        ];
      }
    }*/

    return $form;
  }

  /**
   * @return array
   *  Array of sitemap statuses keyed by variant name.
   *  Status values:
   *  0: Instance is unpublished
   *  1: Instance is published
   *  2: Instance is published but is being regenerated
   *
   * @todo Move to SitemapGeneratorBase or DefaultSitemapGenerator so it can be overwritten by sitemap types with custom storages.
   */
  protected function fetchSitemapInstanceStatuses() {
    $results = $this->db
      ->query('SELECT type, status FROM {simple_sitemap} GROUP BY type, status')
      ->fetchAll();

    $instances = [];
    foreach ($results as $i => $result) {
      $instances[$result->type] = isset($instances[$result->type])
        ? $result->status + 1
        : (int) $result->status;
    }

    return $instances;
  }

  /**
   * @return array
   *
   * @todo Move to SitemapGeneratorBase or DefaultSitemapGenerator so it can be overwritten by sitemap types with custom storages.
   */
  protected function fetchSitemapInstancePublishedTimestamps() {
    return $this->db
      ->query('SELECT type, MAX(sitemap_created) FROM (SELECT sitemap_created, type FROM {simple_sitemap} WHERE status = :status) AS timestamps GROUP BY type', [':status' => 1])
      ->fetchAllKeyed(0, 1);
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function generateSitemap(array &$form, FormStateInterface $form_state) {
    $this->generator->generateSitemap();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function rebuildQueue(array &$form, FormStateInterface $form_state) {
    $this->generator->rebuildQueue();
  }

}
