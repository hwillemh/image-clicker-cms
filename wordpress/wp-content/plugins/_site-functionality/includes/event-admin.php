<?php
class SMG_Event_Admin
{
    function __construct()
    {
        add_filter("manage_event_posts_columns", [$this, 'manage_event_posts_columns']);
        add_action("manage_event_posts_custom_column", [$this, "manage_event_posts_custom_column"], 10, 2);
        //add_action('manage_posts_extra_tablenav', [$this, 'manage_posts_extra_tablenav'], 100, 2);
        add_action('restrict_manage_posts', [$this, 'manage_posts_extra_tablenav'], 100, 2);
        add_filter('pre_get_posts', [$this, 'pre_get_posts'], 100, 2);
        add_filter('months_dropdown_results', '__return_empty_array');
    }
    function pre_get_posts($query)
    {
        global $pagenow;
        if ('edit.php' != $pagenow || !$query->is_admin)
            return $query;
        if ($query->query_vars["post_type"] !== "event") return;
        if (isset($_GET["m"]) && $_GET["m"] == "all") return;
        //if (isset($_GET["action"]) && $_GET["action"] == "-1") {

        $query->set("orderby", "date");
        $query->set("order", "asc");
        $query->set(
            'date_query',
            array(
                array(
                    'after' => array(
                        'year' => date("Y"),
                        'month' => date("m"),
                        'day' => date("d")
                    ),
                    'inclusive' => true
                ),
            )
        );


        return $query;
    }

    function manage_posts_extra_tablenav($post_type)
    {
        if ($post_type != "event") return;
        global $wpdb;
        /*
        $post_status = esc_attr($_GET['post_status']);
        $post_status = in_array($post_status, $GLOBALS['avail_post_stati'])
            ? " AND post_status = {$post_status}"
            : 'all';
        'all' === $post_status and $post_status = '';
        */

        $total_page_dates = $wpdb->get_results($wpdb->prepare("
    SELECT
        YEAR( post_date )  AS year,
        MONTH( post_date ) AS month,
        post_date AS post_date,
        count( ID )        AS posts
    FROM {$wpdb->posts}
    WHERE
        post_type = %s
       
    GROUP BY
        YEAR( post_date ),
        MONTH( post_date )
    ORDER BY post_date
    ASC
", get_current_screen()->post_type));



?>
        <select name="m" id="filter-by-date">
            <option value="">Future Events</option>
            <option value="all" <?= isset($_Get["m"]) && $_GET["m"] == "all" ? "selected" : "" ?>>All Dates</option>
            <?php
            if (isset($_GET["m"])) :
                foreach ($total_page_dates as $date) : $selected = $_GET["m"] == $date->year . $date->month ? "selected" : "";  ?>
                    <option <?= $selected ?> value="<?= $date->year . $date->month ?>"><?= date("F", strtotime($date->post_date)) ?> <?= $date->year ?> </option>
            <?php
                endforeach;
            endif; ?>
        </select>
<?php
        /*

        echo sprintf("<a class='admin-button %s' href='%s' style=''>Future Events</a>", isset($_GET["show-all"]) ? "selected" : "", admin_url("edit.php?orderby=date&order=asc&s&post_status=all&post_type=event"));

        echo sprintf("<a class='admin-button' href='%s' style=''>Show All</a>", admin_url("edit.php?orderby=date&order=asc&s&post_status=all&post_type=event&show-all=true"));
        */
    }
    function manage_event_posts_columns($defaults)
    {
        d($defaults);
        $new_columns = [
            'cb' => $defaults['cb'],
            'image' => 'Image',
            'title' => $defaults['title'],
            'dates' => 'Dates',
            'taxonomy-event_series' => $defaults['taxonomy-event_series'],
            'taxonomy-event_filter' => $defaults['taxonomy-event_filter'],
            'taxonomy-event_tags' => $defaults['taxonomy-event_tags'],
            'taxonomy-event_promoters' => $defaults['taxonomy-event_promoters'],
        ];
        return $new_columns;
    }
    function manage_event_posts_custom_column($column_name, $post_id)
    {


        if ($column_name == 'image') {
            $event = get_post($post_id);
            $blocks = parse_blocks($event->post_content);
            $image_url = (isset($blocks[0]["attrs"]["image"]) && isset($blocks[0]["attrs"]["image"]["imageID"])) ?  wp_get_attachment_url($blocks[0]["attrs"]["image"]["imageID"]) : (isset($blocks[0]["attrs"]["image"]) ? $blocks[0]["attrs"]["image"]["imageUrl"] : "");
            $link = get_edit_post_link($post_id);
            echo sprintf("<a href='%s' ><img src='%s' width='120' /></a>",  $link, $image_url);
        }
        if ($column_name == 'dates') {
            $dates = rwmb_meta('dates', [], $post_id);
            $output = "";
            foreach ($dates as $date) {
                if (!isset($date["date"])) continue;
                $output .= sprintf("<div>%s</div>", date("M j, y, g:t", strtotime($date["date"])));
            }

            echo $output;
        }
    }
}
