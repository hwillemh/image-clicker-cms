<?php
class SMG_Admin_Header
{
    function __construct()
    {
        add_action("admin_enqueue_scripts", [$this, "admin_enqueue_scripts"]);
    }
    function admin_enqueue_scripts($a)
    {
        wp_enqueue_style("blockfont", "https://cloud.typography.com/6870894/6987832/css/fonts.css");
        wp_enqueue_style("rwmb", SMG_URL . "css/smg-metabox.css");
    }
}
