<?php

/**
 * Get Upsell Product IDS
 *
 * @param int $product_id
 * @param int $no_of_ids
 * @return array
 */
function bulk_manager_get_promotional_product_ids($product_id, $no_of_ids = 4)
{
    if (empty($product_id)) {
        return false;
    }

    $post_ids = bulk_manager_get_default_ids($product_id);

    if (empty($post_ids)) {
        return false;
    }

    if (count($post_ids) <= $no_of_ids) {
        $no_of_ids = count($no_of_ids);
    }

    $results = bulk_manager_array_random($post_ids, $no_of_ids);

    return is_array($results) ? $results : [$results];
}

/**
 * Get product category terms
 *
 * @param string $taxonomy
 * @return array
 */
function bulk_manager_product_terms($taxonomy = 'product_cat')
{
    $categories = get_terms(['taxonomy' => 'product_cat']);

    return $categories;
}

/**
 * Get product id's by term id
 *
 * @param int $term_id
 * @return array
 */
function bulk_manager_get_products_by_terms($term_id)
{
    if (empty($term_id)) {
        return false;
    }

    $posts_per_page = -1;

    $tax_query = [
        [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => $term_id
        ]
    ];

    $args = [
        'post_type'        => 'product',
        'post_status'      => 'publish',
        'posts_per_page'   => $posts_per_page,
        'fields'           => 'ids',
        'suppress_filters' => true,
        'tax_query'        => $tax_query
    ];

    $query = new WP_Query($args);
    $count = count($query->posts);

    $post_ids = $query->posts;

    return $post_ids;
}

/**
 * Get related product id's
 *
 * @param int $product_id
 * @param integer $no_of_ids
 * @return array
 */
function bulk_manager_get_related_ids($product_id, $no_of_ids = 4)
{
    if (empty($product_id)) {
        return false;
    }

    $posts_per_page = -1;
    $product_cats_ids = wc_get_product_term_ids($product_id, 'product_cat');

    $tax_query = [
        [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => $product_cats_ids
        ]
    ];

    if (empty($product_cats_ids)) {
        $posts_per_page = 10;
        $tax_query = false;
    }

    $args = [
        'post_type'        => 'product',
        'post_status'      => 'publish',
        'posts_per_page'   => $posts_per_page,
        'post__not_in'     => [$product_id],
        'fields'           => 'ids',
        'suppress_filters' => true,
        'tax_query'        => $tax_query
    ];

    $query = new WP_Query($args);
    $count = count($query->posts);

    $post_ids = $query->posts;

    if ($count < $no_of_ids) {
        $default_ids = bulk_manager_get_default_ids($product_id);
        $post_ids = array_merge($post_ids, $default_ids);
    }

    $ids = bulk_manager_array_random($post_ids, $no_of_ids);
    $ids = is_array($ids) ? $ids : [$ids];

    return $ids;
}

/**
 * Get products query
 *
 * @param array $product_id
 * @param integer $posts_per_page
 * @return object
 */
function bulk_manager_get_default_ids($product_id = [], $posts_per_page = 10)
{
    $args = [
        'post_type'        => 'product',
        'posts_per_page'   => $posts_per_page,
        'post_status'      => 'publish',
        'post__not_in'     => [$product_id],
        'fields'           => 'ids',
        'suppress_filters' => true,
    ];

    $query = new WP_Query($args);

    return $query->posts;
}

/**
 * Random array generator
 *
 * @param array $arr
 * @param integer $num
 * @return array
 */
function bulk_manager_array_random($arr, $num = 1)
{
    shuffle($arr);

    $r = array();
    for ($i = 0; $i < $num; $i++) {
        $r[] = $arr[$i];
    }

    return $num == 1 ? $r[0] : $r;
}

/**
 * Get gallery images
 *
 * @return void
 */
function bulk_manager_get_gallery_images() {
    $settings       = get_option('bulk_manager_page_settings');
    $gallery_images = isset($settings['bulk_manager_gallery_images']) ? $settings['bulk_manager_gallery_images'] : [];

    $srcs = [];
    if( !empty($gallery_images) && !empty($gallery_images[0])) {
        foreach ($gallery_images as $key => $imageId) {
            $srcs[] = [
                'url' => wp_get_attachment_image_src($imageId)[0],
                'id'  => $imageId
            ];
        }
    }
    return $srcs;
}

