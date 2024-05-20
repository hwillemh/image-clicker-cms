<?php

class SMG_Brand extends SMG_Base{
    public function __construct()
    {
        {
            parent::__construct();
            add_action('init', array($this, 'register_posts'));
            add_filter('rwmb_meta_boxes', [$this, 'register_meta_boxes']);
        }
        
    }
    function register_meta_boxes($meta_boxes)
    {
        $meta_boxes[] = array(
            'title' => 'Meta data',
            'id'    => 'brand-meta',
            'post_types' => ['brand'],
            'class' => 'brand-meta',
            'fields' => [
                [
                    "id"    => 'image',
                    "name"  => "Centered Image",
                    "type"  => "image_advanced",
                    "desc" => "Image size 1000 x 625px. Logo centered within.",
                    "max_file_uploads" => 1,
                    "max_status" => false,
                ],
                [
                    "id"    => 'image_left',
                    "name"  => "Left-Aligned Image",
                    "type"  => "image_advanced",
                    "desc" => "Image size 1000 x 625px. Logo top-left aligned.",
                    "max_file_uploads" => 1,
                    "max_status" => false,
                ],
            ]
        );
        return $meta_boxes;
    }
    public function register_posts()
    {
        $post_types = array(
            array(
                'content_id' => 'brand',
                'label_plural' => 'Brands',
                'label_singular' => 'Brand',
                'has_archive' => true,
                'rewrite' => array('slug' => 'brand'),
                'public' => true,
                'show_in_rest' => true,
                'rest_base' => 'brands',
                'hierarchical' => false,
                'supports' => array(
                    'title',
                    //'editor',
                    'revisions',
                    'thumbnail',
                    //'page-attributes',
                    //'excerpt',
                ),
            )
        );
        $this->post_type_factory($post_types);
    }
}