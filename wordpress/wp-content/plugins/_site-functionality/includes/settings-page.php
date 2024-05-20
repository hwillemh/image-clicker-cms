<?php
class SMG_Settings_Page
{
    function  __construct()
    {
        add_filter('mb_settings_pages', [$this, "mb_settings_page"]);
        add_filter('rwmb_meta_boxes', [$this, 'rwmb_meta_boxes']);
        add_action("admin_enqueue_scripts", [$this, "admin_enqueue_scripts"]);
    }
    function admin_enqueue_scripts($a)
    {

        wp_enqueue_style("rwmb", SMG_URL . "css/smg-metabox.css");
    }

    function rwmb_meta_boxes($meta_boxes)
    {
        $meta_boxes[] = [
            'id'             => 'general',
            'title'          => 'General',
            'context'        => 'normal',
            'post_types' => 'page',
            // 'settings_pages' => 'contact_us',
            // 'tab'            => 'general',
            'fields'         => [
                [
                    'name' => 'Concact Us Topics',
                    'id'   => 'contact_us_topics',
                    'type' => 'fieldset_text',
                    'columns' => 8,
                    'options' => [

                        'topic' => 'Topic',
                        'email'   => 'Email',
                    ],
                    'clone' => true,
                    'sort_clone'  => true,
                    'visible' => ["post_ID",  "189"]

                ],
            ],
        ];
        return $meta_boxes;
    }

    function mb_settings_page($settings_pages)
    {

        $settings_pages[] = [
            'id'          => 'contact_us',
            'option_name' => 'contact_us',
            'menu_title'  => 'Contact Us',
            'parent'      => 'theme-options',
            'tabs'        => [
                'general' => 'Read Me',         // No icon

            ],

        ];
        return $settings_pages;
    }
}
