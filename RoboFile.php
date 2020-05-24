<?php

// @codingStandardsIgnoreStart

/**
 * Base tasks for setting up a module to test within a full Drupal environment.
 *
 * @class RoboFile
 * @codeCoverageIgnore
 */
class RoboFile extends \Robo\Tasks {

  /**
   * Command to update the database.
   *
   * @return \Robo\Result
   *   The result of the collection of tasks.
   */
  public function databaseUpdate() {
    $collection = $this->collectionBuilder();
    $collection->addTaskList($this->updateDatabaseTasks());
    return $collection->run();
  }

  /**
   * Command to build the project.
   *
   * @return \Robo\Result
   *   The result of the collection of tasks.
   */
  public function jobBuildProject() {
    $collection = $this->collectionBuilder();
    $collection->addTaskList($this->copyConfigurationFiles());
    $collection->addTaskList($this->runComposer());
    return $collection->run();
  }

  /**
   * Command to configure the files directory.
   */
  public function filesConfigure() {
    $collection = $this->collectionBuilder();
    $collection->addTaskList($this->setUpFilesDirectory());
    return $collection->run();
  }

  /**
   * Copies configuration files.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function copyConfigurationFiles() {
    $force = TRUE;
    $tasks = [];
    $tasks[] = $this->taskFilesystemStack()
      ->copy('.github/config/settings.local.php',
        'web/sites/default/settings.local.php', $force);
    return $tasks;
  }

  /**
   * Sets up the files directory.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function setUpFilesDirectory() {
    $tasks = [];
    $tasks[] = $this->taskFilesystemStack()
      ->mkdir('web/sites/default/files')
      ->chgrp('web/sites/default/files', 'www-data', TRUE)
      ->chown('web/sites/default/files', 'www-data', TRUE)
      ->chmod('web/sites/default/files', 0770);
    return $tasks;
  }

  /**
   * Runs composer commands.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function runComposer() {
    $tasks = [];
    $tasks[] = $this->taskComposerValidate()->noCheckPublish();
    $tasks[] = $this->taskComposerInstall()
      ->noInteraction()
      ->envVars(['COMPOSER_ALLOW_SUPERUSER' => 1, 'COMPOSER_DISCARD_CHANGES' => 1] + getenv())
      ->optimizeAutoloader();
    return $tasks;
  }

  /**
   * @return array
   */
  protected function updateDatabaseTasks(){
    $tasks = [];

    $tasks[] = $this->drush()
      ->args('updatedb')
      ->option('verbose');
    $tasks[] = $this->drush()
      ->args('config-import')
      ->option('verbose');
    $tasks[] = $this->drush()
      ->args('cache:rebuild');

    return $tasks;
  }

  /**
   * Runs a Drush command.
   *
   * @return \Robo\Task\Base\Exec
   *   A Drush exec command.
   */
  protected function drush() {
    return $this->taskExec('vendor/bin/drush')->option('yes');
  }

}
