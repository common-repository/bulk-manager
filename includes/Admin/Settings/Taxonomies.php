<?php

namespace Bulk\Manager\Admin\Settings;

/**
 * Posts class handler.
 */
class Taxonomies extends Page {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id        = 'taxonomies';
		$this->label     = esc_html__( 'Taxonomies', 'bulk-manager' );

		parent::__construct();

        add_action( 'bulk_manager_settings_save_button_' . $this->id, [$this, 'save_button'] );
	}

    public function save_button()
    {
        ?>
        <p class="submit">
            <button class="button-primary bulk-manager-update-taxonomy"><?php esc_html_e( 'Update Taxonomy/Term', 'bulk-manager' ); ?></button>
			<?php wp_nonce_field( 'bulk-manager-update-taxonomy' ); ?>
		</p>
        <?php
    }

	public function get_section_settings(){

		$settings =
			[
				[
					'title' => esc_html__( 'Taxonomy', 'bulk-manager' ),
					'type'  => 'title',
					'desc'  => esc_html__( 'Available all post types with custom post type.', 'bulk-manager' ),
					'id'    => 'bulk_manager_post_types',
				],
				[
					'title'           => esc_html__( 'Post Type', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select post type for which one you need action.', 'bulk-manager' ),
					'id'              => 'bulk_manager_all_post_types_tax',
					'default'         => '',
					'type'            => 'select',
					'show_if_matched' => 'option',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => bulk_manager_get_all_post_types('taxonomies'),
				],
				[
					'title'           => esc_html__('Taxonomy', 'bulk-manager'),
					'desc'            => esc_html__( 'Select taxonomy for which one you need action.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_taxonomy_selected',
					'default'         => '',
					'type'            => 'select',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [],
				],
				[
					'title'           => esc_html__('Terms', 'bulk-manager'),
					'desc'            => esc_html__( 'Select terms for which one you need action.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_taxonomy_terms',
					'default'         => '',
					'type'            => 'select',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [],
				],
				[
					'title'           => esc_html__('Posts', 'bulk-manager'),
					'desc'            => esc_html__( 'Select a specific or multiple product to update/delete.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_posts_selected',
					'default'         => '',
					'type'            => 'multiselect',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [],
				],
				[
					'title'           => esc_html__( 'Action For', 'bulk-manager' ),
					'desc'            => esc_html__('Take action for term/taxonomy or posts by term/taxonomy', 'bulk-manager' ),
					'id'              => 'bulk_manager_taxonomies_term_action_for',
					'default'         => '',
					'type'            => 'select',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [
						'term'           => esc_html__('Term', 'bulk-manager'),
						'taxonomy'       => esc_html__('Taxonomy', 'bulk-manager'),
						'update_post'    => esc_html__('Posts', 'bulk-manager'),
					],
				],
				[
					'title'           => esc_html__( 'Action', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select action which one you want to perform.', 'bulk-manager' ),
					'id'              => 'bulk_manager_action_type',
					'default'         => '',
					'type'            => 'select',
					'show_if_matched' => 'option',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [
						'update_content'    => esc_html__('Update Content', 'bulk-manager'),
						'update_excerpt'    => esc_html__('Update Excerpt', 'bulk-manager'),
						'update_author'     => esc_html__('Update Author', 'bulk-manager'),
						'update_image'      => esc_html__('Update Featured Image', 'bulk-manager'),
						'update_delete'     => esc_html__('Delete', 'bulk-manager'),
						'update_taxonomies' => esc_html__('Update Taxonomies', 'bulk-manager'),
						'update_status'     => esc_html__('Update Status', 'bulk-manager'),
					],
				],
				[
					'title'           => esc_html__( 'Content', 'bulk-manager' ),
					'desc'            => esc_html__( 'Write content which you want to update with the existing.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_content',
					'default'         => '',
					'type'            => 'wp_editor',
					'show_if_matched' => 'yes',
					'css'             => 'width:350px;height:120px;',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Excerpt', 'bulk-manager' ),
					'desc'            => esc_html__( 'Write excerpt text which you want to update with the existing.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_excerpt',
					'default'         => '',
					'type'            => 'textarea',
					'show_if_matched' => 'yes',
					'css'             => 'width:350px;height:80px;',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Author', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select author if you want to move posts from one author to another.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_author',
					'default'         => '',
					'type'            => 'select',
					'show_if_matched' => 'yes',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => bulk_manager_get_authors(),
				],
				[
					'title'           => esc_html__( 'Taxonomies', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select taxonomies which you want to add/remove with/from post.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_taxonomies',
					'default'         => '',
					'type'            => 'select',
					'show_if_matched' => 'yes',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [],
				],
				[
					'title'           => esc_html__( 'Terms', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select terms which you want to add/remove with/from post.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_taxonomies_terms',
					'default'         => '',
					'type'            => 'multiselect',
					'show_if_matched' => 'yes',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [],
				],
				[
					'title'           => esc_html__( 'Status', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select status for post.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_status',
					'default'         => '',
					'type'            => 'select',
					'show_if_matched' => 'yes',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [
						'publish'    => esc_html__('Published', 'bulk-manager'),
						'draft'      => esc_html__('Draft', 'bulk-manager'),
						'pending'    => esc_html__('Pending', 'bulk-manager'),
						'future'     => esc_html__('Scheduled', 'bulk-manager')
					],
				],
				[
					'title'           => esc_html__( 'Scheduled Date', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_scheduled_datetime',
					'default'         => '',
					'type'            => 'datetime',
					'suffix'          => esc_html__('Datetime format is: 2023-12-25 17:40:00', 'bulk-manager'),
					'desc_tip'        => esc_html__('Datetime format is:<br/> 2023-12-25 17:40:00', 'bulk-manager'),
				],
				[
					'title'           => esc_html__( 'Upload Image', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_image',
					'class'           => 'bulk_manager_selected_types_update_image',
					'default'         => '',
					'type'            => 'image_uploader',
					'show_if_matched' => 'yes',
				],
				[
					'title'           => esc_html__( 'Action Type', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select action type for Taxonomy.', 'bulk-manager' ),
					'id'              => 'bulk_manager_taxonomies_taxonomy_action',
					'default'         => '',
					'type'            => 'select',
					'show_if_matched' => 'option',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [
						'add_taxonomy'    => esc_html__('Add Taxonomy', 'bulk-manager'),
						'edit_taxonomy'   => esc_html__('Edit Taxonomy', 'bulk-manager'),
						'delete_taxonomy' => esc_html__('Delete Taxonomy', 'bulk-manager')
					],
				],
				[
					'title'           => esc_html__( 'Singular Label', 'bulk-manager' ),
					'desc'            => esc_html__( 'Singular label for taxonomy.', 'bulk-manager' ),
					'id'              => 'bulk_manager_taxonomies_custom_taxonomy_singular_label',
					'default'         => '',
					'type'            => 'text',
					'css'             => 'width:350px',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Plural Label', 'bulk-manager' ),
					'desc'            => esc_html__( 'Plural label for taxonomy.', 'bulk-manager' ),
					'id'              => 'bulk_manager_taxonomies_custom_taxonomy_plural_label',
					'default'         => '',
					'type'            => 'text',
					'css'             => 'width:350px',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Slug', 'bulk-manager' ),
					'desc'            => esc_html__( 'This is taxonomy slug', 'bulk-manager' ),
					'id'              => 'bulk_manager_taxonomies_custom_taxonomy_slug',
					'default'         => '',
					'type'            => 'text',
					'css'             => 'width:350px',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Action Type', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select action type for action.', 'bulk-manager' ),
					'id'              => 'bulk_manager_taxonomies_term_action',
					'default'         => '',
					'type'            => 'select',
					'show_if_matched' => 'option',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [
						'add_term'       => esc_html__('Add Term', 'bulk-manager'),
						'edit_term'      => esc_html__('Edit Term', 'bulk-manager'),
						'delete_term'    => esc_html__('Delete Term', 'bulk-manager'),
					],
				],
				[
					'title'           => esc_html__( 'Name', 'bulk-manager' ),
					'desc'            => esc_html__( 'Give a name for term.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_term_name',
					'default'         => '',
					'type'            => 'text',
					'css'             => 'width:350px',
					'class'           => 'bulk-manager-terms-update-box',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Slug', 'bulk-manager' ),
					'desc'            => esc_html__( 'Give a slug for term.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_term_slug',
					'default'         => '',
					'type'            => 'text',
					'css'             => 'width:350px',
					'class'           => 'bulk-manager-terms-update-box',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Description', 'bulk-manager' ),
					'desc'            => esc_html__( 'Write description for term.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_term_description',
					'default'         => '',
					'type'            => 'textarea',
					'css'             => 'width:350px;height:80px;',
					'class'           => 'bulk-manager-terms-update-box',
					'desc_tip'        => true
				],
				[
					'type' => 'sectionend',
					'id'   => 'bulk_manager_post_types',
				],
			];

		return apply_filters( 'bulk_manager_posts_settings', $settings );
	}

}
