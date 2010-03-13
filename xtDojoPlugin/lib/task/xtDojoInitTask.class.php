<?php
/**
 * Configure default directory structure for plugin, and initializes some
 * service files. Not fully implemented yet
 *
 * @package xtDojoPlugin
 * @subpackage task
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 0.9alfa
 */

class xtDojoInitTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure( )
  {
    $this->namespace        = 'dojo';
    $this->name             = 'init';
    $this->briefDescription = 'initializes dojo environment';
    $this->detailedDescription = <<<EOF
The [dojo:init|INFO] task sets up the current project for using the xtDojoPlugin

Creates default directory structure for dojo:

  [./symfony dojo:init|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute( $arguments = array( ), $options = array( ) )
  {
    // first we have to load all the plugin configuraton
    $this->configuration->loadPlugins( );

    $fileSystem = $this->getFilesystem( );

    $paths = sfConfig::get('xtDojo_fullPath');

    foreach ( $paths as $key => $path ) {
      if ( $key == 'prod' ) {
        continue;
      }
      $fileSystem->mkdirs( $path );
    }

    $fileSystem->touch( $paths['dev'].'/main.js' );
    file_put_contents( $paths['dev'].'/main.js', "dojo.provide(\"app.main\");\n\r\n\r" );

    $buildJSContent = sprintf(<<<EOF
var buildScriptsPath = '%s/';
load(buildScriptsPath + "build.js");
EOF
    ,sfConfig::get('sf_web_dir').'/js/dojo/src'.sfConfig::get('xtDojo_buildScriptDir'));

    $fileSystem->touch( sfConfig::get('sf_web_dir').'/js/dojo/builder.js' );
    file_put_contents( sfConfig::get('sf_web_dir').'/js/dojo/builder.js', $buildJSContent );

  }
}