/**
 * Display a help tip.
 *
 * @param  string $tip.
 * @param  bool   $allow_html
 * @return string
 */
function bulk_manager_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$sanitized_tip = bulk_manager_sanitize_tooltip( $tip );
	} else {
		$sanitized_tip = esc_attr( $tip );
	}

	return apply_filters( 'bulk_manager_help_tip', '<span class="bulk-manager-help-tip" tabindex="0" aria-label="' . $sanitized_tip . '" data-tip="' . $sanitized_tip . '"></span>', $sanitized_tip, $tip, $allow_html );
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * @param  string $var
 * @return string
 */
function bulk_manager_sanitize_tooltip( $var ) {
	return htmlspecialchars(
		wp_kses(
			html_entity_decode( $var ?? '' ),
			array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'small'  => array(),
				'span'   => array(),
				'ul'     => array(),
				'li'     => array(),
				'ol'     => array(),
				'p'      => array(),
			)
		)
	);
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 * @return string|array
 */
function bulk_manager_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'bulk_manager_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Parse a relative date option from the settings API into a standard format.
 *
 * @since 3.4.0
 * @param mixed $raw_value
 * @return array
 */
function bulk_manager_parse_relative_date_option( $raw_value ) {
	$periods = array(
		'days'   => esc_html__( 'Day(s)', 'bulk-manager' ),
		'weeks'  => esc_html__( 'Week(s)', 'bulk-manager' ),
		'months' => esc_html__( 'Month(s)', 'bulk-manager' ),
		'years'  => esc_html__( 'Year(s)', 'bulk-manager' ),
	);

	$value = wp_parse_args(
		(array) $raw_value,
		array(
			'number' => '',
			'unit'   => 'days',
		)
	);

	$value['number'] = ! empty( $value['number'] ) ? absint( $value['number'] ) : '';

	if ( ! in_array( $value['unit'], array_keys( $periods ), true ) ) {
		$value['unit'] = 'days';
	}

	return $value;
}

/**
 * Set post types
 *
 * @return void
 */
function bulk_manager_set_all_post_types(){
    $args = [
        'public'   => true,
        '_builtin' => false
    ];

    $output             = 'objects';
    $operator           = 'and';
    $post_types_objects = get_post_types( $args, $output, $operator );
    unset($post_types_objects['e-landing-page']);
    unset($post_types_objects['elementor_library']);

    $all_post_types = get_option( 'bulk_manager_opt_all_post_types', [] );
    unset($all_post_types['post']);
    unset($all_post_types['page']);

    if(
        (
        !empty(array_keys($post_types_objects)) &&
        !empty(array_keys($all_post_types))
        ) &&
        (array_keys($post_types_objects) === array_keys($all_post_types))
    ){
        return;
    }

    $post_types = [
        'post' => esc_html__('Post', 'bulk-manager'),
        'page' => esc_html__('Page', 'bulk-manager'),
    ];

    foreach ($post_types_objects as $key => $post_type) {
        $post_types[$post_type->name] = $post_type->label;
    }

    update_option('bulk_manager_opt_all_post_types', $post_types);
}

/**
 * Get all post types
 *
 * @param string $tab
 * @return array
 */
function bulk_manager_get_all_post_types($tab = 'post_types')
{
    $posts_types = get_option( 'bulk_manager_opt_all_post_types', [] );
    if('taxonomies' == $tab){
        unset($posts_types['page']);
        unset($posts_types['product']);
    }
    if('post_types' == $tab){
        unset($posts_types['product']);
    }
    if('woocommerce' == $tab){
        $posts_types = ['product'];
    }
    return $posts_types;
}

/**
 * Get authors
 *
 * @return array
 */
function bulk_manager_get_authors()
{
    $users   = get_users();
    $authors = [];
    foreach ($users as $user) 
    {
        $authors[$user->ID] = $user->display_name;
    }
    return $authors;
}

/**
 * Get Categories
 *
 * @return array
 */
function bulk_manager_get_categories()
{
    $args = array(
        'hide_empty' => false,
    );
    $cats = [];
    $terms = get_categories($args);
    foreach ($terms as $key => $term) {
        $cats[$term->term_id] = $term->name;
    }
    return $cats;
}

