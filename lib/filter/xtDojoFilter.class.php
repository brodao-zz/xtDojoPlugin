<?php
/**
 * xtDojoFilter automatically adds javascripts and stylesheet
 * information to the sfResponse content.
 *
 * @package xtDojoPlugin
 * @subpackage filter
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 1.5
 */

class xtDojoFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute(sfFilterChain $filterChain)
  {
    $response = $this->context->getResponse();
    $request = $this->context->getRequest();

    $moduleName = $this->context->getModulename();
    require $this->context->getConfigCache()->checkConfig('modules/'.$moduleName.'/config/dojo.yml');

    $dojoTheme = sfConfig::get('dojo_theme', array('name' => 'tundra'));

    if ($this->isFirstCall())
    {
      $dojoDijits     = sfConfig::get('dojo_dijits', array());
      $dojoQueries    = sfConfig::get('dojo_queries', array());
      $dojoViewParams = sfConfig::get('dojo_actions', array('all'=>array('layout')));

      $viewAction     = $this->context->getActionname();
      $dojoView       = key_exists($viewAction, $dojoViewParams) ? array_merge_recursive($dojoViewParams['all'],$dojoViewParams[$viewAction]):$dojoViewParams['all'];

      dojo::addDijits($dojoDijits, $dojoView);
      dojo::addQueries($dojoQueries, $dojoView);
      dojo::setDojoParser(!($request->isXmlHttpRequest()));
    }

    dojo::setTheme($dojoTheme['name']);

    $this->addDojoCss($response, $dojoTheme);
    $this->addDojoJs($response);

    // execute next filter
    $filterChain->execute();

  }
  /**
   *  Includes all dojo CSS files in response
   *
   * @param sfWebResponse $response
   * @param array $dojoTheme
   *
   * @return void
   */
  protected function addDojoCss(sfWebResponse $response, array $dojoTheme)
  {
    $dojoPaths = $this->getDojoPaths();

    if ('dev' == sfConfig::get('sf_dojo_env','dev'))
    {
      $cssPath = $dojoPaths['src'];
    }
    else
    {
      $cssPath = $dojoPaths['prod'];
    }

    $response->addStylesheet($cssPath.'/dijit/themes/'.$dojoTheme['name'].'/'.$dojoTheme['name'].'.css');
    if ( array_key_exists('css', $dojoTheme) && is_array($dojoTheme['css']) && !empty($dojoTheme['css']))
    {
      foreach ($dojoTheme['css'] as $style)
      {
        $response->addStylesheet($cssPath.$style);
      }
    }
  }
  /**
   *  Includes all dojo JS files in response
   *
   * @param sfWebResponse $response
   *
   * @return void
   */
  protected function addDojoJs(sfWebResponse $response)
  {
    $dojoPaths = $this->getDojoPaths();

    if ('dev' == sfConfig::get('sf_dojo_env','dev'))
    {
      $dojoJS = $dojoPaths['src'].'/dojo/dojo.js';
      $mainJS = $dojoPaths['dev'].'/main.js';
    }
    else
    {
      $dojoJS = $dojoPaths['prod'].'/dojo/dojo.js';
      $mainJS = $dojoPaths['prod'].'/app/main.js';
    }

    $response->addJavascript($dojoJS);
    $response->addJavascript($mainJS);
  }
  /**
   * Returns http path to the dojo sources
   *
   * @return string
   */
  protected function getDojoPaths()
  {
    return sfConfig::get('xtDojo_webPath');
  }
}