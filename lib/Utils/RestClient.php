<?php
namespace HBI\Utils;

use HBI\HBIHelper;

/**
 *
 */
class RestClient
{


    public static function get($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $json = curl_exec($ch);
        unset($ch);

        return $json;
    }

}
