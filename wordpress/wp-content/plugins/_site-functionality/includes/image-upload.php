<?php
class SMG_Image_Upload
{

    static function upload_image($url, $post_id)
    {
        $image = "";
        if ($url != "") {

            $file = array();
            $file['name'] = $url;
            $file['tmp_name'] = download_url($url);

            if (is_wp_error($file['tmp_name'])) {
                @unlink($file['tmp_name']);
                var_dump($file['tmp_name']->get_error_messages());
            } else {
                $attachmentId = media_handle_sideload($file, $post_id);

                if (is_wp_error($attachmentId)) {
                    @unlink($file['tmp_name']);
                    var_dump($attachmentId->get_error_messages());
                } else {
                    $image = wp_get_attachment_url($attachmentId);
                }
            }
        }
        return $image;
    }
}
