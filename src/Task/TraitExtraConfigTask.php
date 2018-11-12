<?php

namespace GrumPHP\Task;

trait TraitExtraConfigTask
{
  /**
   * @var boolean|array
   */
  protected $extraConfig;
  
  /**
   * @return string
   */
  public function getExtraConfig($key)
  {
    if (!$this->extraConfig && $this->extraConfig !== FALSE) {
      $this->extraConfig = FALSE;
      $configured = $this->grumPHP->getTaskConfiguration($this->getName());
      if (isset($configured['_extra'])) {
        $this->extraConfig = $configured['_extra'];
      }
    }

    return $this->extraConfig && isset($this->extraConfig[$key]) ? $this->extraConfig[$key] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function alterConfig(&$config)
  {
    if (array_key_exists('_extra', $config)) {
      unset($config['_extra']);
    }
  }
}
