<?php

class SMG_Api_Smg
{

    function __construct()
    {
        add_action('rest_api_init', [$this, 'rest_api_init']);
    }

    function rest_api_init()
    {

        register_rest_route('smg/v1', '/tm_events', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_tm_events'],
            'args'                => array(
                'slug' => array(
                    'default' => 'view',
                ),
            ),
            'permission_callback' => '__return_true'
        ));
        register_rest_route('smg/v1', '/events', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_events'],
            'args'                => array(
                'slug' => array(
                    'default' => 'view',
                ),
            ),
            'permission_callback' => '__return_true'
        ));


        register_rest_route('smg/v1', '/menu', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_menu'],
            'args'                => array(
                'slug' => array(
                    'default' => 'view',
                ),
            ),
            'permission_callback' => '__return_true'
        ));
        register_rest_route('smg/v1', '/also-like', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_also_like'],
            'args'                => array(
                'id' => array(
                    'default' => 'view',
                ),
            ),
            'permission_callback' => '__return_true'
        ));
    }
    function get_also_like($request)
    {
        $post_id = $request->get_param("id");
        //$event = get_post($post_id);
        $tags = wp_get_post_terms($post_id, "event_tags", ['hierarchical'      => '1', 'hide_empty'        => false,]);
        $tag_terms = [];
        foreach ($tags as $tag) {
            $tag_terms[] = $tag->term_id;
        }
        $hierarchy_tags = $this->_get_taxonomy_hierarchy("event_tags", $post_id);
        $promotor = wp_get_post_terms($post_id, "event_promoters");
        $promotor_ids = [];
        foreach ($promotor as $tag) {
            $promotor_ids[] = $tag->term_id;
        }

        $args = [
            "post_type" => "event",
            "post_status" => ["future", "publish"],
            "exclude" => $post_id,
            "posts_per_page" => 10,
            "date_query" => [
                [
                    "after" => isset($request["after"]) ? $request["after"] :  date("Y-m-d"),
                    "before" => isset($request["before"]) ? $request["before"] :  null
                ]
            ],
            "tax_query" => [
                [
                    "taxonomy" => "event_tags",
                    "field" => "term_id",
                    "terms" =>   $tag_terms,
                ],
                [
                    "taxonomy" => "event_promoters",
                    "field" => "term_id",
                    "terms" =>   $promotor[0]->term_id,
                ],
                'relation' => 'OR',

            ]
        ];
        $events = get_posts($args);
        $output = [];
        foreach ($events as $event) {
            $json = SMG_Api_Helpers::getJsonPost($event);
            $json["points"] = 0;
            $event_tags = wp_get_post_terms($event->ID, "event_tags");
            foreach ($event_tags as $etag) {
                if (in_array($etag->term_id, $tag_terms)) {
                    $etag->parent === 0 ? $points = 1 : $points = 2;
                    $json["points"] += $points;
                    $json["same_tags"] = true;
                }
            }
            $prom_tags = wp_get_post_terms($event->ID, "event_promoters");
            foreach ($prom_tags as $etag) {
                if (in_array($etag->term_id, $promotor_ids)) {

                    $json["points"] += 1;
                    $json["same_promotor"] = true;
                }
            }
            $output[] =   $json;
        }
        usort($output, function ($a, $b) {
            return strcmp($b["points"], $a["points"]);
        });
        return   array_slice($output, 0, 3);
    }
    function _get_taxonomy_hierarchy($taxonomy, $post_id,  $parent = 0, $level = 0)
    {
        // only 1 taxonomy
        $taxonomy = is_array($taxonomy) ? array_shift($taxonomy) : $taxonomy;

        // get all direct decendants of the $parent
        //$terms = get_terms($taxonomy, array('parent' => $parent));
        $terms = wp_get_post_terms($post_id, "event_tags", ['parent' => $parent]);
        // prepare a new array.  these are the children of $parent
        // we'll ultimately copy all the $terms into this new array, but only after they
        // find their own children
        $children = array();

        // go through all the direct decendants of $parent, and gather their children
        foreach ($terms as $term) {
            // recurse to get the direct decendants of "this" term
            $term->children = $this->_get_taxonomy_hierarchy($taxonomy, $post_id,  $term->term_id, $level + 1);
            $term->level = $level;
            // add the term to our new array
            $children[$term->term_id] = $term;
        }

        // send the results back to the caller
        return $children;
    }
    function get_menu()
    {
        $args = [
            "post_type" => "nav_menu_item",
        ];
        $menu = get_posts($args);
    }
    function get_events($request)
    {

        $args = [
            "post_type" => "event",
            "post_status" => ["future", "publish"],
            "posts_per_page" => -1,
            "order" => "ASC",
            "orderby" => "date",

        ];

        if (isset($request["slug"]) && $request["slug"] != "view") {
            $args["name"] =   $request["slug"];
        } else {
            $args["date_query"] = [
                [
                    "after" => isset($request["after"]) ? $request["after"] :  date("Y-m-d"),
                    "before" => isset($request["before"]) ? $request["before"] :  null
                ]
            ];
            /*
            $args["meta_query"] = [
                'relation'        => 'AND',
                [
                    "key" => "hide_in_listings",
                    "value" => "1",
                    'type'        => 'string',
                    "compare" => "NOT EXISTS"
                ]
            ];
            */
        }

        if (isset($request["event_series"])) {
            $args["tax_query"] = [
                [
                    "taxonomy" => "event_series",
                    "field" => "term_id",
                    "terms"  => $request["event_series"],
                    'operator' => 'IN',
                ]


            ];
        }

        $events = get_posts($args);
        debug_log(count($events));
        $json_events = [];
        foreach ($events as $event) {

            debug_log($event->post_title);
            if (rwmb_meta("hide_in_listings", "", $event->ID) == "1") continue;
            $jsonevent = SMG_Api_Helpers::getJsonPost($event);
            /*$jsonevent["event_title"] = $event->post_title;
            $jsonevent["event_series"] = wp_get_post_terms($event->ID, "event_series");
            $jsonevent["event_filter"] = wp_get_post_terms($event->ID, "event_filter");
           
            */


            $dates = rwmb_meta("dates", [], $event->ID);
            debug_log(count($dates));
            if (count($dates) > 1) {
                $days = [];
                foreach ($dates as $date) {
                    $days[explode("T", $date["date"])[0]][] = $date["date"];
                }
                foreach ($days as $day) {
                    $now = date_create();
                    $diff = date_diff($now, date_create($day[0]), false);
                    if ($diff->invert == 1 && $diff->days > 0) continue;
                    $jsonevent["date"] =  $day[0];
                    $jsonevent["times"] = $day;
                    $json_events[] = $jsonevent;
                }
            } else {
                $json_events[] = $jsonevent;
            }
        }
        debug_log(count($json_events));
        return $json_events;
    }
    function get_tm_events()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://app.ticketmaster.com/discovery/v2/events?countryCode=US&venueId=KovZpZAFdJtA&startDateTime=2023-08-01T00%3A00%3A00Z&size=100&sort=date%2Casc&apikey=GAIoXHoPmgJWTD04yBnDj0VxOf5A3Rfx',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $TM_response = curl_exec($curl);

        curl_close($curl);
        $args = [
            "post_type" => "event",
            "posts_per_page" => -1,
        ];
        $WP_events = get_posts($args);
        $TM_array = json_decode($TM_response);
        $response = [];

        foreach ($TM_array->_embedded->events as $event) {
            if (isset($WP_events[0])) {
                //debug_log($event);
                $TM_date = date_create($event->dates->start->localDate);
                $wp_date_rwmb = rwmb_meta("date-time", "", $WP_events[0]->ID);
                $wp_date = date_create($wp_date_rwmb);
                $diff = date_diff($TM_date, $wp_date);
                debug_log($diff);

                if ($diff->invert == 1) {
                    $p =  array_shift($WP_events);
                    $date_time = explode(" ", $wp_date_rwmb);
                    $wp_event = [
                        "name" => $p->post_title,
                        "dates" => [
                            //"start" => [
                            //     "localDate" => $date_time[0],
                            //     "localTime" => $date_time[1]
                            // ]
                        ],
                        "info" => rwmb_meta("info", "", $p->ID),
                        "url" => rwmb_meta("url", "", $p->ID),
                    ];

                    $response[] = $wp_event;
                }
            }
            /*$temp_event = [
                "name" => $event->name,
                        "dates" => [
                            ["start" => 
                            ["localDate" => $TM_date ]
                            ]]
                        ];
            */
            $response[] =  $event;
            //if ($event[0].)
        }

        return $response;
    }
}
