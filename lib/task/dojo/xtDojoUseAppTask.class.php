<?php
/**
 * This class is a simple task, which helps to configure
 * application to use dojo as decorator.
 *
 * @package xtDojoPlugin
 * @subpackage task
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 1.5
 */

class xtDojoUseAppTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCommandArgument('app', sfCommandArgument::REQUIRED, 'The application name'),
    ));

    $this->addOptions(array(
        new sfCommandOption('theme', null, sfCommandOption::PARAMETER_OPTIONAL, 'The Dojo Theme', 'tundra'),
        new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Force recreation of dojo.yml file'),
    ));

    $this->namespace           = 'dojo';
    $this->name                = 'use-app';
    $this->briefDescription    = 'initializes dojo for selected application';
    $this->detailedDescription = <<<EOF
The [dojo:use-app|INFO] task sets up the selected application for using the xtDojoPlugin

Creates default dojo configuration files in application folder:

  [./symfony dojo:use-app frontend|INFO]

Use given theme to generate config files

  [./symfony dojo:use-app --theme=tundra frontend|INFO]

To recreate dojo.yml file for application use [--force|COMMENT] option

  [./symfony dojo:use-app --force frontend|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['app'];

    // Validate the application name
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $app))
    {
      throw new sfCommandException(sprintf('The application name "%s" is invalid.', $app));
    }

    $appDir = sfConfig::get('sf_apps_dir').'/'.$app;
    if (!is_dir($appDir))
    {
      throw new sfCommandException(sprintf('The application "%s" does not exists.', $appDir));
    }

    // we have to load all the plugin configuraton
    $this->configuration->loadPlugins( );
    
    $dojoDirs = sfConfig::get('xtDojo_fullPath');
    $mdFile = $dojoDirs['dev'].'/main.js';
    if(!is_file($mdFile))
    {
      throw new sfCommandException('You must run [./sumfony dojo:init] before using this command.');
    }

    $fileSystem = new xtDojoFileSystem($this->dispatcher, $this->formatter);
    $yaml       = new sfYaml();

    $flag = false;

    $appDojoConfig    = $appDir.'/config/dojo.yml';
    $appFiltersConfig = $appDir.'/config/filters.yml';

    $this->logSection('dojo', 'Creating default configuration for application.');
    $yamlConfigurations = array();
    $yamlConfigurations = array(
        'dojo'    =>  array(
            'default' => array(
                'theme'   => array('name'   => $options['theme']),
                'actions' => array('all'    => array('layout')),
                'dijits'  => array('layout' => array()),
            )
        ),
        'filters' => array(
            'dojo'    =>  array('class' => 'xtDojoFilter')
        )
    );

    $dojoConfig = $yaml->dump($yamlConfigurations['dojo'],3);

    if(!file_exists($appDojoConfig) || $options['force'])
    {
      $fileSystem->touch($appDojoConfig);
      file_put_contents($appDojoConfig, $dojoConfig);
      $this->logSection('dojo', 'dojo.yml for selected application has been created.');
    }
    else
    {
      $this->logSection('dojo', 'dojo.yml allready exists.',null,'ERROR');
      $flag = true;
    }
    
    $filtersConfig = $yaml->dump($yamlConfigurations['filters'],3);

    $this->logSection('dojo', 'Trying to configure filters.');
    $filters = file_get_contents($appFiltersConfig);
    if(!strstr($filters, 'dojo'))
    {
      $filters = str_replace('execution:', $filtersConfig.'execution:', $filters);
      file_put_contents($appFiltersConfig, $filters);
      $this->logSection('dojo', 'Filters configured successfully.');
    }
    else
    {
      $this->logSection('dojo', 'Probably there is section \'dojo\' in filters.yml of '.$app.' application. Please check it manualy.', null, 'ERROR');
      $flag = true;
    }

    if(true===$flag)
      $this->logBlock('Probably dojo is allready configured for selected application. Please checkout README for the configuration details.', 'ERROR');
    else
      $this->logBlock('Everything is configured. You may now use dojo in selected application of your project.', 'INFO');
  }
}