<?php
/**
 * Task to build dojo javascripts. Not fully implemented yet.
 * 
 * @package xtDojoPlugin
 * @subpackage task
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 1.5alfa
 */
class xtDojoBuildTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('ver', null, sfCommandOption::PARAMETER_OPTIONAL, 'The Dojo version', 'DEV'),
    ));

    $this->namespace        = 'dojo';
    $this->name             = 'build';
    $this->briefDescription = 'builds production version of dojo';
    $this->detailedDescription = <<<EOF
The [dojo:build|INFO] task builds production javascript using the dojo build system.

NOTICE: [You need to have java installed!|COMMENT]

For example you can build dojo without any parameter. Version will be set to "[DEV|COMMENT]"

  [./symfony dojo:build|INFO]

If you wish to you custom version(default is DEV) set [ver|COMMENT] option like this

  [./symfony dojo:build --ver="1.0"|INFO]

NOTICE: other building options would be implemented in the future releases!
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // first we have to load all the plugin configuraton
    $this->configuration->loadPlugins();

    $fileSystem = new xtDojoFileSystem($this->dispatcher, $this->formatter);

    $paths = sfConfig::get('xtDojo_fullPath');
    $webDir = sfConfig::get('sf_web_dir');

    $builder = $webDir.'/js/dojo/dojoBuild.'.(stristr(PHP_OS, 'WIN')?'bat':'sh');

    $profile = $fileSystem->generateProfile();

    $cmd = $builder.' cssOptimize=comments action=clean,release mini=true optimize=shrinksafe expandProvide=true layerOptimize=shrinksafe profileFile='.$profile.'  releaseDir='.$paths['prod'].' version="'.$options['ver'].'" releaseName=""';
    $fileSystem->execute($cmd, array($this,'logBuildInfo'), array($this,'logBuildError'));

    $fileSystem->remove($profile);

    $this->logSection('dojo', 'Build finished.');
  }
  /**
   * Info log function for java output
   *
   * !NB: This is a test version of function. Will be rewritten in future.
   *
   * @param string $message
   */
  public function logBuildInfo($message)
  {
    if (strlen($message) > 1)
    {
      $lines = explode("\n", $message);
      foreach ($lines as $line)
      {
        if (strlen($line) > 1)
        {
          $lineArray = explode(':',$line);
          if (count($lineArray) == 1)
          {
            $this->logSection('Info', $lineArray[0]);
          }
          else if (count($lineArray) > 2)
          {
            $this->logSection(trim($lineArray[1]), trim($lineArray[2]));
          }
          else
          {
            $this->logSection(trim($lineArray[0]), trim($lineArray[1]));
          }
        }
      }
    }
  }
  /**
   * Eror log function for java output
   *
   * !NB: This is a test version of function. Will be rewritten in future.
   *
   * @param string $message
   */
  public function logBuildError($message)
  {
    if (strlen($message) > 1)
    {
      $lines = explode("\n", $message);
      foreach ($lines as $line)
      {
        if (strlen($line) > 1)
        {
          $this->logSection('ERROR', $line, null, 'ERROR');
        }
      }
    }
  }

}