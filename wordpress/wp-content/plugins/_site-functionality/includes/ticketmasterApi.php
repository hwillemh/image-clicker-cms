<?php
class SMG_TicketmasterAPI
{
    function __construct()
    {
    }
    static public function get_event($ticketmaster_id)
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf('https://app.ticketmaster.com/discovery/v2/events/%s?apikey=GAIoXHoPmgJWTD04yBnDj0VxOf5A3Rfx', $ticketmaster_id),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $TM_response = curl_exec($curl);
        curl_close($curl);
        $TM_array = json_decode($TM_response);
        return $TM_array;
    }
    static public function get_events()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://app.ticketmaster.com/discovery/v2/events?countryCode=US&venueId=KovZpZAFdJtA&startDateTime=2023-08-01T00%3A00%3A00Z&size=100&sort=date%2Casc&apikey=GAIoXHoPmgJWTD04yBnDj0VxOf5A3Rfx',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $TM_response = curl_exec($curl);
        curl_close($curl);
        $TM_array = json_decode($TM_response);
        
        return $TM_array;
    }
}
