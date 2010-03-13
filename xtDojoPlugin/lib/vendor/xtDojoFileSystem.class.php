<?php
/**
 * Extends sfFilesystem for xtDojoBuildTask.
 *
 * @package xtDojoPlugin
 * @subpackage filesystem
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 1.0
 */

class xtDojoFileSystem extends sfFilesystem
{
  /**
   *  @see sfFilesystem
   */
  public function __construct(sfEventDispatcher $dispatcher = null, sfFormatter $formatter = null)
  {
    parent::__construct($dispatcher, $formatter);
  }
  /**
   *  @see sfFilesystem
   */
  public function execute($cmd, $stdoutCallback = null, $stderrCallback = null)
  {
    $this->logSection('exec ', $cmd);

    $descriptorspec = array(
      1 => array('pipe', 'w'), // stdout
      2 => array('pipe', 'w'), // stderr
    );

    $process = proc_open($cmd, $descriptorspec, $pipes);
    if (!is_resource($process)) {
      throw new RuntimeException('Unable to execute the command.');
    }

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $output = '';
    $err = '';
    while (!feof($pipes[1])) {
      foreach ($pipes as $key => $pipe) {
        // default line if (!$line = fread($pipe, 128))
        // was rewriten in case to provide more beautiful java output
        if (!$line = fread($pipe, 3072)) {
          continue;
        }

        if (1 == $key) {
          // stdout
          $output .= $line;
          if ($stdoutCallback){
            call_user_func($stdoutCallback, $line);
          }
        } else {
          // stderr
          $err .= $line;
          if ($stderrCallback) {
            call_user_func($stderrCallback, $line);
          }
        }
      }

      sleep(0.1);
    }

    fclose($pipes[1]);
    fclose($pipes[2]);

    if (($return = proc_close($process)) > 0) {
      throw new RuntimeException('Problem executing command.', $return);
    }

    return array($output, $err);
  }
  /**
   * Generates dojo profile for building purposes.
   *
   * @return string
   */
  public function generateProfile()
  {
    $dojoPaths = sfConfig::get('xtDojo_fullPath');

    $file_contents = sprintf(<<<EOL
dependencies = {
  layers: [
    {
      name: "../app/main.js",
      layerDependencies: [],
      dependencies: ["app.main"]
    }
  ],

  prefixes: [
    [ "dojo", "%s/dojo" ],
    [ "dijit", "%s/dijit" ],
    [ "dojox", "%s/dojox" ],
    [ "app", "%s" ]
  ]
}
EOL
            , $dojoPaths['src']
            , $dojoPaths['src']
            , $dojoPaths['src']
            , $dojoPaths['dev']
            );

    // build filename
    $filename = $dojoPaths['src'].sfConfig::get('xtDojo_buildScriptDir').sfConfig::get('xtDojo_profile');
    $this->touch($filename);
    file_put_contents($filename, $file_contents);

    return $filename;
  }
}