<?php
require_once SMG_DIR . "/event_helpers/smg_upload_file_by_url.php";
class SMG_Event_Save
{
    function __construct()
    {
        add_action("wp_after_insert_post", [$this, "wp_after_insert_post"], 10, 3);
        add_action('upload_tm_image', [$this, 'upload_tm_image'], 10, 3);
        add_filter('cron_schedules', [$this, 'example_add_cron_interval']);
    }

    function example_add_cron_interval($schedules)
    {


        $schedules['five_seconds'] = array(
            'interval' => 5,
            'display'  => esc_html__('Every Five Seconds'),
        );
        return $schedules;
    }
    function upload_tm_image()
    {
        debug_log("upload_image");

        $args = [
            "post_type" => "event",
            "post_status"   => "all",
            "meta_query" => [
                [
                    "key"   => "_new_tm_image",
                    "value" => "1",
                    'compare' => '=',
                ]
            ]
        ];
        $events = get_posts($args);
        debug_log(count($events));
        foreach ($events as $post) {
            //get events without 
            $blocks = parse_blocks($post->post_content);
            if (!isset($blocks[0]["attrs"]["image"]["imageID"])) {
                $image_id = smg_upload_file_by_url($blocks[0]["attrs"]["image"]["imageUrl"]);
                $blocks[0]["attrs"]["image"]["imageID"] = $image_id;
                $serialized_block = (serialize_block($blocks[0]));
                $post->post_content = $serialized_block;
            }
            if (!isset($blocks[0]["attrs"]["altImage"]["imageID"])) {
                $image_id = smg_upload_file_by_url($blocks[0]["attrs"]["altImage"]["imageUrl"]);
                $blocks[0]["attrs"]["altImage"]["imageID"] = $image_id;
                $serialized_block = (serialize_block($blocks[0]));
                $post->post_content = $serialized_block;
            }

            update_post_meta($post->ID, "_new_tm_image", "2");
        }
        $timestamp = wp_next_scheduled('upload_tm_image');
        wp_unschedule_event($timestamp, 'upload_tm_image');
    }
    function wp_after_insert_post($post_id, $post, $update)
    {
        global $counter;
        if ($counter > 0) return;
        hit_counter();
        if ($post->post_type != "event") return;
        $blocks = parse_blocks($post->post_content);
        if (!$blocks) return;
        $eventType = $blocks[0]["attrs"]["eventType"];
        $eventBlock = $blocks[0]["attrs"];
        if ($eventType == "ticketmaster") {
            $ticketmaster_id = $blocks[0]["attrs"]["ticketmasterId"];
            $tmEeventData = $blocks[0]["attrs"]["tmEventData"];
            add_post_meta($post_id, "_new_tm_image", "1");
            if (!wp_next_scheduled('upload_tm_image')) {
                wp_schedule_event(time(), 'five_seconds', 'upload_tm_image');
            }

            isset($tmEeventData["title"]) && update_post_meta($post_id, "eventTitle",  $tmEeventData["title"]);
            $post->post_title = $blocks[0]["attrs"]["title"];
            $post->post_name = sanitize_title($tmEeventData["name"]);
            //debug_log($tmEeventData);
            $terms =  [$tmEeventData["classifications"][0]["segment"]["name"], $tmEeventData["classifications"][0]["genre"]["name"], $tmEeventData["classifications"][0]["subGenre"]["name"]];
            $parent = 0;
            foreach ($terms as $term) {
                $taxonomy = "event_tags";
                $term_id = term_exists($term, $taxonomy, $parent);
                if (!$term_id) {
                    $term_id = wp_insert_term($term, $taxonomy, ["parent" => $parent]);
                }
                $add =  wp_set_post_terms($post_id,  $term_id["term_id"],  $taxonomy, true);
                $parent = $term_id["term_id"];
            }
            if (isset($tmEeventData["promoter"])) {
                $term = $tmEeventData["promoter"]["name"];
                $taxonomy = "event_promoters";
                $term_id = term_exists($term, $taxonomy);
                if (!$term_id) {
                    $term_id = wp_insert_term($term, $taxonomy);
                }
                $add =  wp_set_post_terms($post_id,  $term_id["term_id"],  $taxonomy, false);
            }

            wp_set_post_terms($post_id,  $blocks[0]["attrs"]["event_series"],  "event_series", false);
            wp_set_post_terms($post_id,  $blocks[0]["attrs"]["event_filters"],  "event_filter", false);
            debug_log($blocks[0]["attrs"]["hide_in_listings"]);
            update_post_meta($post_id, "hide_in_listings", $blocks[0]["attrs"]["hide_in_listings"] ? "1" : "");
            if ($ticketmaster_id != "") {
                $item = end($eventBlock["eventDates"]);
                $block_date = str_replace("Z", "", $item["date"]);

                $last_date =  implode(" ", explode("T", $block_date));
                debug_log($last_date);
                $post->post_date =  $last_date;
                $post->post_date_gmt =  get_gmt_from_date($last_date);
                update_post_meta($post_id, "first_date", $last_date);
                update_post_meta($post_id, "last_date", $last_date);
                update_post_meta($post_id, "localDate", $tmEeventData["dates"]["start"]["localDate"]);
                update_post_meta($post_id, "localTime", $tmEeventData["dates"]["start"]["localTime"]);
                //isset($event->info) && update_post_meta($post_id, "info", $event->info);
                update_post_meta($post_id, "url", $tmEeventData["url"]);
                update_post_meta($post_id, "price_range_min",  $tmEeventData["priceRanges"][0]["min"]);
                update_post_meta($post_id, "price_range_max",  $tmEeventData["priceRanges"][0]["max"]);
                update_post_meta($post_id, "eventTitle",  $tmEeventData["name"]);
                update_post_meta($post_id, "starting_price_with_fee", number_format((int)$tmEeventData["priceRanges"][0]["min"] + 3.5, 2));
                $button = [
                    "link" => isset($blocks[0]["attrs"]["buttonLink"]) && isset($blocks[0]["attrs"]["buttonLink"]["link"]) && $blocks[0]["attrs"]["buttonLink"]["link"] !== "" ? $blocks[0]["attrs"]["buttonLink"]["link"] : $tmEeventData["url"] . "?brand=townhall&camefrom=cfc_town_hall_website",
                    "label" => isset($blocks[0]["attrs"]["buttonLink"]) && $blocks[0]["attrs"]["buttonLink"]["label"] !== "" ? $blocks[0]["attrs"]["buttonLink"]["label"] : "Get Tickets",
                ];
                update_post_meta($post_id, "button_link", $button);
            }
        }
        $eventDates = $eventBlock["eventDates"];
        $first_date = new DateTime($eventDates[0]["date"]);
        $year = $first_date->format('Y');
        $month = $first_date->format('n');
        if ($month > 8) {
            $season = $year . "/" . ((int)$year + 1);
        } else {
            $season = ((int)$year - 1) . "/" . $year;
        }
        update_post_meta($post_id, "season", $season);
        update_post_meta($post_id, "dates", $eventDates);
        foreach ($eventDates as $date) {
            $ds[explode("T", $date["date"])[0]][] = $date;
        }
        update_post_meta($post_id, "is_multiday", count($ds));
        remove_action('wp_after_insert_post', [$this, 'wp_after_insert_post']);
        wp_update_post($post);
        add_action('wp_after_insert_post', [$this, 'wp_after_insert_post'], 10, 3);
    }
}
