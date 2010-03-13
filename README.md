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
After that place Dojo SDK sources to generated directory. 
By default it is: `<project_directory>/web/js/dojo/src/`
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
