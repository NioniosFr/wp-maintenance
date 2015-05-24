# wp-maintenance

A [wp-cli](http://wp-cli.org/) command to manage [WordPress's](https://wordpress.org) maintenance mode.

## Features
* Easily define the period the site will be in maintenance mode
* Provide a custom page to use as the temporary screen

## Dependencies
* php >= 5.3
* wp-cli

## Installing
If you are not interested on the versions of the project, you will only need to get [wp_maintenance.php](https://raw.githubusercontent.com/NioniosFr/wp-maintenance/master/src/wp_maintenance.php) file and require it with `wp-cli`.

### Get the project with git
Pull the project's source code from GitHub

```bash
$ git clone https://github.com/NioniosFr/wp-maintenance.git
```

### Get the project with composer
The package is registered on `packagist`, thus you will only have to require this repository in your `composer.json` file as follows:

```json
{
    "require":{
        "nioniosfr/wp-maintenance": "0.1.0"
    }
}
```

# Using
Require `src/wp-maintenance.php` with `wp-cli.phar`
```bash
php wp-cli.phar --require=wp-maintenance/src/wp_maintenance.php
```

The command to use is named `maintenance`.
A bit long to type but keeps things simple and is actually intended to be executed by scripts not humans.

## A verbose usage example 
Assuming you pulled the source code in `~/wp-maintenance` and that you have `wp-cli.phar` globally available in your systems path as the `wp` command, you can use `wp-maintenance` as follows:

```bash
$ cd ~/wp-maintenance/src;
$ wp --require=wp_maintenance.php help maintenace
```

