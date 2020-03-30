<?php

namespace Drupal\fidesio_core;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationPluginManager;

class ExecuteMigrationPlugin {
  /**
   * Migration plugin manager service.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManager
   */
  protected $migrationPluginManager;

  /**
   * Create an instance of the class.
   *
   * @param \Drupal\migrate\Plugin\MigrationPluginManager $migrationPluginManager
   *   Migration Plugin Manager service.
   */
  public function __construct(MigrationPluginManager $migrationPluginManager) {
    $this->migrationPluginManager = $migrationPluginManager;
  }

  /**
   * @param $pluginId
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\migrate\MigrateException
   */
  public function execute($pluginId) {
    $migration_plugin = $this->migrationPluginManager->createInstance($pluginId);
    $executable = new MigrateExecutable($migration_plugin, new MigrateMessage());
    $executable->import();
  }
}
