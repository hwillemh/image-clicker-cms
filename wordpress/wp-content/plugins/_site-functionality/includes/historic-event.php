<?php
class SMG_Historic_Event extends SMG_Base
{
    function __construct()
    {
        parent::__construct();
        add_action('init', array($this, 'register_posts'));
        add_filter('rwmb_meta_boxes', [$this, 'register_meta_boxes']);
        //add_action('init', array($this, 'register_taxonomy'));
        add_filter('enter_title_here', [$this, 'wpb_change_title_text']);
    }
    function wpb_change_title_text($title)
    {

        $screen = get_current_screen();

        if ('historic_event' == $screen->post_type) {
            $title = 'Event Headline';
        }

        return $title;
    }



    function register_meta_boxes($meta_boxes)
    {
        $meta_boxes[] = array(
            'title' => 'Historic Event Data',
            'id'    => 'historic-meta',
            'post_types' => ['historic_event'],
            'class' => 'historic-meta',
            'fields' => [
                [
                    "id" => "artist_name",
                    "name" => "Artist Name",
                    "desc" => "This will appear in the name grid if flagged below",
                    "admin_columns"  => [
                        "filterable" => true,
                        "sort" => true,
                    ],

                ],
                [
                    "id" => "include_in_grid",
                    "name" => "Include in Name Grid",
                    "type"  => "checkbox",
                    "admin_columns"  => [

                        "sort" => true,
                    ],

                ],
                [
                    "type" => "divider"
                ],

                [
                    "id" => "event_image",
                    "name" => "Event Image",
                    "type" => "image_advanced",
                    "max_file_uploads" => 1,
                    "max_status" => false,
                    "admin_columns"  => true,


                ],
                [
                    "type" => "divider"
                ],
                /* [
                    "id" => "event_artifacts",
                    "name" => "Event Artifacts",
                    "type" => "image_advanced",

                ],*/




            ]
        );
        return $meta_boxes;
    }
    public function register_posts()
    {
        $post_types = array(
            array(
                'content_id' => 'historic_event',
                'label_plural' => 'Historic Events',
                'label_singular' => 'Historic Event',
                'has_archive' => false,
                'rewrite' => array('slug' => 'historic_event'),
                'public' => true,
                'show_in_rest' => true,
                'rest_base' => 'historic_events',
                'supports' => array(
                    'title',
                    'revisions',
                    //'page-attributes',
                    'editor',


                ),
            )
        );
        $this->post_type_factory($post_types);
    }
}
