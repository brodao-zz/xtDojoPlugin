<?php
/**
 * Service dojo class.
 *
 * @package xtDojoPlugin
 * @subpackage dojo
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 1.5
 */

class dojo
{
  /**
   *  Theme variable
   * @var string
   */
  public static $theme = 'tundra';
  /**
   * Registered programmatic dijits
   * @var array
   */
  protected static $_dijits = array();
  /**
   * Registered programmatic queries
   * @var array
   */
  protected static $_queries = array();
  /**
   * Actions to perform on window load
   * @var array
   */
  protected static $_dojoOnLoad = array();
  /**
   * Has the dijit loader been registered?
   * @var bool
   */
  protected static $_loaderRegistered = false;
  /**
   * Arbitrary javascript to include in dojo script
   * @var array
   */
  protected static $_js = array();
  /**
   * Add a script to execute onLoad
   *
   * @param  string $callback
   *
   * @return void
   */
  public static function addOnLoad($callback)
  {
    if (!in_array($callback, self::$_dojoOnLoad, true))
    {
      self::$_dojoOnLoad[] = '  '.$callback;
    }
  }
  /**
   * Prepend an onLoad event to the list of onLoad actions
   *
   * @param  string $callback 
   *
   * @return void
   */
  public static function prependOnLoad($callback)
  {
    if (!in_array($callback, self::$_dojoOnLoad, true))
    {
      array_unshift(self::$_dojoOnLoad, $callback);
    }
  }
  /**
   *  Sets dojo theme
   *
   * @param string $theme
   *
   * @return void
   */
  public static function setTheme($theme)
  {
    self::$theme = $theme;
  }
  /**
   * Add a programmatic dijit
   *
   * @param  string $id
   * @param  array $params
   *
   * @return void
   */
  public static function addDijit($id, array $params)
  {
    self::$_dijits[$id] = array('id' => $id, 'params' => $params);
  }
  /**
   * Add multiple dijits at once
   *
   * Expects an array of id => array $params pairs
   *
   * @param  array $dijits
   *
   * @return void
   */
  public static function addDijits(array $dijits, array $viewDijits)
  {
    foreach ($viewDijits as $view)
    {
      foreach ($dijits[$view] as $id => $params)
      {
        self::addDijit($id, $params);
      }
    }
  }
  /**
   * Add a programmatic query
   *
   * @param  string $id
   * @param  string $select
   * @param  array  $params
   *
   * @return void
   */
  public static function addQuery($id, $select, array $params)
  {
    self::$_queries[$id] = array('select' => $select, 'params' => $params);
  }
  /**
   * Add multiple queries at once
   *
   * Expects an array of $id => $select, array $params pairs
   *
   * @param  array $dijits
   *
   * @return void
   */
  public static function addQueries(array $queries, array $viewQueries)
  {
    foreach ($viewQueries as $view)
    {
      if (isset($queries[$view]))
      {
        foreach ($queries[$view] as $id => $params)
        {
          self::addQuery($id, $params['select'], $params['params']);
        }
      }
    }
  }
  /**
   * Retrieve all dijits
   *
   * Returns dijits as an array of assoc arrays
   *
   * @return string|false
   */
  public static function getDijits()
  {
    $digitsArray = array_values(self::$_dijits);
    return !empty($digitsArray)?json_encode($digitsArray):false;
  }
  /**
   * Retrieve all queries
   *
   * Returns queries as an array of assoc arrays
   *
   * @return string|false
   */
  public static function getQueries()
  {
    $queriesArray = array_values(self::$_queries);
    return !empty($queriesArray)?json_encode($queriesArray):false;
  }
  /**
   *  Adds custom JavaScript in loader
   *
   * @param string $js
   *
   * @return void
   */
  public static function addJavaScript($js)
  {
    self::$_js[] = $js;
  }
  /**
   * Create dijit loader functionality
   *
   * @return void
   */
  public static function getLoader()
  {
    if (!self::$_loaderRegistered)
    {
      $digits = self::getDijits();
      $queries = self::getQueries();
      $js = '';

      if (false !== $digits)
      {
        $js .=<<<EOJ
  dojo.forEach(dijits, function(info){var n = dojo.byId(info.id);if(null != n) dojo.attr(n, dojo.mixin({id: info.id}, info.params));});

EOJ;
        self::addJavaScript('var dijits = '.self::getDijits().';');
      }

      if (false !== $queries)
      {
        $js .=<<<EOJ
  dojo.forEach(queries, function(info){dojo.forEach(dojo.query(info.select), function(selectTag){dojo.attr(selectTag, dojo.mixin({}, info.params));})});

EOJ;
        self::addJavaScript('var queries = '.self::getQueries().';');
      }
      $js .=<<<EOJ
  dojo.parser.parse();
EOJ;
      self::prependOnLoad($js);
      self::$_loaderRegistered = true;
    }
  }
  /**
   * initializes dojo application
   *
   * @return string
   */
  public static function init()
  {
    if (!empty(self::$_dijits)||!empty(self::$_queries))
    {
      self::getLoader();
    }

    $addOnLoad  = implode("\n", self::$_dojoOnLoad);
    $javascript = implode("\n", self::$_js);

    $html = sprintf(<<<EOF
<script type="text/javascript">
//<![CDATA[
dojo.addOnLoad(function(){
%s
});
%s
//]]>
</script>
EOF
            ,$addOnLoad
            ,$javascript
    );

    return $html;
  }

}