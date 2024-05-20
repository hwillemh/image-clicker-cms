<?php
class SMG_Force_Login_Bypass
{
    function __construct()
    {
        add_filter( 'rest_authentication_errors', [$this,'forcelogin_bypass'], 20 );
    }

    /**
     * Bypass Force Login to allow for exceptions.
     *
     * @param bool $bypass Whether to disable Force Login. Default false.
     * @param string $visited_url The visited URL.
     * @return bool
     */
    function forcelogin_bypass($bypass)
    { 
        // Allow 'wp-json' to be publicly accessible
        if (strpos($_SERVER['REQUEST_URI'],  'wp-json')) {
            return true;
        }

        return $bypass;
    }
}
