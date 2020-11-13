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

This will install wordpress into `.docker-context`
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

## Translations ##

To make this work we need to do the following steps:

- Create translation files
- Translate the labels
- Compile translations to a .mo file

#### Follow the steps below to accomplish this: ####

To generate the .pot files we need the wp-cli commandline tool. Install it using the instructions found [here](https://make.wordpress.org/cli/handbook/guides/installing/).

To create a new .pot file, run 

```bash
bin/create-translations
```

This will scan the plugin for labels being used, generate a .pot file for php and merge it with the js .pot file. (This is automatically being built by babel during transpilation).

To translate the labels, I used an external tool called [POEdit](https://poedit.net/). Open the .po file for the locale you want to edit, and click Catalogue -> Update from POT file, and select `isset-video-publisher.pot`.

This will add the missing labels to your list of translations. When you're done, you need to compile the translations to an .mo file. Do this by clicking File -> Compile to MO.

## Codestyles ##

Install WP codestandards and PHP CodeSniffer:

```bash
composer install
```

Check:

```bash
make cscheck
```

Automatically Fix:

```bash
make csfix
```

