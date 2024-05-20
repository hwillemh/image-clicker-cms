<?php
class SMG_Menu_Image
{
    function __construct()
    {
        add_action('wp_nav_menu_item_custom_fields', [$this, "wp_nav_menu_item_custom_fields"], 10, 2);
        add_action('admin_enqueue_scripts',  [$this, 'enqueue_admin']);
        add_action('wp_update_nav_menu_item', [$this, 'wp_update_nav_menu_item'], 10, 2);
      
    }
    
    

    function enqueue_admin()
    {
        // Check if 'wp_enqueue_media' action has been executed and the post type is 'property'.
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }
        wp_enqueue_script('manage-media',  SMG_URL . 'js/menu-image.js', __FILE__, [], '1.0.0', true);

        /*
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        wp_enqueue_script('media-upload');
        */
    }

    function wp_nav_menu_item_custom_fields($item_id, $args)
    {
        $menu_item_image = get_post_meta($item_id, 'menu_item_image', true);
        debug_log($item_id);
        debug_log($menu_item_image);
        $image_url = wp_get_attachment_url((int)$menu_item_image);
        debug_log($image_url);
?>


        <style type="text/css">
            .fh-profile-upload-options th,
            .fh-profile-upload-options td,
            .fh-profile-upload-options input {
                vertical-align: top;
            }

            .user-preview-image {
                display: block;
                height: auto;
                width: 300px;
                margin-bottom: 10px;
                margin-top: 10px;
            }

            .description {
                margin-top: 20px;
                margin-bottom: 20px;

            }

            .menu-image-label {
                margin-bottom: 5px;
                display: block;
            }

            .my-manage-media-button {
                margin-top: 10px;
                margin-bottom: 10px;
            }
        </style>



        <div class="description description-wide">
            <label for="menu-image-label edit-menu-item-title">Menu Image</label>
            <img class="user-preview-image" src="<?= $image_url ?>">
            <button type='button' class="my-manage-media-button browser button button-hero" id="uploadimage">Upload Image</button><br />
            <input class="menu-image-input " name="menu_item_image[<?php echo $item_id; ?>]" type="hidden" value="<?php echo esc_attr($menu_item_image); ?>" />
        </div>





<?php
    }
}
