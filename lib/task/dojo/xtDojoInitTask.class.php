<?php
/**
 * Configure default directory structure for plugin, and initializes some
 * service files.
 *
 * @package xtDojoPlugin
 * @subpackage task
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 1.5
 */

class xtDojoInitTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions (array(
        new sfCommandOption('reinit', null, sfCommandOption::PARAMETER_NONE, 'Force re-initialization of dojo.'),
        new sfCommandOption('get-src', null, sfCommandOption::PARAMETER_NONE, 'downloads dojo SDK.'),
        new sfCommandOption('ver', null, sfCommandOption::PARAMETER_OPTIONAL, 'dojo SDK version.','1.5.0'),
    ));

    $this->namespace        = 'dojo';
    $this->name             = 'init';
    $this->briefDescription = 'initializes dojo environment for project';
    $this->detailedDescription = <<<EOF
The [dojo:init|INFO] task sets up the current project for using the xtDojoPlugin

Creates default directory structure for dojo:

  [./symfony dojo:init|INFO]

Force re-initialization of dojo environment. Deletes all data in dojo folders. USE CAREFULLY.

  [./symfony dojo:init --reinit|INFO]

Automaticaly download dojo sources from the internet and extract it.

  [./symfony dojo:init --get-src|INFO]

Select dojo version to download.

  [./symfony dojo:init --get-src --ver=1.5.0]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $fileSystem = new xtDojoFileSystem($this->dispatcher, $this->formatter);

    $flag = false;

    $installFile = sfConfig::get('sf_data_dir').'/.dojo_installed';

    // Validate dojo version
    if (!preg_match('@^[0-9]{1}\.[0-9]{1}\.[0-9]{1}([0-9]{1})?((b|rc)?[0-9]{1}([0-9]{1})?)?$@is', $options['ver']))
    {
      throw new sfCommandException(sprintf('Version "%s" is invalid. Please check --ver option you have entered or contact plugin author.', $options['ver']));
    }

    if ($options['reinit'])
    {
      if(!$this->askConfirmation (array_merge(
        array('This command will remove all data in dojo folders and will create new structure:', ''),
        array('', 'Are you sure you want to proceed? (y/N)')
      ), 'QUESTION_LARGE', false)
      )
      {
        $this->logSection ('dojo', 'task aborted');

        return 1;
      }
      else
      {
        file_exists($installFile) ? $fileSystem->remove($installFile) : null;
        $flag = true;
      }
    }
    // load all the plugin configuraton
    $this->configuration->loadPlugins();

    $paths = sfConfig::get('xtDojo_fullPath');
    $webDir = sfConfig::get('sf_web_dir');

    foreach ($paths as $key => $path)
    {
      if ($key == 'prod') continue;
      
      if ($flag)
      {
        $files = sfFinder::type('file')->in($path);
        $dirs  = sfFinder::type('dir')->in($path);

        $fileSystem->remove($files);
        $fileSystem->remove($dirs);
        $fileSystem->remove($path);
      }
      $fileSystem->mkdirs($path);
    }

    $mainJSFile = $paths['dev'].'/main.js';
    if (!is_file($mainJSFile))
    {
      $fileSystem->generateMain();
    }
    else
    {
      $this->logSection('dojo', 'Dojo main.js allready exists.',null,'ERROR');
    }

    if (function_exists('curl_init'))
    {
      if ($options['get-src'])
      {
        $dojoSDK = array(
            'downloadLink'  => str_replace('{ver}', $options['ver'], sfConfig::get('dojo_SDK_link')),
            'localFilename' => sfConfig::get('sf_cache_dir').'/dojo-sdk.tgz',
            'excludePath'   => 'dojo-release-'.$options['ver'].'-src'
        );

        if (!file_exists($dojoSDK['localFilename']))
        {
          $this->logSection('dojo', 'Sources archive not found. Initializing download....');
          $fileSystem->getSources($dojoSDK['downloadLink'],$dojoSDK['localFilename']);
        }

        if (!file_exists($installFile))
        {
          $fileSystem->extractSources($dojoSDK['localFilename'], $paths['src'], $dojoSDK['excludePath']);
        }
        else
        {
          $this->logSection('dojo', 'Dojo has been allready extracted.');
        }
      }
    }
    else
    {
      $this->logSection('dojo', 'You must activate php_curl module before running this task with get-src option',null,'ERROR');
    }
    

    $fileSystem->generateBuilder($flag);
    if (!$flag)
    {
      $this->logBlock('Project configured. Now you must put dojo SDK sources(dojo,dojox,dijit,util folders) in '.$webDir.'/js/dojo/src folder.', 'INFO');
    }
    else
    {
      $this->logBlock('Project reconfigured. All data was delted. Now you must put dojo SDK sources(dojo,dojox,dijit,util folders) in '.$webDir.'/js/dojo/src folder, and rebuild dojo if needed.', 'INFO');
    }
  }

}