/**
 * Get tags
 *
 * @return void
 */
function bulk_manager_get_tags()
{
    $args = array(
        'hide_empty' => false,
    );
    $tags = [];
    $terms = get_tags($args);
    foreach ($terms as $key => $term) {
        $tags[$term->term_id] = $term->name;
    }
    return $tags;
}

/**
 * Fet taxonomies
 *
 * @param string $post_type
 * @return array
 */
function bulk_manager_get_taxonomies_by_post($post_type = 'post')
{
    $args = array(
        'object_type' => array( $post_type ),
        'public'      => true,
        'show_ui'     => true,
    );
    $taxonomies = get_taxonomies( $args, 'objects' );
    $data = [];
    foreach ($taxonomies as $key => $taxonomy) {
        $data[$taxonomy->name] = $taxonomy->label;
    }
    return $data;
}

/**
 * Update posts default field comes from wordpress
 *
 * @param int $id
 * @param int|sting|array|object $payload
 * @return boolean
 */
function bulk_manager_update_post_defaults( $post_id, $postdata = [] )
{
    $data['ID'] = $post_id;
    $data       = wp_parse_args( $postdata, $data );

    $result = wp_update_post( $data );

    if(is_wp_error($result)){
        return false;
    }

    return true;
}

/**
 * Get product by selection from dropdown
 *
 * @param int $product_id
 * @param int $stock_qty
 * @return boolean
 */
function bulk_manager_get_items_by_selection_type( $posts, $post_type, $args = [] )
{

    if( ( $posts == null ) || ( ( count( $posts ) == 1 ) && in_array( 0, $posts ) ) ) {
        $query_args = array(
            'fields'         => 'ids',
            'posts_per_page' => -1,
            'post_type'      => $post_type,
        );

        if(!empty($args)){
            $query_args['tax_query'] = [
                'relation' => 'AND',
                [
                    'taxonomy' => array_key_first($args),
                    'field'    => 'slug',
                    'terms'    => reset($args)
                ]
            ];
        }

        $query = new WP_Query( $query_args );
        $posts = $query->posts;
    }
    return $posts;
}

/**
 * Set stock quantity by product id
 *
 * @param int $product_id
 * @param int $stock_qty
 * @return boolean
 */
function bulk_manager_update_stock_qty( $product_id, $stock_qty )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $product->set_manage_stock( true );
    $product->set_stock_quantity( $stock_qty );
    return $product->save();
}

/**
 * Set sale price
 *
 * @param int $product_id
 * @param int|float $sale_price
 * @return boolean
 */
function bulk_manager_update_sale_price( $product_id, $sale_price )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $regular_price = $product->get_regular_price();
    if( $sale_price > $regular_price ) return false;

    $product->set_sale_price( $sale_price );
    return $product->save();
}

/**
 * Set regular price
 *
 * @param int $product_id
 * @param int|float $regular_price
 * @return boolean
 */
function bulk_manager_update_regular_price( $product_id, $regular_price )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $product->set_regular_price( $regular_price );
    return $product->save();
}

/**
 * Set upsell products
 *
 * @param int $product_id
 * @param array $upsell_ids
 * @return boolean
 */
function bulk_manager_update_upsell( $product_id, $upsell_ids )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $product->set_upsell_ids( $upsell_ids );
    return $product->save();
}

/**
 * Set cross sells products
 *
 * @param int $product_id
 * @param array $cross_sells_ids
 * @return boolean
 */
function bulk_manager_update_cross_sells( $product_id, $cross_sells_ids )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $product->set_cross_sell_ids( $cross_sells_ids );
    return $product->save();
}

/**
 * Set stock status for product
 *
 * @param int $product_id
 * @param array $cross_sells_ids
 * @return boolean
 */
function bulk_manager_update_stock_status( $product_id, $stock_status )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $product->set_manage_stock( false );
    $product->set_stock_status( $stock_status );
    return $product->save();
}

/**
 * Set update weight for product
 *
 * @param int $product_id
 * @param array $cross_sells_ids
 * @return boolean
 */
function bulk_manager_update_weight( $product_id, $weight )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $product->set_weight( $weight );
    return $product->save();
}

/**
 * Set sku for product
 *
 * @param int $product_id
 * @param string $sku
 * @return boolean
 */
