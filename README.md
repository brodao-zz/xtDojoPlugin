symfony + dojo = xtDojoPlugin
================================

Requirements
------------
* [symfony framework](http://www.symfony-project.org/installation) 1.2.X or higher

* [Dojo Toolkit SDK](http://www.dojotoolkit.org/download/)

## Installation ##

  * Install the plugin (via a package)

        symfony plugin:install xtDojoPlugin

  * Install the plugin (via a svn)
  
        svn co http://svn.symfony-project.com/plugins/xtDojoPlugin/trunk plugins/xtDojoPlugin

  * Install the plugin (via a git)

        git clone git://github.com/sadikoff/xtDojoPlugin.git plugins/xtDojoPlugin

  * Activate the plugin in the `config/ProjectConfiguration.class.php`
  
        class ProjectConfiguration extends sfProjectConfiguration
        {
          public function setup()
          {
            ...
            $this->enablePlugins('xtDojoPlugin');
            ...
          }
        }

  * Initialize plugin

       symfony dojo:init --get-src

  It's strongly recomended to use `--get-src` option. With it plugin will download dojo sources and extract it to default directories. You will recieve following structure:

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
              dojoBuild.(sh|bat according to your OS)

  * Setup application you want to use dojo

       symfony dojo:use-app backend


## Using plugin ##

Checkout global `dojo.yml` file in the config folder of application you configured. For example:

    apps/backend/config/dojo.yml

This file has following structure:

    default: # required element
      theme: # theme block
        name: 'dojo_theme' # dojo theme title | default: tundra
        css:  ['css_file', 'another_css_file'] # additional css files
      actions: # required element, action groups
        all: [group_name, another_group_name] # required elemrnt, set wich groups of dijits use for all actions
        action_name: [group_name] # set custom action to use specific groups of dijits
      dijits: # dijit blocks container
        group_name: # dijits block title
          block_id: {dojotype: 'dojoType'} # id of html element in template: set of attributes for this id
          another_block_id: {dojotype: 'dojoType'} # id of html element in template: set of attributes for this id
        another_group_name: # another dijits block
      queries: # queries block container (optional)
        group_name: # queries block title
          block_name:
            select: 'selector' # dojo selector
            params: {dojotype: 'dojoType'} # set of attributes for this block
          another_block_name: # another queries block

dojo.yml file example:

    default:
      theme:
        name: 'tundra'
      actions:
        all: ['layout']
      dijits:
        layout:
          borderConteiner: { dojoType: 'dijit.layout.BorderContainer', design: 'headline', liveSplitters: 'true', style: 'width:100%;height:100%' }
          topPane: { dojoType: 'dijit.layout.ContentPane', region: 'top', minSize: '50', splitter: 'true', style: 'height:50px' }
          bottomPane: { dojoType: 'dijit.layout.ContentPane', region: 'bottom', minSize: '50', splitter: 'false', style: 'height:50px' }
          centerPane: { dojoType: 'dijit.layout.ContentPane', region: 'center', title: 'Another Pane' }
      queries:
        layout:
          submit_button:
            select: 'input[type="submit"]'
            params: { dojoType: 'dijit.form.Button', label: 'Submit' }
          input_text:
            select: 'input[type="text"]'
            params: { dojoType: 'dijit.form.TextBox' }
          input_password:
            select: 'input[type="password"]'
            params: { dojoType: 'dijit.form.TextBox' }

template code for this specification will be:

    <div id="borderConteiner">
      <div id="topPane" title="The Title">
        <h1>I'm content!</h1>
      </div>
      <div id="centerPane">
        <h1>I'm more content!</h1>
        <input type="text" name="field_1" />
        <input type="password" name="field_2" />
        <input type="submit" />
      </div>
    </div>

Also you can use per module dojo.yml files to specify custom definition for layouts. You must place additional dojo.yml files into config folder of module. Files has folowwing structure

    [yml]
    default: # same as global, but not required
    all: # same as default excepts theme block 

> #### Warning!
> default directive is not required for this file. If you specify it here it will rewrite global specification

Some dojo instances must be defined in layout.php of your application for dojo. You must put in head block the following code:

    [php]
    <?php echo dojo::init() ?>

before `</head>` tag, and set theme with

    <body class="<?php echo dojo::$theme ?>">

To add javascript functions in onload action you may use

    [php]
    <?php dojo::addOnLoad('<your javascript code here>') ?>

before `init()` is called.

To specify wich dojo widjets to use, edit main.js file in `web/js/dojo/dev` folder

    example main.js listing:

    dojo.provide("app.main");

    dojo.require("dojo.cookie");
    dojo.require("dojo.parser");

    dojo.require("dijit.layout.BorderContainer");
    dojo.require("dijit.layout.ContentPane");
    dojo.require("dijit.form.Button");
    dojo.require("dijit.form.TextBox");



## Dojo build ##

Plugin has dojo building system. To accelerate your application you must build dojo with following command:

    symfony dojo:build

> #### Warning!
> to build dojo successfully you must have JRE/JDK installed on your computer
>
> there nay be some errors while building on windows machine

after build it is necessary to put following string in your settings.yml file

    [yml]
    dojo_env: 'prod'  # default value is dev

you must rebuild dojo after main.js changing

> #### Feedback
> questions and suggestions you can sent to sadikoff [at] gmail.com
>
> Special thanks to Alan Cardilo (brodao [at] gmail.com) for queries idea and realization
