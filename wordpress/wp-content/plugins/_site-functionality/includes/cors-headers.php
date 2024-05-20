<?php
class SMG_Cors_Headers
{

    function __construct()
    {
        // add_action('init',);
        add_action('rest_api_init', function () {

            remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');

            add_filter('rest_pre_serve_request', [$this, 'add_cors_http_header']);
        }, 15);

        add_filter('rest_pre_serve_request', [$this, 'add_cors_http_header']);
    }
    function add_cors_http_header()
    {
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', function ($value) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Expose-Headers: Link', false);

            return $value;
        });
    }
}