function bulk_manager_update_sku( $product_id, $sku )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $product->set_sku( $sku );
    return $product->save();
}

/**
 * Set backorders for product
 *
 * @param int $product_id
 * @param string $sku
 * @return boolean
 */
function bulk_manager_update_backorders( $product_id, $backorders )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $product->set_manage_stock( true );
    $product->set_backorders( $backorders );
    return $product->save();
}

/**
 * Set content for post/product/custom posts type
 *
 * @param int $product_id
 * @param string $content
 * @return boolean
 */
function bulk_manager_update_content( $product_id, $content )
{
    $data['post_content'] = $content;
    return bulk_manager_update_post_defaults( $product_id, $data );
}

/**
 * Set excerpt for post/product/custom posts type
 *
 * @param int $product_id
 * @param string $content
 * @return boolean
 */
function bulk_manager_update_excerpt( $product_id, $excerpt )
{
    $data['post_excerpt'] = $excerpt;
    return bulk_manager_update_post_defaults( $product_id, $data );
}

/**
 * Set post_author for post/product/custom posts type
 *
 * @param int $product_id
 * @param string $content
 * @return boolean
 */
function bulk_manager_update_author( $product_id, $post_author )
{
    $data['post_author'] = $post_author;
    return bulk_manager_update_post_defaults( $product_id, $data );
}

/**
 * Set featured image for post/product/custom posts type
 *
 * @param int $product_id
 * @param int $image_id
 * @return boolean
 */
function bulk_manager_update_image( $product_id, $image_id )
{
    $result = set_post_thumbnail($product_id, $image_id);

    if(is_wp_error($result)){
        return false;
    }
    return true;
}

/**
 * Set product gallery image
 *
 * @param int $product_id
 * @param array $image_ids
 * @return boolean
 */
function bulk_manager_update_gallery_image( $product_id, $image_ids )
{
    $product = wc_get_product( $product_id );
    if ( ! $product ) return false;

    $product->set_gallery_image_ids( $image_ids );
    return $product->save();
}

/**
 * Set status for post/product/custom posts type
 *
 * @param int $post_id
 * @param array $postdata [status, time for secheduled post e.ge format 2023-12-25 17:40:00] 
 * @return boolean
 */
function bulk_manager_update_status( $post_id, $postdata = [] )
{
    $status = $postdata[0];
    $time   = $postdata[1];
    $data['post_status']   = $status;
    if(('future' == $status) || ('publish' == $status)){
        $time = ('publish' == $status) ? 'now' : $time;
        $time = strtotime( $time );
        $data['post_date']     = date( 'Y-m-d H:i:s', $time );
        $data['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $time );
    }

    return bulk_manager_update_post_defaults( $post_id, $data );
}

/**
 * Process selected terms data for post|product tags
 *
 * @param string $taxonomy
 * @param array $terms
 * @return array $new_terms array of terms name
 */
function bulk_manager_process_selected_terms( $taxonomy, $terms )
{
    $all_terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
    ] );

    $new_terms = [];
    foreach ($all_terms as $key => $all_term) {
        if(in_array($all_term->term_id, $terms)){
            $new_terms[] = $all_term->name;
        }
    }

    return $new_terms;
}

/**
 * Set taxonomies for post/product/custom posts type
 *
 * @param int $post_id
 * @param array $postdata [taxonomy, terms[]] 
 * @return boolean
 */
function bulk_manager_update_taxonomies( $post_id, $postdata = [] )
{
    $taxonomy = $postdata[0];
    $terms    = $postdata[1];

    if(('post_tag' == $taxonomy) || ('product_tag' == $taxonomy)){
        // post_tag & product_tag should be array of name to get updated
        $new_terms = bulk_manager_process_selected_terms( $taxonomy, $terms );

        if('product_tag' == $taxonomy){
            $result = wp_set_post_terms( $post_id, $new_terms, $taxonomy );
        }else{
            $result = wp_set_post_tags( $post_id, $new_terms);
        }

    }else{
        // others should be array of id's to get updated
        $result = wp_set_post_terms( $post_id, $terms, $taxonomy );
    }

    if(is_wp_error($result)){
        return false;
    }

    return true;
}

/**
 * Trash post by id
 *
 * @param int $post_id
 * @param string $postdata
 * @return boolean
 */
