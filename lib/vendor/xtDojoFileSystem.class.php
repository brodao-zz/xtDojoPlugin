<?php
/**
 * Extends sfFilesystem for xtDojoPlugin.
 *
 * @package xtDojoPlugin
 * @subpackage filesystem
 * @author Sadikov Vladimir aka DMC <sadikoff@gmail.com>
 * @version 1.5
 */

require_once 'Archive/Tar.php';

class xtDojoFileSystem extends sfFilesystem
{
  /**
   * Dojo paths array
   *
   * @var array
   */
  private $paths = array();

  /**
   *  @see sfFilesystem
   */
  public function __construct(sfEventDispatcher $dispatcher = null, sfFormatter $formatter = null)
  {
    $this->paths = sfConfig::get('xtDojo_fullPath');
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
   * Writes data to the file.
   *
   * @param string $file The filename
   * @param string $content The content of given file to write
   */
  public function write2file($file, $content)
  {
    if(!file_put_contents($file, $content))
    {
      $this->logSection('file', 'An error accured when trying to write data.',null,'ERROR');
    }
    else
    {
      $this->logSection('file', 'Data successfully writen to '.$file,null,'ERROR');
    }
  }
  /**
   * Generates dojo profile for building purposes.
   *
   * @return string
   */
  public function generateProfile()
  {

    $finder = sfFinder::type('dir')->discard('util')->maxdepth(0);
    $buildDirsPath = $finder->in($this->paths['src']);

    $prefixes = '';
    foreach ($buildDirsPath as $path)
    {
      $tmp = end(explode('/', $path));
      $buildDirs[$tmp] = $path;
      $prefixes .= sprintf(<<<EOL
    [ "%s", "%s" ],

EOL
              , $tmp
              , $path);
    }

    $profileContent = sprintf(<<<EOL
dependencies = {
  layers: [
    {
      name: "../app/main.js",
      layerDependencies: [],
      dependencies: ["app.main"]
    }
  ],

  prefixes: [
%s    [ "app", "%s" ]
  ]
}
EOL
            , $prefixes
            , $this->paths['dev']
            );

    $profileFile = $this->paths['src'].sfConfig::get('xtDojo_buildScriptDir').sfConfig::get('xtDojo_profile');

    $this->touch($profileFile);
    $this->write2file($profileFile, $profileContent);

    return $profileFile;
  }
  /**
   * This method generates default main.js file for dojo.
   *
   * @return void
   */
  public function generateMain()
  {
    $mainJSFile    = $this->paths['dev'].'/main.js';
    $mainJSContent = <<<EOF
dojo.provide("app.main");

dojo.require("dojo.cookie");
dojo.require("dojo.parser");

EOF;


      $this->touch($mainJSFile);
      $this->write2file($mainJSFile, $mainJSContent);
  }

  /**
   * This method generates dojo builder file according to OS.
   * Dojo builder is used for automatic build dojo via symfony console.
   *
   * @param boolean $flag
   *
   * @return void
   */
  public function generateBuilder($flag)
  {
    $ext = stristr(PHP_OS, 'WIN')?'bat':'sh';

    $dojoBuilderFile = sfConfig::get('sf_web_dir').'/js/dojo/dojoBuild.'.$ext;
    if($flag) $this->remove($dojoBuilderFile);

    if(!is_file($dojoBuilderFile))
    {
      $this->touch($dojoBuilderFile);
      if ($ext == 'sh') {
        $this->chmod($dojoBuilderFile, 0755);
        $dojoBuilderContent = sprintf(<<<EOF
cd %s/util/buildscripts/
./build.sh "$@"
EOF
                , $this->paths['src']
        );
      }
      else
      {
        $dojoBuilderContent = sprintf(<<<EOF
cd %s/util/buildscripts/
build.bat %%*
EOF
                , $this->paths['src']
        );
      }
      $this->write2file($dojoBuilderFile, $dojoBuilderContent);
    }
    else
    {
      $this->logSection('dojo', 'Dojo builder helper allready exists.',null,'ERROR');
    }
  }

  /**
   * This method downloads dojo SDK sources from internet.
   *
   * @param string $from Url to SDK archive
   * @param string $to Absolute path to save file
   *
   * @return void
   */
  public function getSources($from, $to)
  {
    $ch = curl_init($from);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
    $data = curl_exec($ch);
    $this->logSection('dojo', 'Successfully downloaded.');
    $this->write2file($to, $data);
  }

  /**
   * This method extracts previously downloaded dojo SDK sources
   *
   * @param string $archive
   * @param string $path_to
   * @param string $exclude_path
   *
   * @return void
   */
  public function extractSources($archive, $path_to, $exclude_path)
  {
    $this->logSection('dojo', 'Start extracting files.');
    $archiveTar = new Archive_Tar($archive);
    $flag = $archiveTar->extractModify($path_to, $exclude_path);
    if($flag)
    {
      $this->touch(sfConfig::get('sf_data_dir').'/.dojo_installed');
      $this->logSection('dojo', 'All files have been successfully extracted.');
    }
    else
      $this->logSection('dojo', 'An error accured while extracting files. Please check all manualy.',null,'ERROR');
  }
}