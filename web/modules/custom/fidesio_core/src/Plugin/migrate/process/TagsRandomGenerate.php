<?php
namespace Drupal\fidesio_core\Plugin\migrate\process;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Perform generation of random tags
 *
 * @MigrateProcessPlugin(
 *   id = "tags_random_generate"
 * )
 *
 * To generate custom random tags use the following:
 *
 * @code
 * field_text:
 *   plugin: tags_random_generate
 *   source: text
 * @endcode
 *
 */
class TagsRandomGenerate extends ProcessPluginBase implements ContainerFactoryPluginInterface {
  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The state manager.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, MigrationInterface $migration, EntityTypeManagerInterface $entityTypeManager, StateInterface $state) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $migration,
      $container->get('entity_type.manager'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, \Drupal\migrate\MigrateExecutableInterface $migrateExecutable, \Drupal\migrate\Row $row, $destinationProperty) {
    if (empty($this->state->get('tags_ids'))) {
      $tags_ids = [];
      $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('tags');
      foreach ($terms as $term) {
        $tags_ids[] = $term->tid;
      }
      $this->state->set('tags_ids', $tags_ids);
    }

    $tags = $this->state->get('tags_ids');
    $key = array_rand($tags);

    return $tags[$key];

  }


}
