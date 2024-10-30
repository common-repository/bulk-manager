<?php

namespace Bulk\Manager\Admin\Settings;

use Bulk\Manager\Admin\Settings;

/**
 * SettingsPage class handler.
 */
abstract class Page {

    protected $id        = '';
    protected $label     = '';
    protected $tab_label = '';

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'admin_settings_tabs', array( $this, 'add_tab' ), 20 );
        add_action( 'bulk_manager_settings_' . $this->id, [$this, 'output'] );
        add_action( 'bulk_manager_settings_save_button_' . $this->id, [$this, 'save_button'] );
        add_action( 'bulk_manager_settings_save_' . $this->id, [$this, 'save'] );
    }

    /**
     * Add tab to settings.
     */
    public function add_tab( $tabs ) {
        $tabs[ $this->id ] = [
            'label'     => $this->label,
            'tab_label' => $this->tab_label,
        ];

        return $tabs;
    }

    /**
     * Get settings
     *
     * @return void
     */
    public function get_settings(){
        return $this->get_section_settings();
    }

    /**
     * Output settings control
     */
    public function output() {
        $settings = $this->get_settings();
        Settings::output_fields( $settings );
    }

    /**
     * Output settings control
     */
    public function save_button() {
        Settings::save_button();
    }

    /**
     * Save settings
     */
    public function save() {
        $settings = $this->get_settings();
        Settings::save_fields( $settings );
    }
}
