# Elephfront application skeleton

**This repository is under development.**

Elephfront is an open-source PHP front-end stack for templates creation.  

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

This will start a new server under the `http://localhost:9876/` URL and launch your default browser to this URL.  
From now on, every change you make in your assets files will trigger a "compilation" on those files and automatically refresh your browser in order to ease your development process.

## Directory structure

By default, the project contains a **src** directory. This is where you put all your raw assets.   
The expected structure is the following :

```
src/
  assets/
    css/
    js/
  pages/
  system/
```

### assets/css

The **assets/css** folder is where you put your SASS files.  
By default a **main.scss** file is expected and will be compiled and minified.

### assets/js

The **assets/js** folder is where you put your JS files.  
By default a **main.js** file is expected and will be compiled and minified.

### pages

The **pages** folder is where you put the various templates you want to created. The files are expected to be **.php** files. The inner structure is left to you. Just be aware that if you create an **index.php** file, it will fetched by default by the router if you try to reach a sub-directory. You can use everything you would do in PHP in those files (like `include`, `require`, functions, etc.), they will be interpreted by a PHP server.

### system

This folder is internal to Elephfront and contains the router used by the internal PHP server launched by the `serve` command and the error page if the page you try to reach does not exist when the server is launched. This is also were the **robo-live-reload** will put its JS file to make the browser listens for messages from the Web Socket server.    
In most cases, you will never need to touch the files in this directory.

## Configuration

### Compilation tasks

By default, the compilations tasks only process a `main.scss` and a `main.js` file.  
If you wish to have the CSS compilation task or the JS compilation task to manage more files, you can add them by creating and using the **elephfront-config.php** configuration file at the root of the project.

This file is expected to return a single array. Its structure should be the same as the one from the `loadDefaultConfig()` method in the **RoboFile.php** file.  
Let's say you have the following directory and file structure:

```
src/
  assets/
    css/
      home/
        slider.scss
      main.scss
      home.scss // contains an `include('home/slider.scss')`
    js/
  pages/
  system/
```

You could have the following **elephfront-config.php** file:

```php
<?php
return [
    'compile' => [
        'css' => [
            $config['paths']['source'] . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'home.scss' => $config['paths']['build'] . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'home.css',
        ]
    ]
];
```

From now on, when the `compile:scss` task is called, it will not only manage your **main.scss** file, but also your **home.scss** file.  
Note that the configuration from your **elephfront-config.php** file is merged with the default configuration, so you do not have to rewrite the basic configuration when defining an **elephfront-config.php** file.  
Please also note that the current configuration is passed to the **elephfront-config.php** file through the variable `$config` so you can get access to the **source** and **build** directories path.  

The exact same thing can be done for JS file : just change the `css` key under the `compile` key to `js`.

### Directory copy task

As for assets compilation, you can add more directories to copy from the **source** to the **build** directory. By default, only the **pages** and **system** directories are copied. You can add more using the **elephfront-bootstrap.php** files:

```php
<?php
return [
    'compile' => [
        'directories' => [
            $config['paths']['source'] . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR => $config['paths']['build'] . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR,
        ]
    ]
];
```

This would also copy the directory under **src/assets/fonts** to **build/assets/fonts**.

TODO : **elephfront-bootstrap.php**

## Commands

Aside from the `serve` command, this skeleton provides a few other useful methods if you need to perform specific tasks without using the servers.
 
### `build`

This command will build the **build** directory from the **src** directory : it will copy all **pages** and **system** directories and compile all assets.

### `compile:assets`

Compile all assets (SASS & JS).

### `compile:js`

Include all JS scripts included using the `roboimport()` method (from the [robo-import-js task](https://github.com/elephfront/robo-import-js)) and minify them (using the [robo-js-minify task](https://github.com/elephfront/robo-js-minify)).

### `compile:scss`

Compile the *.scss* files using the [robo-sass task](https://github.com/elephfront/robo-sass) and minify them using the [robo-css-minify task](https://github.com/elephfront/robo-css-minify).

### `copy:directories`

Copy the directories **pages** and **system** (and all the directories configured under the `compile.directories` configuration key) to the **build** directory.

## Contributing

If you find a bug or would like to ask for a feature, please use the [GitHub issue tracker](https://github.com/Elephfront/application/issues).
If you would like to submit a fix or a feature, please fork the repository and [submit a pull request](https://github.com/Elephfront/application/pulls).

## License

Copyright (c) 2017, Yves Piquel and licensed under [The MIT License](http://opensource.org/licenses/mit-license.php).
Please refer to the LICENSE.txt file.
