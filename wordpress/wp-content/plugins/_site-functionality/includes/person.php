<?php
class SMG_Person extends SMG_Base
{

    function __construct()
    {
        parent::__construct();
        add_action('init', array($this, 'register_posts'));
        add_filter('rwmb_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('init', array($this, 'register_taxonomy'));
        add_action('restrict_manage_posts', [$this, 'restrict_manage_posts']);
        add_filter('enter_title_here', [$this, 'wpse213979_new_title_text'], 10, 2);
    }
    function wpse213979_new_title_text($title, $post)
    {
        if ('person' == $post->post_type) { // Movie is the cpt name
            $title = 'Name ...'; // The text you want to be shown
        }
        return $title;
    }

    function register_meta_boxes($meta_boxes)
    {
        $meta_boxes[] = array(
            'title' => 'Meta data',
            'id'    => 'person-meta',
            'post_types' => ['person'],
            'class' => 'person-meta',
            'fields' => [
                [
                    "id"    => "title",
                    "name"  => "Job Title",
                    "type"  => "text",
                ],
                [
                    "type" => "divider",
                ],
                [
                    "id"    => "phone",
                    "name"  => "Phone Number",
                    "type"  => "text",
                ],
                [
                    "type" => "divider",
                ],
                [
                    "id"    => "email",
                    "name"  => "Email",
                    "type"  => "email",
                ],
                [
                    "type" => "divider",
                ],

                [
                    "id"    => "grouping",
                    "name"  => "Grouping",
                    "type"  => "taxonomy",
                    "taxonomy"  => "people_tax",
                    "multiple" => true,
                    //"admin_columns" => "after title",
                ],

            ]
        );
        return $meta_boxes;
    }

    public function register_posts()
    {
        $post_types = array(
            array(
                'content_id' => 'person',
                'label_plural' => 'Staff and Leadership',
                'label_singular' => 'Person',
                'has_archive' => false,
                'rewrite' => array('slug' => 'person'),
                'public' => true,
                'show_in_rest' => true,
                'rest_base' => 'person',
                'supports' => array(
                    'title',
                    'revisions',
                    'page-attributes',
                ),
            )
        );
        $this->post_type_factory($post_types);
    }
    public function register_taxonomy()
    {
        /* $taxonomies[] =
            [
                'tax_id' => 'people_tax',
                'post_types' => ['person', 'custom_post'],
                'label_singular' => 'Grouping',
                'label_plural' => 'Groupings',
            ];
            */
        register_taxonomy('people_tax', ['person'], array(
            // Hierarchical taxonomy (like categories)
            'hierarchical' => true,
            'meta_box_cb' => false,
            'show_admin_column' => true,
            'show_in_rest' => true,
            // This array of options controls the labels displayed in the WordPress Admin UI
            'labels' => array(
                'name' => _x('Grouping', 'taxonomy general name'),
                'singular_name' => _x('Grouping', 'taxonomy singular name'),
                'search_items' =>  __('Search Groupings'),
                'all_items' => __('All Groupings'),
                'parent_item' => __('Parent Grouping'),
                'parent_item_colon' => __('Parent Grouping:'),
                'edit_item' => __('Edit Grouping'),
                'update_item' => __('Update Grouping'),
                'add_new_item' => __('Add New Grouping'),
                'new_item_name' => __('New Grouping Name'),
                'menu_name' => __('Groupings'),
            ),
            // Control the slugs used for this taxonomy
            'rewrite' => array(
                'slug' => 'groupings', // This controls the base slug that will display before each term
                'with_front' => false, // Don't display the category base before "/locations/"
                //'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
            ),
        ));
        //$this->taxonomy_factory($taxonomies);
    }



    function restrict_manage_posts($post_type)
    {

        // do nothing it it is not a post type we need
        if ('person' !== $post_type) {
            return;
        }

        $taxonomy_name = 'people_tax';

        // get all terms of a specific taxonomy
        $courses = get_terms(
            array(
                'taxonomy' => $taxonomy_name,
                'hide_empty' => false
            )
        );
        // selected taxonomy from URL
        $selected = isset($_GET[$taxonomy_name]) && $_GET[$taxonomy_name] ? $_GET[$taxonomy_name] : '';

        if ($courses) {
?>
            <select name="<?php echo $taxonomy_name ?>">
                <option value="">All Groupings</option>
                <?php
                foreach ($courses as $course) {
                ?><option value="<?php echo $course->slug ?>" <?php selected($selected, $course->slug) ?>><?php echo $course->name ?></option><?php
                                                                                                                                            }
                                                                                                                                                ?>
            </select>
<?php
        }
    }
}
