<?php
/**
 * Service dojo class.
 *
 * @package xtDojoPlugin
 * @subpackage dojo
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 0.9alfa
 */

class dojo {
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
  public static function addOnLoad($callback) {
    if (!in_array($callback, self::$_dojoOnLoad, true)) {
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
  public static function prependOnLoad($callback) {
    if (!in_array($callback, self::$_dojoOnLoad, true)) {
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
  public static function setTheme($theme) {
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
  public static function addDijit($id, array $params) {
    self::$_dijits[$id] = array(
            'id'     => $id,
            'params' => $params,
    );
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
  public static function addDijits(array $dijits, array $viewDijits) {
    foreach ($viewDijits as $view) {
      foreach ($dijits[$view] as $id => $params) {
        self::addDijit($id, $params);
      }
    }
  }
  /**
   * Retrieve all dijits
   *
   * Returns dijits as an array of assoc arrays
   *
   * @return array
   */
  public static function getDijits() {
    return json_encode(array_values(self::$_dijits));
  }
  /**
   *  Adds custom JavaScript in loader
   *
   * @param string $js
   *
   * @return void
   */
  public static function addJavaScript($js) {
    self::$_js[] = $js;
  }
  /**
   * Create dijit loader functionality
   *
   * @return void
   */
  public static function getLoader() {
    if (!self::$_loaderRegistered) {
      $js =<<<EOJ
  dojo.forEach(dijits, function(info) {
    var n = dojo.byId(info.id);
    if (null != n) {
      dojo.attr(n, dojo.mixin({ id: info.id }, info.params));
    }
  });
  dojo.parser.parse();
EOJ;
      self::prependOnLoad($js);
      self::addJavaScript('var dijits = '.self::getDijits().';');
      self::$_loaderRegistered = true;
    }
  }
  /**
   * initializes dojo application
   *
   * @return string
   */
  public static function init() {
    if (!empty(self::$_dijits)) {
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