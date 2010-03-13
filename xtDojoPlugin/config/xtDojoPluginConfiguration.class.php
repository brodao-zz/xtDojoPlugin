<?php
/**
 * Plugin configuration file. You can customize folders and
 * some other optons here.
 *
 * @package xtDojoPlugin
 * @subpackage config
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 1.0
 */

class xtDojoPluginConfiguration extends sfPluginConfiguration
{
  /**
   * Configures the plugin.
   *
   * This method is called before the plugin's classes have been added to sfAutoload.
   */
  public function configure()
  {
    $sf_web_dir = sfConfig::get('sf_web_dir');

    $parameters = array(

      'xtDojo_fullPath'         => array (
        'prod' => $sf_web_dir.'/js/framework',
        'dev'  => $sf_web_dir.'/js/dojo/dev',
        'src'  => $sf_web_dir.'/js/dojo/src',
      ),

      'xtDojo_buildScriptDir'   => '/util/buildscripts',
      'xtDojo_profile'          => '/profiles/sf.profile.js',

      'xtDojo_webPath'          => array (
        'prod' => '/js/framework',
        'dev'  => '/js/dojo/dev',
        'src'  => '/js/dojo/src'
      ),

    );

    sfConfig::add($parameters);
  }
}