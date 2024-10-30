<?php

namespace Bulk\Manager;

/**
 * Assets class handler
 */
class Assets
{
    /**
     * Initialize assets
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('admin_enqueue_scripts', [$this, 'register_assets']);
    }

    /**
     * Bulk Manager scripts
     *
     * @return array
     */
    public function get_scripts()
    {
        return [
            'jquery-tipTip' => [
                'src'     => BULK_MANAGER_ASSETS . '/js/jquery.tipTip.min.js',
                'version' => filemtime(BULK_MANAGER_PATH . '/assets/js/jquery.tipTip.min.js'),
                'deps'    => ['jquery']
            ],
            'bulk-manager-admin' => [
                'src'     => BULK_MANAGER_ASSETS . '/js/admin.js',
                'version' => filemtime(BULK_MANAGER_PATH . '/assets/js/admin.js'),
                'deps'    => ['jquery']
            ]
        ];
    }

    /**
     * Bulk Manager styles
     *
     * @return array
     */
    public function get_styles()
    {
        return [
            'bulk-manager-global' => [
                'src'     => BULK_MANAGER_DIST . '/bulk-global.css',
                'version' => filemtime(BULK_MANAGER_PATH . '/dist/bulk-global.css'),
            ],
            'bulk-manager-tailwind' => [
                'src'     => BULK_MANAGER_DIST . '/bulk-tailwind.css',
                'version' => filemtime(BULK_MANAGER_PATH . '/dist/bulk-tailwind.css'),
            ],
        ];
    }

    /**
     * Register assets
     */
    public function register_assets()
    {
        $scripts = $this->get_scripts();
        $styles = $this->get_styles();

        foreach ($scripts as $handle => $script) {
            $deps = isset($script['deps']) ? $script['deps'] : false;
            $version = isset($script['version']) ? $script['version'] : BULK_MANAGER_VERSION;

            wp_register_script($handle, $script['src'], $deps, $version, true);
        }

        foreach ($styles as $handle => $style) {
            $deps = isset($style['deps']) ? $style['deps'] : false;
            $version = isset($style['version']) ? $style['version'] : BULK_MANAGER_VERSION;

            wp_register_style($handle, $style['src'], $deps, $version);
        }

        if (is_admin()) {
            if ( ! did_action( 'wp_enqueue_media' ) ) {
                wp_enqueue_media();
            }

            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable');

            wp_enqueue_script('jquery-tipTip');
            wp_enqueue_script('bulk-manager-admin');
            wp_enqueue_style('bulk-manager-tailwind');
            wp_enqueue_style('bulk-manager-global');

            $gallery_images = bulk_manager_get_gallery_images();
            $security       = wp_create_nonce( "bulk_manager_ajax_nonce" );
            wp_localize_script('bulk-manager-admin', 'BULK_MANAGER_ADMIN', [
                'ajax_url'       => admin_url('admin-ajax.php'),
                'gallery_images' => $gallery_images,
                'security'       => $security,
                'insert_btn'     => esc_html__('Insert image', 'bulk-manager'),
                'gallery_btn'    => esc_html__('Set gallery image', 'bulk-manager'),
            ]);
        }
    }
}
