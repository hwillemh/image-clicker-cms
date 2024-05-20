<?php
class SMG_Neighborhood_Partner extends SMG_Base{
   
        function __construct()
    {
        parent::__construct();
        add_action('init', array($this, 'register_posts'));
       // add_filter('rwmb_meta_boxes', [$this, 'register_meta_boxes']);
        //add_action('init', array($this, 'register_taxonomy'));
    }
    
    

    public function register_posts()
    {
        $post_types = array(
            array(
                'content_id' => 'neighborhood_partner',
                'label_plural' => 'Neighborhood Partners',
                'label_singular' => 'Partner',
                'has_archive' => false,
                'rewrite' => array('slug' => 'partners'),
                'public' => true,
                'show_in_rest' => true,
                'rest_base' => 'partners',
                'supports' => array(
                    'title',
                    'revisions',
                    'page-attributes',
                    'editor',
                    'thumbnail'
                ),
            )
        );
        $this->post_type_factory($post_types);
    }
    public function register_taxonomy()
    {
        $taxonomies[] =
            [
                'tax_id' => 'people_tax',
                'post_types' => ['person'],
                'label_singular' => 'Grouping',
                'label_plural' => 'Groupings',
            ];
        $this->taxonomy_factory($taxonomies);
    }
    
}