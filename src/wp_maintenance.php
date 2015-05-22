<?php
// Plugin Name: WordPress maintenance.
// Cygwin Usage: wp require=`cygpath -wa ./src/wp_maintenance.php`

if ( defined('WP_CLI') && WP_CLI ) {
    $autoload = dirname(__DIR__). DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
    if (file_exists($autoload)){
        require_once $autoload;
    }
    // define(CURLOPT_SSL_VERIFYPEER, false);
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
class Maintenance_Command extends \WP_CLI_Command
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
    function is_enabled($args, $assoc_args){
        $filePath = $this->maintenance_filePath . $this->maintenance_file;

        if (is_readable($filePath)) {
            include_once($filePath);
            // Measure it by removing 2 second from the actual WP value to avoid confusion in the response.
            if ((time() - $upgrading) <= 598 ){
                WP_CLI::success('Site is in maintenance mode.');
            }else{
                WP_CLI::warning("Site is not in maintenance mode but, there is a $this->maintenance_file file in:  $this->maintenance_filePath");
                return;
            }
        }

        WP_CLI::error('Site is not in maintenance.');
    }

    /**
     * Add a site into maintenance mode.
     * 
     * ## OPTIONS
     *  [time]
     *  : The interval in which the site should remain in maintenance defined in second minutes or hours.: <number><s|m|h>
     *  
     *  [page]
     *  : An HTML page to use as the maintenance page.
     *  
     * ## EXAMPLES
     *  wp maintenance enable
     *  wp maintenance enable --time=10m
     *  wp maintenance enable --time=1h --page=/tmp/my-cool-html-file.html
     *  
     * @synopsis [--time=<interval>] [--page=<path-to-a-maintenance-file>]
     */
    function enable($args, $assoc_args){
        $file = $this->maintenance_filePath . $this->maintenance_file;

        if(!empty($assoc_args['time'])){
            $time = $this->user_time_in_posix($assoc_args['time']);
        }

        // WordPress checks time to be less than 10 minutes "time() - time() == 0".
        $contents = empty($time) ? '<?php $upgrading = time(); ?>' : "<?php /* Time: ${assoc_args['time']} */ \$upgrading = $time; ?>";
        @file_put_contents($file, $contents);

        if(!empty($assoc_args['page'])){
            if(file_exists($assoc_args['page'])){
                @copy($assoc_args['page'], $this->maintenance_pagePath.$this->maintenance_page);
            }else{
                WP_CLI::error(escapeshellarg($assoc_args['page']) .' is not a valid filepath.');
            }
        }
        if (file_exists($file)){
            WP_CLI::success('Maintenance mode has been enabled.');
        }else{
            WP_CLI::error('Maintenance could not be enabled.');
        }
    }

    /**
     * Internal function to determine the interval of time based on
     * a specific string format.
     *
     * @param mixed $time
     * @return array|double|int
     */
    protected function user_time_in_posix($time)
    {
        preg_match('/^([0-9]{1,4})(s|m|h){1}$/i', $time, $interval);
        $span = empty($interval[2]) ? 'default' : $interval[2];
        $time = null;

        switch($span){
            case 's':
            case 'S':
                $time = time() + ($interval[1]);
                break;
            case 'm':
            case 'M':
                $time = time() + ($interval[1] * 60);
                break;
            case 'h':
            case 'H':
                $time = time() + ($interval[1] * 60 )*60;
                break;
            default:
                $time = time();
                break;
        }

        $time -= 600;
        return $time;
    }

    /**
     * Disable a site from maintenance mode
     * 
     * ## OPTIONS
     *  None
     * ## EXAMPLES
     *  wp maintenance
     */
    function disable($args, $assoc_args){
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
