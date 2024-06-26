<?php

/**
 * Upload image from URL programmatically
 *
 * @author Misha Rudrastyh
 * @link https://rudrastyh.com/wordpress/how-to-add-images-to-media-library-from-uploaded-files-programmatically.html#upload-image-from-url
 */
function smg_upload_file_by_url($image_url)
{

    // it allows us to use download_url() and wp_handle_sideload() functions
    require_once(ABSPATH . 'wp-admin/includes/file.php');

    // download to temp dir

    $temp_file = download_url($image_url);

    if (is_wp_error($temp_file)) {
        return false;
    }

    // move the temp file into the uploads directory
    $file = array(
        'name'     => basename($image_url),
        'type'     => mime_content_type($temp_file),
        'tmp_name' => $temp_file,
        'size'     => filesize($temp_file),
    );
    $sideload = wp_handle_sideload(
        $file,
        array(
            'test_form'   => false // no needs to check 'action' parameter
        )
    );

    if (!empty($sideload['error'])) {
        // you may return error message if you want
        return false;
    }

    // it is time to add our uploaded image into WordPress media library
    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => $sideload['url'],
            'post_mime_type' => $sideload['type'],
            'post_title'     => basename($sideload['file']),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $sideload['file']
    );

    if (is_wp_error($attachment_id) || !$attachment_id) {
        return false;
    }

    // update metadata, regenerate image sizes
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    wp_update_attachment_metadata(
        $attachment_id,
        wp_generate_attachment_metadata($attachment_id, $sideload['file'])
    );
    //wp_set_post_terms($attachment_id, "event", "category");
    wp_set_object_terms($attachment_id, "event", "category");

    return $attachment_id;
}
