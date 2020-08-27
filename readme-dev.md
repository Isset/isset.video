# Isset Video Publisher

> Nothing puts more emphasis on "word" in WordPress like a 5 minute long video

## Requirements

- yarn
- docker / docker-compose
- Unix-like environment
- `realpath` needs to be installed

### Realpath setup

On MacOS, if setup fails with an error regarding `realpath` it might not be installed on your host:

- OS X: `brew install coreutils`

## Setup

Setting up your dev environment can be done by running

```bash
bin/setup
```

This will install wordpress and the [Timber](https://timber.github.io/docs/) plugin into `.docker-context`
You will need to change a setting in wp-config.php
```$xslt
define('FS_METHOD','direct');
```

## Dev server

The dev server can be started with


```bash
# -d can be omitted if you like clogging your console
docker-compose up -d
```

You should now be able to navigate to `http://localhost:2080` where you'll be able to walk through the Wordpress install.  
After installing WordPress, you need to enable the Timber and Isset Video Publisher plugin


## CSS/JS development

For CSS/JS development, you can run

```bash
bin/dev-webpack
```

To only build CSS/JS once, you can run

```bash
bin/setup-webpack
```

Which will start a webpack watch job


## Timber

Timber is a tool that provides us a Twig environment and shortcuts, documentations can be found here: https://timber.github.io/docs/

## File structure

```
| - scss # pre-processed SCSS files
| - js # pre-processed JavaScript
| - languages # pre-processed translation files
| - plugin # PHP source
  | - css # generated CSS
  | - js # generated JS
  | - languages # generated translations
  | - views # Twig templates
  |   # WordPress entry point, this is the file that 
  | - isset-video-publisher.php Wordpress will load
  |   # PHP source, following PSR-4 in namespace 
  |   # IssetBV\VideoPublisher\Wordpress\
  | - src 
    |   # Since we can't use the composer autoloader,
    |   # this is a polyfill autoloader
    | - Autoloader.php 
    |   # Main entry point for this plugin, registering of styles, 
    |   # scripts and other components happens here
    | - Plugin.php 
```

