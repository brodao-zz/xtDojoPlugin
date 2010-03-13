symfony + dojo = xtDojoPlugin
================================

Requirements
------------
* [symfony framework](http://www.symfony-project.org/installation) 1.2.X or higher

* [Dojo Toolkit SDK](http://www.dojotoolkit.org/download/)

Installation
------------
At first, you need to download or checkout plugin sources. Then to install plugin into project, 
move xtDojoPlugin into plugin directory and activate it.
    // <project_directory>/config/ProjectConfiguration.class.php
    ...
    public function setup() 
    {
    ...
        $this->enablePlugins('xtDojoPlugin');
    ...
    }
    ...
After activation run clear cache symfony task.
    symfony cc
Now plugin is ready to configure dojo environment. You must run init task 
from console like this:
    symfony dojo:init
After that download Dojo SDK sources and place it to generated `src` directory. By default it is: `<project_directory>/web/js/dojo/src/`
    default directory structure:

    web/
      ...
      js/
        ...
        dojo/
          dev/
            main.js
          src/
            dijit/
            dojo/
            dojox/
            util/
            builder.js
Now dojo is configured and you can start using it.
Using plugin
------------
Fisrt of all you must define global `dojo.yml` file in the config folder of application you need. For example:
    <project_directory>/apps/backend/config/dojo.yml
This file has following structure:
    default: # required element
      theme: # theme block
        name: 'dojo_theme' # dojo theme title | default: tundra
        css:  ['css_file', 'another_css_file'] # additional css files
      actions: # required element, action groups
        all: [group_name, another_group_name] # required elemrnt, set wich groups of dijits use for all actions
        action_name: [group_name] # set custome action to use specific groups of dijits
      dijits: # dijit blocks container
        group_name: # dijits block title
          block_id: {dojotype: 'dojoType'} # id of html element in template: set of attributes for this id
          another_block_id: {dojotype: 'dojoType'} # id of html element in template: set of attributes for this id
        another_group_name: # another dijits block
dojo.yml file example:
    default:
      theme: 'tundra'
      actions:
        all: ['layout']
      dijits:
        layout:
          borderConteiner: {dojoType: 'dijit.layout.BorderContainer', design: 'headline', liveSplitters: 'true'}
          topPane: {dojoType: 'dijit.layout.ContentPane', region: 'top', minSize: '50', splitter: 'true', style: 'height:50px'}
          centerPane: {dojoType: 'dijit.layout.ContentPane', region: 'center', title: 'Another Pane'}
template code for this specification will be:
    <div id="borderConteiner">
      <div id="topPane" title="The Title">
        <h1>I'm content!</h1>
      </div>
      <div id="centerPane">
        <h1>I'm more content!</h1>
      </div>
    </div>
