<?php
namespace NioniosFr\WP_Maintenace;
use WP_CLI\Utils\Utils as Utils;

/**
 * A maintenance object.
 *
 * Implements functionality used by the WP_CLI Command.
 *
 * @author nioniosfr
 * @version 0.1.0
 */
class Maintenance
{

    /**
     * The offest of WordPress maintenance triggering.
     *
     * @var int
     */
    const WP_MNTNCN_RESET = 600;

    /**
     * The file mode to use for the maintenance page and file.
     *
     * @var int
     */
    const FILE_MODE = 644;

    /**
     * Path to the templates folder.
     *
     * @var string
     */
    protected $templates_path;

    /**
     * The maintenance file as defined by WP.
     *
     * @var string
     */
    public $mfile;

    /**
     * The maintenance page as defined by WP.
     *
     * @var string
     */
    public $mpage;

    /**
     * A reference to the WP filesystem object.
     *
     * @var \WP_Filesystem_Direct
     */
    protected $fs;

    /**
     * The full path of the maintenance file's folder.
     *
     * @var string
     */
    protected $maintenance_filePath = ABSPATH;

    /**
     * The WP name for the maintenance file.
     *
     * @var string
     */
    protected $maintenance_file = '.maintenance';

    /**
     * The full path of the maintenance file's folder.
     *
     * @var string
     */
    protected $maintenance_pagePath = WP_PLUGIN_DIR;

    /**
     * The WP name for the maintenance page.
     *
     * @var string
     */
    protected $maintenance_page = 'maintenance.php';

    /**
     * Whether the class instantiated properly.
     *
     * @var boolean
     */
    public $errored = false;

    /**
     * Default constructor.
     *
     * @throws \Exception If the core class could not instantiate properly.
     */
    function __construct ()
    {
        $this->templates_path = dirname(__DIR__) . '/templates/';
        $this->mfile = $this->maintenance_filePath . $this->maintenance_file;
        $this->mpage = $this->maintenance_pagePath . '/' . $this->maintenance_page;
        $this->fs = $this->_get_filesystem();
    }

    protected function _get_filesystem ()
    {
        $corePath = ABSPATH . 'wp-admin/';

        if (is_dir($corePath)) {
            @include_once $corePath . 'includes/class-wp-filesystem-base.php';
            @include_once $corePath . 'includes/class-wp-filesystem-direct.php';
            return new \WP_Filesystem_Direct(null);
        } else {
            throw new \Exception(
                'WordPress core files where not found in:' . $corePath);
        }
    }

    /**
     * Whether the site is in maintenance mode.
     *
     * @return bool
     */
    function is_active ()
    {
        if ($this->fs->is_readable($this->mfile)) {
            // global $upgrading;
            @eval(file_get_contents($this->mfile));
            // Measure it by removing 2 second from the actual WP value to avoid
            // confusion in the response.
            if (isset($upgrading) &&
                     (time() - $upgrading) <= (self::WP_MNTNCN_RESET - 2)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Enable maintenance mode for a given period of time.
     *
     * @param string $time
     *            The period of time to stay on maintenance.
     * @return boolean
     */
    function enable ($time = '10m', $date = 'now')
    {
        return $this->fs->put_contents(
            $this->mfile,
            $this->_generate_mfile_content($time, $date));
    }

    /**
     * Disable maintenance mode
     */
    function disable ()
    {
        if ($this->fs->exists($this->mfile)) {
            return $this->fs->delete($this->mfile);
        }
        return true;
    }

    /**
     * If the maintenance file exist.
     *
     * @return boolean
     */
    function mfile_exists ()
    {
        return $this->fs->exists($this->mfile);
    }

    /**
     * If a custom maintenance page exists
     *
     * @return boolean
     */
    function mpage_exists ()
    {
        return $this->fs->exists($this->mfile);
    }

    /**
     * Get the contents that should be in the maintenance file.
     *
     * @param timestamp $time
     *            The UNIX timestamp to stay in maintenance
     * @param date $date
     *            The date format for when to activate the maintenance mode.
     * @return string The contents of the maintenance file.
     */
    function _generate_mfile_content ($time, $date)
    {
        var_dump($time);
        $activation = $this->_date_activation_in_posix($date);
        $data = array(
                    'date' => date('j F Y H:i:s', time()),
                    'duration' => $this->_user_time_in_posix($time, $date),
                    'activation' => $activation,
                    'userduration' => $time,
                    'activationDate' => date('j F Y H:i:s', $activation)
        );
        $m = new \Mustache_Engine();
        $template = $this->fs->get_contents(
            $this->templates_path . 'maintenance-file.mustache');
        return $m->render($template, $data);
    }

    /**
     * Copy over to WP a custom maintenance page.
     *
     * @param string $path
     * @return boolean
     */
    function new_mpage ($path)
    {
        return $this->fs->copy($path, $this->mpage, true, self::FILE_MODE);
    }

    /**
     * Internal function to determine the interval of time based on
     * a specific string format.
     *
     * @param mixed $time
     * @return double int
     */
    protected function _user_time_in_posix ($time, $date)
    {
        preg_match('/^([0-9]{1,4})(s|m|h){1}$/i', $time, $interval);
        $span = empty($interval[2]) ? 'default' : $interval[2];

        // WordPress checks time to be less than 10 minutes "time() - time() ==
        // 0".

        // Assign starting time based on the WordPress check (10m+ gets
        // invalidated) and a tiny lag of this script.
        $duration = $this->_date_activation_in_posix($date);

        switch ($span) {
            case 's':
            case 'S':
                $duration += $interval[1];
                break;
            case 'm':
            case 'M':
                $duration += ($interval[1] * 60);
                break;
            case 'h':
            case 'H':
                $duration += (($interval[1] * 60) * 60);
                break;
            default:
                break;
        }

        return $duration;
    }

    /**
     * Converts date() formated strings in POSIX timestamp.
     *
     * @param string $date
     */
    protected function _date_activation_in_posix ($date)
    {
        $activation = strtotime($date, (time() - self::WP_MNTNCN_RESET));
        return $activation;
    }
}
