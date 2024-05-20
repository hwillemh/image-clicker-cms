<?php
class SMG_Preview_Link
{
    function __construct()
    {
        add_filter('preview_post_link', [$this, 'the_preview_fix'], 10, 2);
    }

    function the_preview_fix($link, $post)
    {

        $domain = "https://staging.thetownhall.org/";
        if ($post->post_type == "event") {
            return $domain . "/events/$post->post_name";
        } else if ($post->post_type == "page") {
            $parent = get_post($post->post_parent);
            return $domain . "/$parent->post_name/$post->post_name";
        }

        return $domain;
    }
}
