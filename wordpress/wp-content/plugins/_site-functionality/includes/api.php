<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SMG_API
{

    public function __construct()
    {
        add_filter('rest_speaker_collection_params', [$this, 'my_prefix_add_rest_orderby_params'], 10, 1);
        add_filter('rest_speaker_query', [$this, 'filter_rest_accommodation_query'], 10, 2);
        add_action('rest_api_init',  [$this, 'register_rest_images']);
    }

    function register_rest_images()
    {
        register_rest_field(
            array('page'),
            'featured_image',
            array(
                'get_callback'    => [$this, 'get_rest_featured_image'],
                'update_callback' => null,
                'schema'          => null,
            )
        );
        register_rest_field(
            array('page'),
            'page_path',
            array(
                'get_callback'    => [$this, 'get_page_path'],
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }
    function get_rest_featured_image($object, $field_name, $request)
    {

        if ($object['featured_media']) {
            $img = wp_get_attachment_image_src($object['featured_media'], "full");
            return $img[0];
        }
        return false;
    }


    function filter_rest_accommodation_query($query_vars, $request)
    {
        $query_vars["orderby"] = "menu_order";
        $query_vars["order"] = "asc";
        return $query_vars;
    }

    function my_prefix_add_rest_orderby_params($params)
    {
        $params['orderby']['enum'][] = 'menu_order';
        return $params;
    }

    function get_page_path($object, $field_name, $request)
    {
        $path = "";
        if ($object["parent"] != 0) {
            $parent = get_post($object["parent"]);
            if ($parent->post_parent != 0) {
                $pparent = get_post($parent->post_parent);
                $path .= "/" . $pparent->post_name;
            }
            $path .= "/" . $parent->post_name;
        }

        return  $path .= "/" . $object['slug'];
    }
}
