<?php
namespace NioniosFr\WP_Maintenace;
use NioniosFr\WP_Maintenace\Maintenance as Maintenance;
if (defined('\WP_CLI') &&\WP_CLI) {
    $autoload = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' .
             DIRECTORY_SEPARATOR . 'autoload.phps';
    if (file_exists($autoload)) {
        require_once $autoload;
    } else {
        spl_autoload_register(
            function  ($class)
            {
                // project-specific namespace prefix
                $prefix = 'NioniosFr\\WP_Maintenace\\';

                // base directory for the namespace prefix
                $base_dir = __DIR__ . DIRECTORY_SEPARATOR;

                // does the class use the namespace prefix?
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) {
                    // no, move to the next registered autoloader
                    return;
                }

                $relative_class = substr($class, $len);

                $filepath = $base_dir .
                         str_replace('\\', DIRECTORY_SEPARATOR, $relative_class);

                // if the file exists, require it
                if (file_exists($filepath . '.php')) {
                    require $filepath . '.php';
                } elseif (file_exists($filepath . '.class.php')) {
                    require $filepath . '.class.php';
                }
            });
    }
} else {
    exit(1);
}

/**
 * Maintenance manager for wp-cli.
 *
 * Command utilising the internal WordPress maintenance mode
 * and puts your site into maintenance mode.
 *
 * @version 1.1
 * @author nioniosfr
 */
class Maintenance_API_Command extends \WP_CLI_Command
{

    /**
     *
     * @var NioniosFr\WP_Maintenace\Maintenance
     */
    protected $m;
    function __construct ()
    {
        parent::__construct();
        try {
            $this->m = new Maintenance();
        } catch (\Exception $e) {
            \WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Check whether a site is in maintenance mode.
     *
     * ## OPTIONS
     * None
     *
     * ## EXAMPLES
     * wp maintenance is_enabled
     */
    function is_enabled ($args, $assoc_args)
    {
        if ($this->m->is_active()) {
            \WP_CLI::success('Site is in maintenance mode.');
            return true;
        }

        if ($this->m->mfile_exists()) {
            \WP_CLI::warning(
                sprintf(
                    "Site is not in maintenance mode but, there is a `%s` file in: %s",
                    basename($this->m->mfile),
                    dirname($this->m->mfile)));
            return;
        }

        \WP_CLI::error('Site is not in maintenance.');
    }

    /**
     * <pre>
     * Add a site into maintenance mode.
     *
     * ## OPTIONS
     * <duration>
     * : The duration for which the site will remain in maintenance. Defined in
     * seconds minutes or hours: 0-9{1,4}s|m|h
     *
     * [--date]
     * : The date on which the maintenance mode should get activated.
     * NOTE:Setting the maintenance mode to a future value will disable
     * automatic
     * updates.
     *
     * [--page]
     * : A page to use during the maintenance mode.
     *
     * @see http://php.net/manual/en/function.strtotime.php [--page]
     *      : An HTML or PHP page to dispaly during maintenance.
     *
     *      ## EXAMPLES
     *      wp maintenance enable
     *      wp maintenance enable 10m
     *      wp maintenance enable 10m now
     *      wp maintenance enable 10m 05-01-2015
     *      wp maintenance enable 1h --page=/tmp/my-cool-html-file.html
     *
     *      @synopsis <duration> [--date=<date-to-activate-maintenance>]
     *      [--page=<path-to-a-maintenance-file>]
     *
     */
    function enable ($args, $assoc_args)
    {
        if (! empty($assoc_args['date'])) {
            $date = str_replace('/', '-', $assoc_args['date']);
        } else {
            $date = 'now';
        }

        if (strtotime($date) < (time() - 2)) {
            \WP_CLI::error(
                'Invalid activation date. Only future dates are accepted.');
        }

        $this->m->enable($args[0], $date);

        if (! empty($assoc_args['page'])) {
            if (file_exists($assoc_args['page']) &&
                     $this->m->new_mpage($assoc_args['page'])) {
                \WP_CLI::line(
                    'Copied maintenance page in: ' . $this->m->mpage->fullPath);
            } else {
                \WP_CLI::error(
                    escapeshellarg($assoc_args['page']) .
                             ' is not a valid filepath.');
            }
        }

        if ($this->m->is_active()) {
            \WP_CLI::success('Maintenance mode has been enabled.');
        } elseif ($this->m->mfile_exists()) {
            \WP_CLI::success('Maintenance mode has been schedulled.');
        } else {
            \WP_CLI::error('Maintenance mode is not enabled neither schedulled');
        }
    }

    /**
     * Disable a site from maintenance mode
     *
     * ## OPTIONS
     * [--force]
     * : Force the disbale.
     *
     * ## EXAMPLES
     * wp maintenance
     * @synopsis [--force]
     */
    function disable ($args, $assoc_args)
    {
        if ($this->m->is_active()) {
            if ($this->m->disable()) {
                \WP_CLI::success('Maintenance mode has been disabled.');
            } else {
                \WP_CLI::error('Maintenance mode could not be disabled.');
            }
        } else {
            // Run the disable if requested.
            if (isset($assoc_args['force'])) {
                if ($this->m->disable()) {
                    \WP_CLI::line('Forced the disable command..');
                }
            }
            \WP_CLI::line(
                'Maintenance mode is not enabled. Nothing to do here..');
        }
    }
}

\WP_CLI::add_command(
    'maintenance',
    'NioniosFr\WP_Maintenace\Maintenance_API_Command');
