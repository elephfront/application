# Elephfront application skeleton

Elephfront is an open-source PHP front-end template creations stack.  

This repository is an application skeleton based on the Elephfront tools. You can use it to quickly kickstart your new templates creation project.  
This application skeleton will manage for you your SASS assets compilation, your CSS minification, your JS inclusion, your JS minification and comes bundled with a Live Reload server that will really ease your templates developments.

## Requirements

- PHP >= 7.1.0
- The [Robo task runner](https://github.com/consolidation/Robo)
- The [absalomedia/sassphp](https://github.com/absalomedia/sassphp) PHP extension. (See [this section](https://github.com/elephfront/robo-sass#installing-the-extension) of the [robo-sass task](https://github.com/elephfront/robo-sass) to learn how to install it.)

## Installation

You can create a new elephfront project using [composer](http://getcomposer.org):

```
composer create-project elephfront/application my-template-project
```

## Starting the server

The main feature of the package is to provide a command that will start a PHP server to serve your pages.  
It also will start a Live Reload server in the background to automatically refresh your browser when you make changes to one your assets file (as there is a watcher for changes in SASS files, JS files and pages / system files).
 
Once installed, you can use the following command to start both servers :

```
vendor/bin/robo serve
```

This will start a new server under the URL `http://localhost:9876/` and launch your default browser to this URL.
From now on, every change you make in your assets files will trigger a "compilation" on those files and automatically refresh your browser in order to ease your development process.

## Commands

Aside from the `serve` command, this skeleton provides a few other useful methods if you need to perform specific tasks without using the servers.
 
### build

This command will build the **build** directory from the **src** directory : it will copy all **pages** and **system** directories and compile all assets.

### compile:assets

Compile all assets (SASS & JS).

### compile:js

Include all JS scripts included using the `roboimport()` method (from the [robo-import-js task](https://github.com/elephfront/robo-import-js)) and minify them (using the [robo-js-minify task](https://github.com/elephfront/robo-js-minify)).

### compile:scss

Compile the *.scss* files using the [robo-sass task](https://github.com/elephfront/robo-sass) and minify them using the [robo-css-minify task](https://github.com/elephfront/robo-css-minify).

### copy:directories

Copy the directories **pages** and **system** (and all the directories configured under the `compile.directories` configuration key) to the **build** directory.