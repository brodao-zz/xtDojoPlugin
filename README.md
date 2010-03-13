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

    [php]
    // <project_directory>/config/ProjectConfiguration.class.php
    ...
    public function setup() 
    {
    ...
        $this->enablePlugins('xtDojoPlugin');
    ...
    }
    ...

