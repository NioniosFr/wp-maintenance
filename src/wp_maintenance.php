<?php
// Plugin Name: WordPress maintenance.
// Cygwin Usage: wp require=`cygpath -wa ./src/wp_maintenance.php`

if ( defined('WP_CLI') && WP_CLI ) {
    require_once dirname(__DIR__). DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
    define(CURLOPT_SSL_VERIFYPEER, false);
}else{
    exit(1);
}

/**
 * Maintenance managere for wp-cli.
 *
 * Command utilising the internal WordPress maintenance mode
 * and puts your site into maintenance mode.
 *
 * @version 1.0
 * @author qfry
 */
class Maintenance_Command extends WP_CLI_Command
{
    protected $maintenance_filePath = ABSPATH;
    protected $maintenance_file = '.maintenance';
    protected $maintenance_pagePath = WP_PLUGIN_DIR;
    protected $maintenance_page = 'maintenance.php';

    /**
     * Check whether a site is in maintenance mode.
     * 
     * ## OPTIONS
     *  None
     *  
     * ## EXAMPLES
     *      wp maintenance is_enabled
     */
    function is_enabled(){
        if (is_readable(ABSPATH . '.maintenance')){
            WP_CLI::success('Site is in maintenance mode.');
        }else{
            WP_CLI::error('Site is not in maintenance.');
        }
    }

    /**
     * Add a site into maintenance mode.
     * 
     * ## OPTIONS
     *  [time]
     *  : The interval in which the site should remain in maintenance.
     *  
     *  [page]
     *  : An HTML page to use as the maintenance page.
     *  
     * ## EXAMPLES
     *  wp maintenance enable
     *  wp maintenance enable --time=10m
     *  wp maintenance enable --time=1h --page=/tmp/my-cool-html-file.html
     *  
     * @synopsis [--time=<time>] [--page=<path-to-html-file>]
     */
    function enable(){
        $file = $this->maintenance_filePath . $this->maintenance_file;
        $contents = '<?php $$upgrading = time(); ?>';
        
        file_put_contents($file, $contents);
    }

    /**
     * Disable a site from maintenance mode
     * 
     * ## OPTIONS
     *  None
     * ## EXAMPLES
     *  wp maintenance
     */
    function disable(){
        $filePath = $this->maintenance_filePath . $this->maintenance_file;
        if(file_exists($filePath)){
            @unlink($filePath);
            WP_CLI::success('Maintenance mode has been disabled.');
        }else{
            WP_CLI::warning('Maintenance mode is not enabled.');
        }
    }

}
WP_CLI::add_command( 'maintenance', 'Maintenance_Command' );
