<?php

namespace Bulk\Manager;

/**
 * Ajax class
 */
class Ajax
{
    /**
     * Initialize ajax class
     */
    public function __construct()
    {
        add_action('wp_ajax_update_post_data', [$this, 'update_post_data']);
        add_action('wp_ajax_load_taxonomy_terms', [$this, 'load_taxonomy_terms']);
        add_action('wp_ajax_load_posts_by_terms', [$this, 'load_posts_by_terms']);
        add_action('wp_ajax_load_posts_by_type', [$this, 'load_posts_by_type']);
        add_action('wp_ajax_load_taxonomies_by_post_type', [$this, 'load_taxonomies_by_post_type']);
        add_action('wp_ajax_get_post_data', [$this, 'get_post_data']);
        add_action('wp_ajax_get_registered_taxonomies', [$this, 'get_registered_taxonomies']);
        add_action('wp_ajax_get_taxonomies_term', [$this, 'get_taxonomies_term']);
        add_action('wp_ajax_update_taxonomy', [$this, 'update_taxonomy']);
        add_action('wp_ajax_taxonomy_terms_update', [$this, 'taxonomy_terms_update']);
    }

    /**
     * Update post data
     */
    public function update_post_data (){
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        if( empty( $_POST['posts'] ) || ! is_array( $_POST['posts'] ) ||( count( $_POST['posts'] ) == 0 ) ) {
            wp_send_json_error( [
                'msg' => esc_html__('Select a post first.', 'bulk-manager')
            ] );
        }

        $posts   = bulk_manager_get_items_by_selection_type( bulk_manager_clean(wp_unslash($_POST['posts'])), bulk_manager_clean($_POST['post_type']) );
        $success = bulk_manager_update_data( $posts, bulk_manager_clean($_POST['action_type']), bulk_manager_clean(wp_unslash($_POST['payload'])) );
        
        if( ! $success ) {
            wp_send_json_error( [
                'msg' => esc_html__('Something went wrong while updating.', 'bulk-manager')
            ] );
        }

        wp_send_json_success( [
            'msg' => esc_html__('Successfully updated.', 'bulk-manager')
        ] );
    }

    /**
     * Fetch taxonomy and terms by post type
     *
     * @return void
     */
    public function load_taxonomy_terms()
    {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        $type = !empty($_POST['type']) ? bulk_manager_clean($_POST['type']) : 'term_id';
        $taxonomy = bulk_manager_clean($_POST['taxonomy']);
        $categories = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false ]);
        if(is_wp_error($categories)){
            wp_send_json_success([
                'terms' => [],
            ]);
        }
        $data = [];
        foreach ($categories as $key => $category) {
            $data[$category->$type] = $category->name;
        }

        wp_send_json_success([
            'terms' => $data,
        ]);
    }

    /**
     * Fetch posts by terms
     *
     * @return void
     */
    public function load_posts_by_terms()
    {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        $products_ids = bulk_manager_get_items_by_selection_type( null, bulk_manager_clean($_POST['post_type']), [bulk_manager_clean($_POST['taxonomy']) => bulk_manager_clean($_POST['term'])] );

        $products = [];
        if(!empty($products_ids)){
            foreach ($products_ids as $product_id) {
                $products[$product_id] = get_the_title($product_id);
            }
        }

        wp_send_json_success([
            'products' => $products,
        ]);
    }

    /**
     * Fetch terms
     *
     * @return void
     */
    public function get_post_data()
    {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        $data = bulk_manager_get_post_data( bulk_manager_clean($_POST['id']), bulk_manager_clean($_POST['type']), bulk_manager_clean($_POST['taxonomy']) );

        wp_send_json_success($data);
    }

    /**
     * Fetch posts by selected post type
     *
     * @return void
     */
    public function load_posts_by_type()
    {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        $post_type = bulk_manager_clean($_POST['postType']);

        $the_posts = get_posts([
            'post_type'      => $post_type,
            'post_status'    => 'any',
            'posts_per_page' => -1
        ]);
        $types_data = [];
        foreach ($the_posts as $key => $the_post) {
            $types_data[$the_post->ID] = $the_post->post_title;
        }
        if(count($types_data) == 0){
            $types_data = ['none' => esc_html__('None', 'bulk-manager')];
        }else if(count($types_data) >= 2){
            $types_data = [0 => esc_html__('All', 'bulk-manager')] + $types_data;
        }

        wp_send_json_success([
            'postData' => $types_data,
        ]);
    }

    /**
     * Get post taxonomy by post type
     *
     * @return void
     */
    public function load_taxonomies_by_post_type()
    {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        $post_type = bulk_manager_clean($_POST['postType']);
        $types_data = bulk_manager_get_taxonomies_by_post($post_type);
        
        wp_send_json_success([
            'taxonimies' => $types_data
        ]);
    }

    /**
     * Update term to taxonomy
     *
     * @return void
     */
    public function taxonomy_terms_update()
    {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        $result = bulk_manager_update_taxonomy_term(bulk_manager_clean($_POST['actionType']), bulk_manager_clean(wp_unslash($_POST['payload'])));
        
        if( ! $result ) {
            wp_send_json_error( [
                'msg' => esc_html__('Something went wrong while updating.', 'bulk-manager')
            ] );
        }

        wp_send_json_success( [
            'term' => $result,
            'msg'  => esc_html__('Successfully updated.', 'bulk-manager')
        ] );
    }

    /**
     * Get taxonomy terms
     *
     * @return void
     */
    public function get_taxonomies_term()
    {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        $taxonomy = bulk_manager_clean($_POST['taxonomy']);
        $slug     = bulk_manager_clean($_POST['editableTerm']);

        $results = get_term_by('slug', $slug, $taxonomy);

        if(is_wp_error($results)){
            wp_send_json_error([
                'msg' => esc_html__('Something went wrong.', 'bulk-manager'),
            ]);
        }
        wp_send_json_success([
            'term' => $results,
        ]);
    }

    /**
     * Add taxonomy
     *
     * @return void
     */
    public function update_taxonomy()
    {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        $action_type = bulk_manager_clean($_POST['actionType']);
        $payload     = bulk_manager_clean(wp_unslash($_POST['payload']));

        $result = bulk_manager_update_taxonomy_data($action_type, $payload);

        if( ! $result ) {
            wp_send_json_error( [
                'msg' => esc_html__('Something went wrong while updating.', 'bulk-manager')
            ] );
        }

        wp_send_json_success( [
            'slug' => $result,
            'msg'  => esc_html__('Successfully updated.', 'bulk-manager')
        ] );

    }

    /**
     * Get registered taxonomies
     *
     * @return void
     */
    public function get_registered_taxonomies()
    {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['security'] ) ) , 'bulk_manager_ajax_nonce' ) ) {
            wp_send_json_error([
                'msg' => esc_html__('Nonce verification failed.', 'bulk-manager'),
            ]);
        }

        $taxonomy   = bulk_manager_clean($_POST['taxonomy']);
        $taxonomies = get_option('bulk_manager_register_taxonomies', []);
        wp_send_json_success([
            'taxonomies' => $taxonomies[$taxonomy]
        ]);
    }

}
