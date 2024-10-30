<?php

namespace  Bulk\Manager;

/**
 * Generator class
 * 
 * @description: Extended CPTs is a library which provides extended functionality to WordPress custom post types and taxonomies. 
 * This allows developers to quickly build post types and taxonomies without having to write the same code again and again.
 * 
 * @api https://github.com/johnbillion/extended-cpts
 */
class Generator
{
    protected $registered_taxonomies = [];

    /**
     * Class initialize
     */
    function __construct()
    {
        $this->registered_taxonomies = get_option('bulk_manager_register_taxonomies', []);
        add_action('init', [$this, 'init_generator']);
    }

    public function init_generator()
    {
        foreach ($this->registered_taxonomies as $slug => $registered_taxonomoy) {
            register_extended_taxonomy( 
                $slug,
                $registered_taxonomoy['post'],
                [
                    'meta_box'         => 'simple',
                    'dashboard_glance' => true,
                    'admin_cols' => [
                        'updated' => [
                            'title'       => esc_html__('Updated', 'bulk-manager'),
                            'meta_key'    => 'updated_date'
                        ]
                    ]
                ], 
                [
                    'singular' => esc_html($registered_taxonomoy['singluarLabel']),
                    'plural'   => esc_html($registered_taxonomoy['pluralLabel']),
                    'slug'     => $registered_taxonomoy['post'] . '-' . $slug
                ]
            );
        }
    }

}