function bulk_manager_update_delete( $post_id, $postdata )
{
    $result = wp_trash_post($post_id);

    if(is_wp_error($result)){
        return false;
    }

    return true;
}

/**
 * Update post/page/product data
 *
 * @param array $products
 * @param string $action
 * @param string|int|array $payload
 * @return void
 */
function bulk_manager_update_data( $products, $action, $payload = '' )
{
    if( ! $payload ) {
        return false;
    }

    $callback = 'bulk_manager_' . $action;

    $success = true;
    foreach ($products as $product_id) {
        $result = call_user_func( $callback, $product_id, $payload );
        if( ! $result ){
            $success = false;
            break;
        }
    }
    return $success;
}

/**
 * Get content by post id
 *
 * @param int $post_id
 * @return string
 */
function bulk_manager_get_content( $post_id )
{
    return apply_filters('the_content', get_post_field('post_content', $post_id));
}

/**
 * Get excerpt
 *
 * @param int $post_id
 * @return string
 */
function bulk_manager_get_excerpt( $post_id )
{
    return get_the_excerpt($post_id);
}

/**
 * Get author by post id
 *
 * @param int $post_id
 * @return string
 */
function bulk_manager_get_author( $post_id )
{
    return get_post_field( 'post_author', $post_id );
}

/**
 * Get status by post id
 *
 * @param int $post_id
 * @return string
 */
function bulk_manager_get_status( $post_id )
{
    return get_post_status($post_id);
}

/**
 * Get post image by id
 *
 * @param int $post_id
 * @return string
 */
function bulk_manager_get_image( $post_id )
{
    $images[] = [
        'id' => get_post_thumbnail_id($post_id),
        'url' => get_the_post_thumbnail_url($post_id)
    ];
    return $images;
}

/**
 * Get taxonomies by id
 *
 * @param int $post_id
 * @param string $taxonomy
 * @return string
 */
function bulk_manager_get_taxonomies( $post_id, $taxonomy )
{
    $terms = get_the_terms($post_id, $taxonomy);
    if(!empty($terms)){
        $terms = array_column($terms, 'term_id');
    }
    return $terms;
}

/**
 * Get sale price by id
 *
 * @param int $post_id
 * @return int|float
 */
function bulk_manager_get_sale_price( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    return $product->get_sale_price();
}

/**
 * Get regular price by id
 *
 * @param int $post_id
 * @return int|float
 */
function bulk_manager_get_regular_price( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    return $product->get_regular_price();
}

/**
 * Get stock quantity by id
 *
 * @param int $post_id
 * @return int
 */
function bulk_manager_get_stock_qty( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    return $product->get_stock_quantity();
}

/**
 * Get upsells by id
 *
 * @param int $post_id
 * @return array
 */
function bulk_manager_get_upsells( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    return $product->get_upsell_ids();
}

/**
 * Get cross sells by id
 *
 * @param int $post_id
 * @return array
 */
function bulk_manager_get_cross_sells( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    return $product->get_cross_sell_ids();
}

/**
 * Get stock status by id
 *
 * @param int $post_id
 * @return string
 */
function bulk_manager_get_stock_status( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    return $product->get_stock_status();
}

/**
 * Get weight by id
 *
 * @param int $post_id
 * @return string
 */
function bulk_manager_get_weight( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    return $product->get_weight();
}

/**
 * Get backorders by id
 *
 * @param int $post_id
 * @return string
 */
function bulk_manager_get_backorders( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    return $product->get_backorders();
}

/**
 * Get sku by id
 *
 * @param int $post_id
 * @return string
 */
function bulk_manager_get_sku( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    return $product->get_sku();
}

/**
 * Get gallery images by id
 *
 * @param int $post_id
 * @return array
 */
function bulk_manager_get_gallery_image( $post_id )
{
    $product = wc_get_product( $post_id );
    if ( ! $product ) return false;

    $image_ids = $product->get_gallery_image_ids();
    $images = [];
    if(!empty($image_ids) && is_array($image_ids) && count($image_ids) > 0){
        foreach ($image_ids as $image_id) {
            $images[] = [
                'url' => wp_get_attachment_url($image_id),
                'id'  => $image_id,
            ];
        }
    }
    return $images;
}

