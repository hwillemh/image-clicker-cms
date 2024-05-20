<?php
class SMG_Page
{
    function __construct()
    {
        add_filter('rwmb_meta_boxes', [$this, 'register_meta_boxes']);
    }
    function register_meta_boxes($meta_boxes)
    {
        $meta_boxes[] = array(
            'title' => 'Page Data',
            'id'    => 'page-meta',
            'post_types' => ['page'],
            'class' => 'page-meta',
            'fields' => [
                [
                    "name" => "You may also like",
                    "id" => "also_like",
                    "type" => "post",
                    "post_type" => 'page',
                    "clone" => true,
                    "sort_clone" => true,
                    "max_clone" => 3
                ]

            ]
        );
        return $meta_boxes;
    }
}
