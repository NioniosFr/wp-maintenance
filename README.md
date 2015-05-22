# wp_maintenance

A [wp-cli](http://wp-cli.org/) command to manage a [WordPress](https://wordpress.org) sites maintenance mode interval and the displayed maintenace page.

## Depends on

* wp-cli

### Get the project with git
Pull the project's source code from GitHub

```bash
$ git clone https://github.com/NioniosFr/wp_maintenance.git
```

### Get the project with composer

The package is not yet registered on `packagist` thus you will have to define this repository in your `composer.json` file as follows:

```json
{
    "repositories": [
    {
        "url": "https://github.com/NioniosFr/wp_maintenance.git",
            "type": "git"
    }
    ],
    "require": {
        "nioniosfr/wp-maintenance": "master"
    }
}
```

# Using
Require `src/wp_maintenance.php` with `wp-cli.phar`
```bash
php wp-cli.phar --require=wp_maintenance/src/wp_maintenance.php
```

The command to use is named `maintenance`.
A bit long to type but keeps things simple and is actually intended to be executed by scripts not humans.

## A verbose usage example 
Assuming you pulled the source code in `~/wp-maintenance` and that you have `wp-cli.phar` globally available in your systems path as the `wp` command, you can use `wp_maintenance` as follows:

```bash
$ cd ~/wp-maintenance/src;
$ wp --require=wp_maintenance.php help maintenace
```

