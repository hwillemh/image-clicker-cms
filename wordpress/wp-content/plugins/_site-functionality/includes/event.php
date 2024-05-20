<?php

global $counter;
$counter = 0;
function hit_counter()
{
    global $counter;
    $counter += 1;
}

class SMG_Event extends SMG_Base
{

    function __construct()
    {

        parent::__construct();
        add_action('init', array($this, 'register_posts'));
        add_action('init', array($this, 'register_taxonomy'));
        add_filter('rwmb_meta_boxes', [$this, 'register_meta_boxes']);
        //add_action("save_post", [$this, 'save_post'], 10, 3);

        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('init', [$this, 'myplugin_register_template']);
    }



    function myplugin_register_template()
    {
        $post_type_object = get_post_type_object('event');
        $post_type_object->template = array(
            array('tth/event-form',),
        );
    }



    function admin_enqueue_scripts()
    {
        wp_enqueue_script('event-script', SMG_URL . 'js/event-form.js');
    }


    function register_meta_boxes($meta_boxes)
    {
        $ticketmaster_id =  isset($_REQUEST["post"]) ?  rwmb_meta("ticketmaster_id", "", $_REQUEST["post"]) : false;
        $title =  isset($_REQUEST["post"]) ?  rwmb_meta("eventTitle", "", $_REQUEST["post"]) : false;

        $meta_boxes[] = array(
            'title' => 'Event Info',
            'id'    => 'event-meta',
            'post_types' => 'event',
            'visible' => ['_thumbnail_id', '=', '123'],
            'fields' => [
                [
                    "id" => "dates",
                    "name" => "Dates",
                    "type" => "fieldset_text",
                    "clone" => true,
                    // "admin_columns" => ['position' => 'after title',],
                    "sort_clone" => true,
                    "readonly" => true,
                    "disabled" => true,
                    'visible' => ['_thumbnail_id', '=', '123'],
                    "options" => [
                        'date' => 'date',
                        'label' => 'label',
                    ]


                ],
                [
                    "id" => "localDate",
                    "name" => "Local date",
                    "type" => "date",
                    "readonly" => $ticketmaster_id == "ticketmaster",
                    "disabled" => true,
                    //  "visible" => $show_match,
                ],
                [
                    "id" => "localTime",
                    "name" => "Local Time",
                    "type" => "time",
                    "readonly" => $ticketmaster_id == "ticketmaster",
                    "disabled" => true,
                ],
                [
                    "id" => "price_range_min",
                    "name" => "Price Range Min",
                    "type" => "text",
                    "readonly" => $ticketmaster_id == "ticketmaster",
                    // "visible" => $show_match,
                    "disabled" => true,
                ],
                [
                    "id" => "price_range_max",
                    "name" => "Price Range Max",
                    "type" => "text",
                    "readonly" => $ticketmaster_id == "ticketmaster",
                    // "visible" => $show_match,
                    "disabled" => true,
                ],
                [
                    "id" => "starting_price_with_fee",
                    "name" => "Price Range Max",
                    "type" => "text",
                    "readonly" => $ticketmaster_id == "ticketmaster",
                    // "visible" => $show_match,
                    "disabled" => true,
                ],
                [
                    "id" => "season",
                    "name" => "Season",
                    "type" => "text",
                    "readonly" => true,
                    "admin_columns" => true,
                    "disabled" => true,
                ],
                [
                    "id" => "is_multiday",
                    "name" => "Has Multiple Event Days",
                    "type" => "text",
                    "readonly" => true,
                    "disabled" => true,
                ],
                [
                    "id" => "times",
                    "name" => "Multiple Times",
                    "type" => "text",
                    "readonly" => true,
                    "disabled" => true,
                ],
                [
                    "id" => "button_link",
                    "name" => "Button Link",
                    "type" => "text",
                    "readonly" => true,
                    "disabled" => true,
                ],
                [
                    "name" => "Exclude from listings",
                    "id"    => "hide_in_listings",
                    "type"  => "text",
                    "admin_columns" => true,
                    "disabled" => true,
                ]

            ]
        );
        return $meta_boxes;
    }
    public function _title_field()
    {
        $title = rwmb_meta("eventTitle", "", get_the_ID());
        return sprintf("<h3 style='font-size:40px'>%s</h3>", $title);
    }
    public function _ticketmaster_select()
    {
        $ticketmaster_id = rwmb_meta("ticketmaster_id", "", get_the_ID());
        $TM_array = SMG_TicketmasterAPI::get_events();
        $options = ["<option  value='-1'>Select a Ticketmaster event ...</option>"];
        foreach ($TM_array->_embedded->events as $event) {
            $TM_date = date_create($event->dates->start->localDate);
            $options[] = sprintf("<option %s value='%s'>%s</option>", $ticketmaster_id == $event->id ? "selected" : "", $event->id,  date_format($TM_date, "M d, Y") . " – " . $event->name);
        }
        $select = sprintf('<h3><select id="ticketmaster_id" class="rwmb-ticketmaster_id" name="ticketmaster_id" aria-labelledby="select-label">%s</select></h3>', join("\n", $options));
        return $select;
    }
    public function register_posts()
    {

        $post_types = array(
            array(
                'content_id' => 'event',
                'label_plural' => 'Events',
                'label_singular' => 'Event',
                'has_archive' => true,
                'rewrite' => array('slug' => 'event'),
                'public' => true,
                'show_in_rest' => true,
                'rest_base' => 'events',

                'supports' => array(
                    // 'title',
                    'editor',
                    'revisions',
                    //'thumbnail',
                    //'page-attributes',
                    //'excerpt',
                ),
            )
        );
        $this->post_type_factory($post_types);
    }
    public function register_taxonomy()
    {
        $taxonomies = [
            [
                "label_plural" => "Series",
                "label_singular"    => "Series",
                "tax_id"    => "event_series",
                "post_types" => "event",
                "meta_box_cb" => false,
            ],
            [
                "label_plural" => "Filters",
                "label_singular"    => "Filter",
                "tax_id"    => "event_filter",
                "post_types" => "event",
                "meta_box_cb" => false,
            ],
            [
                "label_plural" => "Promoters",
                "label_singular"    => "Promoter",
                "tax_id"    => "event_promoters",
                "post_types" => "event",
            ],
            [
                "label_plural" => "Tags",
                "label_singular"    => "Tag",
                "tax_id"    => "event_tags",
                "post_types" => "event",
            ]

        ];

        $this->taxonomy_factory($taxonomies);
    }
}
