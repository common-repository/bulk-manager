<?php

namespace Bulk\Manager\Admin\Settings;

/**
 * Posts class handler.
 */
class Woocommerce extends Page {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id        = 'woocommerce';
		$this->label     = esc_html__( 'WooCommerce', 'bulk-manager' );

		parent::__construct();

        add_action( 'bulk_manager_settings_save_button_' . $this->id, [$this, 'save_button'] );
	}

    public function save_button()
    {
        ?>
        <p class="submit">
            <button class="button-primary bulk-manager-update-post" value="<?php esc_attr_e( 'Save changes', 'bulk-manager' ); ?>"><?php esc_html_e( 'Update Post', 'bulk-manager' ); ?></button>
			<?php wp_nonce_field( 'bulk-manager-update-post' ); ?>
		</p>
        <?php
    }

	public function get_section_settings(){

		$settings =
			[
				[
					'title' => esc_html__( 'WooCommerce Settings', 'bulk-manager' ),
					'type'  => 'title',
					'desc'  => esc_html__( 'Available all post types with custom post type.', 'bulk-manager' ),
					'id'    => 'bulk_manager_post_types',
				],
				[
					'title'           => esc_html__( 'Type', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select type for which one you need action.', 'bulk-manager' ),
					'id'              => 'bulk_manager_all_post_types',
					'default'         => '',
					'type'            => 'select',
					'class'           => 'bulk-manager-post-data-type',
					'css'             => 'min-width: 350px',
					'wrap_class'      => 'hidden-',
					'show_if_matched' => 'option',
					'desc_tip'        => true,
					'options'         => [
						'product'     => esc_html__('Products', 'bulk-manager'),
						'taxonomy'    => esc_html__('Taxonomy', 'bulk-manager'),
					],
				],
				[
					'title'           => esc_html__('Taxonomy', 'bulk-manager'),
					'desc'            => esc_html__( 'Select a specific or multiple product to update/delete.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_taxonomies',
					'default'         => '',
					'type'            => 'select',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [
						'product_cat'     => esc_html__('Product Category', 'bulk-manager'),
						'product_tag'     => esc_html__('Product Tags', 'bulk-manager'),
					],
				],
				[
					'title'           => esc_html__('Terms', 'bulk-manager'),
					'desc'            => esc_html__( 'Select a specific or multiple product to update/delete.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_terms_by_taxonomy',
					'default'         => '',
					'type'            => 'select',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [],
				],
				[
					'title'           => esc_html__('Products', 'bulk-manager'),
					'desc'            => esc_html__( 'Select a specific or multiple product to update/delete.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_posts_selected',
					'default'         => '',
					'type'            => 'multiselect',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [],
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
						'update_content'       => esc_html__('Update Content', 'bulk-manager'),
						'update_excerpt'       => esc_html__('Update Short Description', 'bulk-manager'),
						'update_author'        => esc_html__('Update Author', 'bulk-manager'),
						'update_image'         => esc_html__('Update Featured Image', 'bulk-manager'),
						'update_delete'        => esc_html__('Trash', 'bulk-manager'),
						'update_taxonomies'    => esc_html__('Update Taxonomies', 'bulk-manager'),
						'update_status'        => esc_html__('Update Status', 'bulk-manager'),
						'update_sale_price'    => esc_html__('Update Sale Price', 'bulk-manager'),
						'update_regular_price' => esc_html__('Update Regular Price', 'bulk-manager'),
						'update_stock_qty'     => esc_html__('Update Stock Quantity', 'bulk-manager'),
						'update_upsells'       => esc_html__('Update Upsells', 'bulk-manager'),
						'update_cross_sells'   => esc_html__('Update Cross Sells', 'bulk-manager'),
						'update_stock_status'  => esc_html__('Update Stock Status', 'bulk-manager'),
						'update_weight'        => esc_html__('Update Weight', 'bulk-manager'),
						'update_backorders'    => esc_html__('Update Backorders', 'bulk-manager'),
						'update_sku'           => esc_html__('Update SKU', 'bulk-manager'),
						'update_gallery_image' => esc_html__('Update Product Gallery', 'bulk-manager'),
					],
				],
				[
					'title'           => esc_html__( 'Backorders', 'bulk-manager' ),
					'desc'            => esc_html__( 'If managing stock, this controls whether or not backorders are allowed. If enabled, stock quantity can go below 0.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_backorders',
					'default'         => '',
					'type'            => 'select',
					'show_if_matched' => 'yes',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [
						'no'        => esc_html__('Do not allow', 'bulk-manager'),
						'notify'    => esc_html__('Allow, but notify customer', 'bulk-manager'),
						'yes'       => esc_html__('Allow', 'bulk-manager'),
					],
				],
				[
					'title'           => esc_html__( 'SKU', 'bulk-manager' ),
					'desc'            => esc_html__( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'bulk-manager' ),
					'suffix'          => esc_html__( 'Remeber, SKU can not be update in bulk.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_sku',
					'default'         => '',
					'type'            => 'text',
					'show_if_matched' => 'yes',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Stock Status', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select stock status for product.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_stock_status',
					'default'         => '',
					'type'            => 'select',
					'show_if_matched' => 'yes',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [
						'instock'         => esc_html__('In stock', 'bulk-manager'),
						'outofstock'      => esc_html__('Out of stock', 'bulk-manager'),
						'onbackorder'     => esc_html__('On backorder', 'bulk-manager'),
					],
				],
				[
					'title'           => esc_html__( 'Weight', 'bulk-manager' ),
					'desc'            => esc_html__( 'Enter weight in decimal form.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_weight',
					'default'         => '',
					'type'            => 'text',
					'show_if_matched' => 'yes',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Sale Price', 'bulk-manager' ),
					'desc'            => esc_html__( 'Enter sale price which you want to update with existing.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_sale_price',
					'default'         => '',
					'type'            => 'text',
					'show_if_matched' => 'yes',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Regular Price', 'bulk-manager' ),
					'desc'            => esc_html__( 'Enter regular price which you want to update with existing.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_regular_price',
					'default'         => '',
					'type'            => 'text',
					'show_if_matched' => 'yes',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Stock Quantity', 'bulk-manager' ),
					'desc'            => esc_html__( 'Enter stock quantity which you want to update with existing.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_stock_qty',
					'default'         => '',
					'type'            => 'text',
					'show_if_matched' => 'yes',
					'desc_tip'        => true
				],
				[
					'title'           => esc_html__( 'Upsells', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select upsells product for the selected product.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_upsells',
					'default'         => '',
					'type'            => 'multiselect',
					'show_if_matched' => 'yes',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [],
				],
				[
					'title'           => esc_html__( 'Cross Sells', 'bulk-manager' ),
					'desc'            => esc_html__( 'Select cross sells product for the selected product.', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_cross_sells',
					'default'         => '',
					'type'            => 'multiselect',
					'show_if_matched' => 'yes',
					'class'           => '',
					'css'             => 'min-width: 350px;',
					'desc_tip'        => true,
					'options'         => [],
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
					'default'         => '',
					'type'            => 'image_uploader',
					'show_if_matched' => 'yes',
				],
				[
					'title'           => esc_html__( 'Product Gallery Image', 'bulk-manager' ),
					'id'              => 'bulk_manager_selected_types_update_gallery_image',
					'default'         => '',
					'type'            => 'multi_image_uploader',
					'show_if_matched' => 'yes',
				],
				[
					'type' => 'sectionend',
					'id'   => 'bulk_manager_post_types',
				],
			];

		return apply_filters( 'bulk_manager_posts_settings', $settings );
	}

}
