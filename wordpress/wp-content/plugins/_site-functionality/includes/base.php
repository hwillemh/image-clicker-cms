<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class SMG_Base
{

    public function __construct()
    {;

        add_theme_support('post-thumbnails');
        add_filter('rest_prepare_taxonomy', [$this, "hide_taxonomies"], 10, 3);
    }

    public function post_type_factory($post_types)
    {
        foreach ($post_types as $post_type) {
            $defaults = array(
                'has_archive' => false,
                'rewrite' => false,
                'show_in_rest' => false,
                'rest_base' => '',
                'hierarchical' => false,

            );
            foreach ($defaults as $key => $val) {
                $post_type[$key] = (isset($post_type[$key])) ? $post_type[$key] : $val;
            }
            $supports = (isset($post_type['supports'])) ? $post_type['supports'] :
                array(
                    'title',
                    'editor',
                    'revisions',
                    'thumbnail',
                );
            $settings = $post_type;
            $mapped = [
                'label' => $post_type['label_plural'],
                'description' => '',
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'capability_type' => 'post',
                'hierarchical' => $post_type['hierarchical'],
                'query_var' => true,
                'has_archive' => $post_type['has_archive'],
                'rewrite' => $post_type['rewrite'],
                'supports' => $supports,
                'show_admin_column' => true,
                'show_in_rest' => $post_type['show_in_rest'],
                'rest_base' => $post_type['rest_base'],
                'rest_controller_class' => 'WP_REST_Posts_Controller',
                'labels' =>
                array(
                    'name' => $post_type['label_plural'],
                    'singular_name' => $post_type['label_singular'],
                    'menu_name' => $post_type['label_plural'],
                    'add_new' => __('Add a new ' . $post_type['label_singular'], 'textarea'),
                    'add_new_item' => __('Add a new ' . $post_type['label_singular'], 'textarea'),
                    'edit' => __('Edit', 'textarea'),
                    'edit_item' => __('Edit ' . $post_type['label_singular'], 'textarea'),
                    'new_item' => __('New ' . $post_type['label_singular'], 'textarea'),
                    'view' => __('View ' . $post_type['label_singular'], 'textarea'),
                    'view_item' => __('View ' . $post_type['label_singular'], 'textarea'),
                    'search_items' => __('Search ' . $post_type['label_plural'], 'textarea'),
                    'not_found' => __('No ' . $post_type['label_plural'] . ' Found', 'textarea'),
                    'not_found_in_trash' => __('No ' . $post_type['label_plural'] . ' Found in Trash', 'textarea'),
                    'parent' => __('Parent ' . $post_type['label_singular'], 'textarea'),
                )
            ];
            $settings = array_merge($settings, $mapped);
            register_post_type(
                $post_type['content_id'],
                $settings
            );
        }
    }

    public function taxonomy_factory($taxonomies)
    {
        $defaults = array(
            'show_ui' => true,
            'show_admin_column' => true,
            'meta_box_cb' => true,
        );
        foreach ($taxonomies as $taxonomy) {
            foreach ($defaults as $key => $val) {
                $taxonomy[$key] = (isset($taxonomy[$key])) ? $taxonomy[$key] : $val;
            }
            $labels = array(
                'name' => _x($taxonomy['label_plural'], 'taxonomy general name'),
                'singular_name' => _x($taxonomy['label_singular'], 'taxonomy singular name'),
                'search_items' => __('Search ' . $taxonomy['label_plural']),
                'all_items' => __('All ' . $taxonomy['label_plural']),
                'parent_item' => __('Parent ' . $taxonomy['label_singular']),
                'parent_item_colon' => __('Parent :' . $taxonomy['label_singular']),
                'edit_item' => __('Edit ' . $taxonomy['label_singular']),
                'update_item' => __('Update ' . $taxonomy['label_singular']),
                'add_new_item' => __('Add New ' . $taxonomy['label_singular']),
                'new_item_name' => __('New ' . $taxonomy['label_singular']),
                'menu_name' => __($taxonomy['label_plural']),
            );

            $args = array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => $taxonomy['show_ui'],
                'show_admin_column' => $taxonomy['show_admin_column'],
                'show_in_rest' => true,
                'query_var' => true,
                'rewrite' => array('slug' => $taxonomy['tax_id']),
            );
            if (isset($taxonomy['meta_box_cb']))
                $args['meta_box_cb'] = $taxonomy['meta_box_cb'];


            register_taxonomy($taxonomy['tax_id'], $taxonomy['post_types'], $args);
        }
    }
    function hide_taxonomies($response, $taxonomy, $request)
    {
        $context = !empty($request['context']) ? $request['context'] : 'view';
        if ($context === 'edit' && $taxonomy->meta_box_cb === false) {
            $data_response = $response->get_data();
            $data_response['visibility']['show_ui'] = false;
            $response->set_data($data_response);
        }
        return $response;
    }
}