/**
 * Get post data
 *
 * @return string|int|array
 */
function bulk_manager_get_post_data( $post_id, $type, $taxonomy )
{
    if( ! $post_id ) {
        return false;
    }

    $action = str_replace('update_','',$type);
    $callback = 'bulk_manager_get_' . $action;

    $result  = call_user_func( $callback, $post_id, $taxonomy );
    return [
        'type'   => $action,
        'output' => $result,
    ];
}

/**
 * Add Taxonomy
 *
 * @param array $payload
 * @return boolean
 */
function bulk_manager_add_taxonomy($payload)
{
    $existing_data  = get_option('bulk_manager_register_taxonomies', []);
    $data[$payload['slug']] = [
        'post'          => $payload['post'],
        'singluarLabel' => $payload['singluarLabel'],
        'pluralLabel'   => $payload['pluralLabel'],
    ];
    $new_data = wp_parse_args( $data, $existing_data );
    $result   = update_option('bulk_manager_register_taxonomies', $new_data);
    if(is_wp_error($result)){
        return false;
    }
    return $payload['slug'];
}

/**
 * Edit Taxonomy
 *
 * @param array $payload
 * @return boolean
 */
function bulk_manager_edit_taxonomy($payload)
{
    $existing_data  = get_option('bulk_manager_register_taxonomies', []);
    $data[$payload['slug']] = [
        'post'          => $payload['post'],
        'singluarLabel' => $payload['singluarLabel'],
        'pluralLabel'   => $payload['pluralLabel'],
    ];
    $new_data = wp_parse_args( $data, $existing_data );
    $result   = update_option('bulk_manager_register_taxonomies', $new_data);
    if(is_wp_error($result)){
        return false;
    }
    return $payload['slug'];
}

/**
 * Delete Taxonomy
 *
 * @param array $payload
 * @return boolean
 */
function bulk_manager_delete_taxonomy($payload)
{
    $existing_data  = get_option('bulk_manager_register_taxonomies', []);
    unset($existing_data[$payload]);
    $result   = update_option('bulk_manager_register_taxonomies', $existing_data);
    if(is_wp_error($result)){
        return false;
    }
    return true;
}

/**
 * Update Taxonomy Data
 *
 * @param string $action
 * @param array $payload
 * @return boolean
 */
function bulk_manager_update_taxonomy_data($action, $payload)
{
    $callback = 'bulk_manager_' . $action;

    $result  = call_user_func( $callback, $payload );
    return $result;
}

/**
 * Add term to taxonomy
 *
 * @param array $payload
 * @return boolean
 */
function bulk_manager_add_term($payload)
{
    $result = wp_insert_term(
        $payload['name'], // the term 
        $payload['taxonomy'], // the taxonomy
        array(
            'description' => $payload['desc'],
            'slug'        => $payload['slug'],
            'parent'      => 0,
        )
    );
    if(is_wp_error($result)){
        return false;
    }
    return $payload['slug'];    
}

/**
 * Edit term to taxonomy
 *
 * @param array $payload
 * @return boolean
 */
function bulk_manager_edit_term($payload)
{
    $term = get_term_by( 'slug', $payload['term'], $payload['taxonomy'] );

    if(is_wp_error($term)){
        return false;
    }

    $result = wp_update_term( $term->term_id, $payload['taxonomy'], [
        'name'        => $payload['name'],
        'slug'        => $payload['slug'],
        'description' => $payload['desc']
    ] );
    if(is_wp_error($result)){
        return false;
    }
    return $payload['slug']; 
}

/**
 * Delete term from taxonomy
 *
 * @param array $payload
 * @return boolean
 */
function bulk_manager_delete_term($payload)
{
    $term = get_term_by( 'slug', $payload['term'], $payload['taxonomy'] );

    if(is_wp_error($term)){
        return false;
    }

    $result = wp_delete_term($term->term_id, $payload['taxonomy']);
    
    if(is_wp_error($result)){
        return false;
    }
    return true;
}

/**
 * Update Term Taxonomy Data
 *
 * @param string $action
 * @param array $payload
 * @return boolean
 */
function bulk_manager_update_taxonomy_term($action, $payload)
{
    $callback = 'bulk_manager_' . $action;

    $result  = call_user_func( $callback, $payload );
    return $result;
}
