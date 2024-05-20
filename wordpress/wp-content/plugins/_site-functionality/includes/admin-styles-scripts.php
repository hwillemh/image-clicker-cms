<?php
class SMG_Admin_Styles_Scripts
{
    function __construct()
    {
        add_action('admin_enqueue_scripts', [$this,'wp_enqueue_scripts']);
    }
    function wp_enqueue_scripts()
    {
        wp_enqueue_style("hoeffler", "https://cloud.typography.com/6870894/7761832/css/fonts.css");
        wp_enqueue_style("google", SMG_URL . "/css/theme-fonts.css");
        wp_enqueue_style('admin', SMG_URL . '/css/admin-styles.css');
    
    }
}
