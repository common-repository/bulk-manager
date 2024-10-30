<?php
/*
* Plugin Name: Bulk Manager
* Plugin URI: https://redq.io
* Description: An easier way to update/delete your pages/posts content, excerpt, categories, tags, taxonomies, author and media at once.
* Version: 1.0.0
* Author: RedQ, Inc
* Author URI: https://redq.io
* Requires at least: 5.0
* Tested up to: 6.4
*
* Text Domain: bulk-manager
* Domain Path: /languages/
*
* Copyright: Â© 2012-2024 RedQ,Inc.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main plugin class
 */
final class Bulk_Manager
{
    /**
     * Plugin version
     * 
     * @var string
     */
    const version = '1.0.0';

    /**
     * contractor
     */
    private function __construct()
    {
        $this->define_constants();

        register_activation_hook(__FILE__, [$this, 'activate']);
        add_action('plugins_loaded', [$this, 'text_domain']);
        add_action('plugins_loaded', [$this, 'init_plugin']);
    }

    /**
     * Initialize singleton instance
     *
     * @return \Bulk_Manager
     */
    public static function init()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('BULK_MANAGER_VERSION', self::version);
        define('BULK_MANAGER_FILE', __FILE__);
        define('BULK_MANAGER_PATH', __DIR__);
        define('BULK_MANAGER_URL', plugins_url('', BULK_MANAGER_FILE));
        define('BULK_MANAGER_ASSETS', BULK_MANAGER_URL . '/assets');
        define('BULK_MANAGER_DIR_PATH', plugin_dir_path(__FILE__));
        define('BULK_MANAGER_DIST', BULK_MANAGER_URL . '/dist');
    }

    /**
     * Plugin information
     *
     * @return void
     */
    public function activate()
    {
        $installer = new Bulk\Manager\Installer();

        $installer->run();
    }

    /**
     * Plugin text-domain
     *
     * @return null
     */
    public function text_domain()
    {
        load_plugin_textdomain('bulk-manager', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Load plugin files
     *
     * @return void
     */
    public function init_plugin()
    {
        new Bulk\Manager\Assets();
        new Bulk\Manager\Ajax();
        new Bulk\Manager\Generator();
        if (is_admin()) {
            new Bulk\Manager\Admin();
        }
    }
}

/**
 * Initialize main plugin
 *
 * @return \BulkManager
 */
function bulk_manager()
{
    return Bulk_Manager::init();
}

bulk_manager();
