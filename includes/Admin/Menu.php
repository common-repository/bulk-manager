<?php

namespace Bulk\Manager\Admin;

/**
 * Admin menu class
 */
class Menu
{
    /**
     * Initialize menu
     */
    function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action( 'wp_loaded', [$this, 'save_settings'] );
    }

    /**
     * Handle plugin menu
     *
     * @return void
     */
    public function admin_menu()
    {
        $parent_slug = 'bulk-manager-dashboard';
        $capability = 'manage_options';

        add_menu_page(esc_html__('Bulk Manager Dashboard', 'bulk-manager'), esc_html__('Bulk Manager', 'bulk-manager'), $capability, $parent_slug, [$this, 'dashboard_page'], 'dashicons-buddicons-groups');
        $settings_page = add_submenu_page($parent_slug, esc_html__('Settings', 'bulk-manager'), esc_html__('Settings', 'bulk-manager'), $capability, $parent_slug, [$this, 'dashboard_page']);

        add_action( 'load-' . $settings_page, [ $this, 'init_settings_page' ] );

    }

    /**
     * Handle menu page
     *
     * @return void
     */
    public function dashboard_page()
    {
        Settings::output();
    }

    /**
     * Load pages
     *
     * @return void
     */
    public function init_settings_page()
    {
        // Set all post types
		Settings::set_all_post_types();

        // Include pages.
		Settings::get_pages();
    }

	/**
	 * Handle saving of settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		global $active_tab;

		if ( ! is_admin() || ! isset( $_GET['page'] ) || 'bulk-manager-dashboard' !== $_GET['page'] ) {
			return;
		}

		Settings::get_pages();

		$active_tab = empty( $_GET['tab'] ) ? 'posts_types' : bulk_manager_clean( wp_unslash( $_GET['tab'] ) );

        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['_wpnonce'] ) ) , 'bulk-manager-save-settings' ) ) return;
        
        if ( '' !== $active_tab && apply_filters( "bulk_manager_settings_save_{$active_tab}", ! empty( $_POST['save'] ) ) ) {
            Settings::save();
        }
	}
}
