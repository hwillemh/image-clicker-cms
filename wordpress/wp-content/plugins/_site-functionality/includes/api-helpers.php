<?php

class SMG_Api_Helpers
{

    public static function  getJsonPost($post)
    {
        $json = [
            "id" => $post->ID,
            "title" => ["rendered" => $post->post_title],
            "slug" => $post->post_name,
            "meta_box" => SMG_Rest_API_Base::get_post_meta(["id" => $post->ID, "type" => $post->post_type]),
            "content_blocks" => SMG_Api_Helpers::parse_contentblocks($post->post_content),
            "status" => $post->post_status,
            "excerpt" => $post->post_excerpt,
            "type" => $post->post_type,
            "date" => $post->post_date,

        ];
        if ($post->post_type == "event") {
            $json["event_title"] = $post->post_title;
            $json["event_series"] = wp_get_post_terms($post->ID, "event_series");
            $json["event_filter"] = wp_get_post_terms($post->ID, "event_filter");

            /*$dates = rwmb_meta("dates", [], $post->ID);
            if (count($dates) > 1) {
                $days = [];
                foreach ($dates as $date) {
                    $days[explode("T", $date["date"])[0]][] = $date["date"];
                }
                foreach ($days as $day) {
                    $now = date_create();

                    $diff = date_diff($now, date_create($day[0]), false);
                    debug_log($diff->invert, $diff->days);
                    if ($diff->invert == 1 && $diff->days > 0) continue;

                    $json["date"] =  $day[0];
                    $json["times"] = $day;
                }
            }
            */
        }
        return $json;
    }
    public static function  parse_contentblocks($content)
    {

        $parsed_content = parse_blocks($content);
        $blocks = [];
        foreach ($parsed_content as $key => $block) {
            if ($block['blockName'] != null) {
                switch ($block['blockName']) {
                    case 'core/video':
                        $src = wp_get_attachment_url($block['attrs']['id']);
                        $block["attrs"]["videoUrl"] = $src;
                    case "core/image":
                        //$image = SMG_Api_Helpers::get_image($block['attrs']["id"]);
                        $src =  wp_get_attachment_image_src($block['attrs']['id'], 'large');
                        $block["attrs"]["imageUrl"] = $src[0];
                        break;
                    case "tth/event-form":
                        unset($block['attrs']['tmEventData']);
                        if (isset($block['attrs']['image']['imageID'])) {
                            $image = SMG_Api_Helpers::get_image($block['attrs']['image']['imageID']);
                            $block["attrs"]['image']["image"] = $image;
                            $src =  wp_get_attachment_image_src($block['attrs']['image']['imageID'], 'large');
                            $block["attrs"]['image']["imageUrl"] = $src[0];
                        }
                        if (isset($block['attrs']['altImage']['imageID'])) {
                            $image = SMG_Api_Helpers::get_image($block['attrs']['altImage']['imageID']);
                            $block["attrs"]['altImage']["image"] = $image;
                            $src =  wp_get_attachment_image_src($block['attrs']['altImage']['imageID'], 'large');
                            $block["attrs"]['altImage']["imageUrl"] = $src[0];
                        }

                        break;
                }

                $blocks[] = $block;
            }
        }
        return $blocks;
    }
    static function get_image($id)
    {
        $image = [];
        $image['srcset'] =  wp_get_attachment_image_srcset($id);
        $image['sizes'] =  wp_get_attachment_image_sizes($id);
        $image['thumbnail'] = wp_get_attachment_image_url($id, 'thumbnail');
        $src =  wp_get_attachment_image_src($id, 'large');
        $image['width'] = isset($src[1]) ? $src[1] : "";
        $image['height'] = isset($src[2]) ? $src[2] : "";
        return $image;
    }
}
