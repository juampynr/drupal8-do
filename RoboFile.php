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
   * Command to build the project.
   *
   * @return \Robo\Result
   *   The result of the collection of tasks.
   */
  public function jobBuildProject() {
    $collection = $this->collectionBuilder();
    $collection->addTaskList($this->copyConfigurationFiles());
    $collection->addTaskList($this->setUpFilesDirectory());
    $collection->addTaskList($this->runComposer());
    return $collection->run();
  }

  /**
   * Command to build the artifact out of the project.
   *
   * @return \Robo\Result
   *   The result of the collection of tasks.
   */
  public function jobBuildArtifact() {
    $collection = $this->collectionBuilder();
    $collection->addTaskList($this->createArtifactDir());
    $collection->addTaskList($this->copyProjectIntoArtifactDir());
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
      ->chmod('web/sites/default/files', 0777);
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
   * Creates the artifact directory.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function createArtifactDir() {
    $tasks = [];
    $tasks[] = $this->taskFilesystemStack()
      ->mkdir('/tmp/artifact');
    return $tasks;
  }

  /**
   * Copies the project into the artifact dir.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function copyProjectIntoArtifactDir() {
    $tasks = [];
    $tasks[] = $this->taskExec('pwd');
    $tasks[] = $this->taskExec('ls -hal');
    $tasks[] = $this->taskExec('rsync --exclude=.git $GITHUB_WORKSPACE /tmp/artifact/');
    $tasks[] = $this->taskFileSystemStack()->rename('/tmp/artifact', '$GITHUB_WORKSPACE/artifact');
    return $tasks;
  }

}
