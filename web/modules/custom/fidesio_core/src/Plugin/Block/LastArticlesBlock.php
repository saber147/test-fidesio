<?php

namespace Drupal\fidesio_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'last articles' Block.
 *
 * @Block(
 *   id = "last_x_articles_block",
 *   admin_label = @Translation("Derniers articles"),
 *   category = @Translation("Derniers articles"),
 * )
 */
class LastArticlesBlock extends BlockBase implements BlockPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $lastArticles = [];
    $config = $this->getConfiguration();

    $numberOfArticlesToShow = $config['last_articles_number'];
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'article');
    $query->condition('status', 1);
    $query->sort('created', 'ASC');
    $query->range(0, $numberOfArticlesToShow);
    $nids = $query->execute();

    if (!empty($nids)) {
      $lastArticles =  \Drupal\node\Entity\Node::loadMultiple($nids);
    }

    return [
      '#theme' => 'last_articles_block',
      '#cache' => [
        'max_age' => $config['cache_invalidation']
      ],
      '#articles' => $lastArticles,
      '#count' => $numberOfArticlesToShow
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['last_articles_number'] = [
      '#type' => 'number',
      '#title' => $this->t('Nombre des articles' ),
      '#description' => $this->t('Nombre des derniers articles à afficher dans le bloc'),
      '#default_value' => isset($config['last_articles_number']) ? $config['last_articles_number'] : 1,
      '#size' => 5,
      '#maxlength' => 5,
      '#min' => 1,
      '#required' => TRUE,
    ];

    $form['cache_invalidation'] = [
      '#type' => 'number',
      '#title' => $this->t('Invalidation de cache chaque'),
      '#description' => $this->t('Nombre d\'heure entre chaque invalidation de cache pour ce bloc'),
      '#default_value' => isset($config['cache_invalidation']) ? $config['cache_invalidation'] : 0,
      '#size' => 5,
      '#maxlength' => 5,
      '#min' => 0,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['last_articles_number'] = $values['last_articles_number'];
    $this->configuration['cache_invalidation'] = $values['cache_invalidation'];
  }

  /**
   * Invalidate block cache if an article
   * have been updated or added (beside the max-age).
   *
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $articleNodesTags = \Drupal::service('handy_cache_tags.manager')->getBundleTag('node', 'article');
    return Cache::mergeTags(parent::getCacheTags(), [$articleNodesTags]);
  }

}
