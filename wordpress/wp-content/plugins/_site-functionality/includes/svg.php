<?php
class SMG_Svg
{
    function __construct()
    {
        add_filter('wp_check_filetype_and_ext', [$this, "wp_check_filetype_and_ext"], 10, 4);
        add_filter('upload_mimes', [$this, 'cc_mime_types']);
        add_action('admin_head', [$this, 'fix_svg']);
    }

    // Allow SVG
    function wp_check_filetype_and_ext($data, $file, $filename, $mimes)
    {
        global $wp_version;
        if ($wp_version !== '4.7.1') {
            return $data;
        }
        $filetype = wp_check_filetype($filename, $mimes);
        return [
            'ext'             => $filetype['ext'],
            'type'            => $filetype['type'],
            'proper_filename' => $data['proper_filename']
        ];
    }

    function cc_mime_types($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }


    function fix_svg()
    {
        echo '<style type="text/css">
          .attachment-266x266, .thumbnail img {
               width: 100% !important;
               height: auto !important;
          }
          </style>';
    }
}
