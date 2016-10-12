<?php
namespace HBI;

use \Facebook\WebDriver\Exception\NoSuchElementException;
use \Facebook\WebDriver\Exception\TimeOutException;

/**
 * TODO: Move Helpers into the HBI\Utils Namespace?
 *       OR move them into the HBI\Helpers Namespace?
 */
class HBIHelper
{
    /**
     * [createRandomEmail description]
     * @param  [type]  $person  [description]
     * @param  boolean $obscure [description]
     * @return [type]           [description]
     */
    public static function createRandomEmail($person, $obscure = false)
    {
        $email = "TEST_BROKEN_EMAIL@scoutpup.com";

        if($obscure) {
            $email = sprintf("%s@scoutpup.com", uniqid());
        } else {
            $rnd = rand(1, 9);
            $email = sprintf("%s-%s%s@scoutpup.com", $rnd, $person->name, $person->surname);
        }

        return $email;

    }

    /**
     * [createPassword description]
     * @param  integer $length [description]
     * @return [type]          [description]
     */
    public static function createPassword($length = 6)
    {
        $pwd = bin2hex(openssl_random_pseudo_bytes($length));

        return $pwd;
    }

    /**
     * [randItemFromJsonFile description]
     * @param  [type] $file [description]
     * @return [type]       [description]
     */
    public static function randItemFromJsonFile($file) {
        $maxLineLength = 4096;
        $handle        = @fopen($file, "r");
        $randomItem    = null;

        if ($handle) {
            $random_line = null;
            $line        = null;
            $count       = 0;

            while (($line = fgets($handle, $maxLineLength)) !== false) {
                $count++;
                // P(1/$count) probability of picking current line as random line
                if(rand() % $count == 0) {
                  $randomItem = rtrim( trim($line), ",");
                }
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
                fclose($handle);
                return null;
            } else {
                fclose($handle);
            }

        }

        // return json_decode( rtrim( trim($randomItem), ",") );
        return $randomItem;
    }

    /**
     * [getArrayOfWeightedValues description]
     * @param  Array       $arrayOfWeightedValues [description]
     * @param  Int|integer $resultSize            [description]
     * @return Array                              [description]
     */
    public static function getArrayOfWeightedValues(Array $arrayOfWeightedValues, Int $resultSize)
    {
        $resultSet    = array();
        $sumOfWeights = array_sum($arrayOfWeightedValues);
        $resultSize   = $resultSize < 100 ? 100 : $resultSize; // 100 (min) to get real results

        for($i=0; $i < $resultSize; $i++) {
            // choose a random between 1 and the sum of the weights.
            $random = rand(1, $sumOfWeights);
            foreach($arrayOfWeightedValues as $name => $weighting) {
                // decrement the random by the current weighting.
                $random -= $weighting;
                // The larger the weighting, the more likely random is less than zero.
                if($random <= 0) {
                    $resultSet[] = $name;
                    break;
                }
            }
        }

        return $resultSet;
    }

    /**
     * [getPageObjectFromApi description]
     * @return [type] [description]
     */
    public static function getPageObjectFromApi()
    {
        // http://dev.losethebackpain.com/api/funnel/get-funnel-page
    }

    /**
     * [getProductObjectFromApi description]
     * @return [type] [description]
     */
    public static function getProductObjectFromApi()
    {
        // We are especially looking for shipping rates
    }

    /**
     * [getDataFromHBICoreAPI description]
     * @param  String $api    [description]
     * @param  Array $fields [description]
     * @return [JSON]         [description]
     */
    public static function getDataFromHBICoreAPI($api, $fields=array(), $env=ENVIRONMENT)
    {        
        $fields['AUTOMATION'] = 'TRUE';

        $url           = sprintf('%s/%s', APISERVER[$env], $api);
        $fields['key'] = APIKEY[$env];
        $qstr          = http_build_query($fields);
        $ch            = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $qstr);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // error_log( 'Grabbing Data From URI: '.$url . '?' . $qstr  );

        $json = curl_exec($ch);
        unset($ch);

        // If the CURL method of pulling the JSON returns a null
        // Simply do a file_get_contents (due to a curl bug)
        if(!$json) {
          $json = file_get_contents($url . '?' . $qstr);
        }

        return $json;
    }

    /**
     * [getCollectionOfFunnels description]
     * @return [type] [description]
     */
    public static function getCollectionOfFunnels()
    {
        // TODO: Waiting on bug fix for Presell Integration
        $ptyp = array(
            "SalesPage"
            // "Presell"
            );

        $json = HBIHelper::getDataFromHBICoreAPI(
                    'api/funnel/get-funnel-pages'
                );

        $obj  = (object)$json;
        $f    = json_decode($obj->scalar);
        $fnls = array();

        foreach ($f->pages as $page) {
            if(in_array($page->stage->name,$ptyp)) {
                $fnls[$page->stage->funnel_id][] = $page;
            }
        }

        return $fnls;
    }

    public static function getCollectionOfFunnelsStartAtOrderForm()
    {
        // TODO: Waiting on bug fix for Presell Integration
        $ptyp = array("OrderForm");
        $json = HBIHelper::getDataFromHBICoreAPI(
                    'api/funnel/get-funnel-pages'
                );

        $obj  = (object)$json;
        $f    = json_decode($obj->scalar);
        $fnls = array();

        foreach ($f->pages as $page) {
            if(in_array($page->stage->name,$ptyp)) {
                $fnls[$page->stage->funnel_id][] = $page;
            }
        }

        return $fnls;
    }

    /**
     * [getListOfFunnelIds description]
     * @return [type] [description]
     */
    public static function getListOfFunnelIds()
    {
        $json = HBIHelper::getDataFromHBICoreAPI(
                    'api/funnel/get-funnels',
                    array('active'=>1)
                );
        $obj  = (object)$json;
        $f    = json_decode($obj->scalar);
        $fids = array();

        foreach ($f->funnels as $funnel) {
            $fids[] = $funnel->id;
        }

        return $fids;
    }

    /**
     * [getListOfFunnelStageIds description]
     * @param  [type] $fid [description]
     * @return [type]      [description]
     */
    public static function getListOfFunnelStageIds($fid)
    {
        $json = HBIHelper::getDataFromHBICoreAPI(
                    'api/funnel/get-funnel-stages',
                    array('funnel_id'=>$fid)
                );
        $obj  = (object)$json;
        $s    = json_decode($obj->scalar);
        $sids = array();

        foreach ($s->stages as $stage) {
            $sids[] = $stage->id;
        }

        return $sids;
    }

    /**
     * [getListofFunnelPageDetails description]
     * @param  [type] $fid [description]
     * @param  [type] $sid [description]
     * @return [type]      [description]
     */
    public static function getListofFunnelPageDetails($fid, $sid)
    {
        $json = HBIHelper::getDataFromHBICoreAPI(
                    'api/funnel/get-funnel-pages',
                    array('funnel_id'=>$fid, 'stage_id'=>$sid)
                );
        $obj  = (object)$json;
        $p    = json_decode($obj->scalar);

        return null;
    }


    public static function checkFor404s($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if($httpCode == 404) {
            /* Handle 404 here. */
        }

        curl_close($handle);

        /* Handle $response here. */
    }

    public static function csvToArray($filename='', $delimiter=',')
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;
        
        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public static function csvToJson($filename='', $delimiter=',')
    {
        $data = SELF::csvToArray($filename, $delimiter);

        return json_encode($data);
    }

    public static function csvToObject($filename='', $delimiter=',')
    {
        $json = SELF::csvToJson($filename, $delimiter);

        return json_decode($json);
    }

    public static function randomQAlogFile()
    {
        print("FUNCTION : randomQAlogFile".PHP_EOL);

        $dirs     = array_filter(glob(LOGSDIR.'/*'), 'is_dir');
        $randdir  = array_rand($dirs);
        $files    = glob($dirs[ $randdir ] . '/*.json');

        print("INFO     : Searching the directory ".$dirs[ $randdir ].PHP_EOL);
        // SELF::xprint($randdir,'randdir');
        // SELF::xprint($dirs,'dirs');

        $randfile = array_rand($files);
        print("INFO     : Grabbing the file in POS ".$randfile.PHP_EOL);
        // SELF::xprint($randfile,'randfile');
        // SELF::xprint($files,'files');

        if(isset($files[$randfile])) {
            print("INFO     : Grabbing Log File ".$files[$randfile].PHP_EOL);
            return $files[$randfile];    
        }

        SELF::xprint($files,'files');        

        return null;
    }

    public static function xprint($v, $varname)
    {
        print("DEBUGOUT : $".$varname.PHP_EOL);

        print_r( $v );

        print(PHP_EOL);
        print(PHP_EOL);
    }

    protected function isErrorBoxPresent(HBIBrowser $browser)
    {
        $errorClass = ".gritter-item-wrapper.growl-danger";
        $isError    = false;

        try {
            $browser->driver()->wait(5, 50)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector($errorClass)
                )
            );
            $isError = true;
        } catch (TimeOutException $e) {
            // Don't really need this, but hey!
            $isError = false;
        } catch (NoSuchElementException $e) {
            $isError = false;            
        }

        return $isError;
    }

    public static function getCountryCode($country_name)
    {
        $country_codes = array (
          'AF' => 'Afghanistan',
          'AX' => 'Åland Islands',
          'AL' => 'Albania',
          'DZ' => 'Algeria',
          'AS' => 'American Samoa',
          'AD' => 'Andorra',
          'AO' => 'Angola',
          'AI' => 'Anguilla',
          'AQ' => 'Antarctica',
          'AG' => 'Antigua and Barbuda',
          'AR' => 'Argentina',
          'AU' => 'Australia',
          'AT' => 'Austria',
          'AZ' => 'Azerbaijan',
          'BS' => 'Bahamas',
          'BH' => 'Bahrain',
          'BD' => 'Bangladesh',
          'BB' => 'Barbados',
          'BY' => 'Belarus',
          'BE' => 'Belgium',
          'BZ' => 'Belize',
          'BJ' => 'Benin',
          'BM' => 'Bermuda',
          'BT' => 'Bhutan',
          'BO' => 'Bolivia',
          'BA' => 'Bosnia and Herzegovina',
          'BW' => 'Botswana',
          'BV' => 'Bouvet Island',
          'BR' => 'Brazil',
          'IO' => 'British Indian Ocean Territory',
          'BN' => 'Brunei Darussalam',
          'BG' => 'Bulgaria',
          'BF' => 'Burkina Faso',
          'BI' => 'Burundi',
          'KH' => 'Cambodia',
          'CM' => 'Cameroon',
          'CA' => 'Canada',
          'CV' => 'Cape Verde',
          'KY' => 'Cayman Islands',
          'CF' => 'Central African Republic',
          'TD' => 'Chad',
          'CL' => 'Chile',
          'CN' => 'China',
          'CX' => 'Christmas Island',
          'CC' => 'Cocos (Keeling) Islands',
          'CO' => 'Colombia',
          'KM' => 'Comoros',
          'CG' => 'Congo',
          'CD' => 'Zaire',
          'CK' => 'Cook Islands',
          'CR' => 'Costa Rica',
          'CI' => 'Côte D\'Ivoire',
          'HR' => 'Croatia',
          'CU' => 'Cuba',
          'CY' => 'Cyprus',
          'CZ' => 'Czech Republic',
          'DK' => 'Denmark',
          'DJ' => 'Djibouti',
          'DM' => 'Dominica',
          'DO' => 'Dominican Republic',
          'EC' => 'Ecuador',
          'EG' => 'Egypt',
          'SV' => 'El Salvador',
          'GQ' => 'Equatorial Guinea',
          'ER' => 'Eritrea',
          'EE' => 'Estonia',
          'ET' => 'Ethiopia',
          'FK' => 'Falkland Islands (Malvinas)',
          'FO' => 'Faroe Islands',
          'FJ' => 'Fiji',
          'FI' => 'Finland',
          'FR' => 'France',
          'GF' => 'French Guiana',
          'PF' => 'French Polynesia',
          'TF' => 'French Southern Territories',
          'GA' => 'Gabon',
          'GM' => 'Gambia',
          'GE' => 'Georgia',
          'DE' => 'Germany',
          'GH' => 'Ghana',
          'GI' => 'Gibraltar',
          'GR' => 'Greece',
          'GL' => 'Greenland',
          'GD' => 'Grenada',
          'GP' => 'Guadeloupe',
          'GU' => 'Guam',
          'GT' => 'Guatemala',
          'GG' => 'Guernsey',
          'GN' => 'Guinea',
          'GW' => 'Guinea-Bissau',
          'GY' => 'Guyana',
          'HT' => 'Haiti',
          'HM' => 'Heard Island and Mcdonald Islands',
          'VA' => 'Vatican City State',
          'HN' => 'Honduras',
          'HK' => 'Hong Kong',
          'HU' => 'Hungary',
          'IS' => 'Iceland',
          'IN' => 'India',
          'ID' => 'Indonesia',
          'IR' => 'Iran, Islamic Republic of',
          'IQ' => 'Iraq',
          'IE' => 'Ireland',
          'IM' => 'Isle of Man',
          'IL' => 'Israel',
          'IT' => 'Italy',
          'JM' => 'Jamaica',
          'JP' => 'Japan',
          'JE' => 'Jersey',
          'JO' => 'Jordan',
          'KZ' => 'Kazakhstan',
          'KE' => 'KENYA',
          'KI' => 'Kiribati',
          'KP' => 'Korea, Democratic People\'s Republic of',
          'KR' => 'Korea, Republic of',
          'KW' => 'Kuwait',
          'KG' => 'Kyrgyzstan',
          'LA' => 'Lao People\'s Democratic Republic',
          'LV' => 'Latvia',
          'LB' => 'Lebanon',
          'LS' => 'Lesotho',
          'LR' => 'Liberia',
          'LY' => 'Libyan Arab Jamahiriya',
          'LI' => 'Liechtenstein',
          'LT' => 'Lithuania',
          'LU' => 'Luxembourg',
          'MO' => 'Macao',
          'MK' => 'Macedonia, the Former Yugoslav Republic of',
          'MG' => 'Madagascar',
          'MW' => 'Malawi',
          'MY' => 'Malaysia',
          'MV' => 'Maldives',
          'ML' => 'Mali',
          'MT' => 'Malta',
          'MH' => 'Marshall Islands',
          'MQ' => 'Martinique',
          'MR' => 'Mauritania',
          'MU' => 'Mauritius',
          'YT' => 'Mayotte',
          'MX' => 'Mexico',
          'FM' => 'Micronesia, Federated States of',
          'MD' => 'Moldova, Republic of',
          'MC' => 'Monaco',
          'MN' => 'Mongolia',
          'ME' => 'Montenegro',
          'MS' => 'Montserrat',
          'MA' => 'Morocco',
          'MZ' => 'Mozambique',
          'MM' => 'Myanmar',
          'NA' => 'Namibia',
          'NR' => 'Nauru',
          'NP' => 'Nepal',
          'NL' => 'Netherlands',
          'AN' => 'Netherlands Antilles',
          'NC' => 'New Caledonia',
          'NZ' => 'New Zealand',
          'NI' => 'Nicaragua',
          'NE' => 'Niger',
          'NG' => 'Nigeria',
          'NU' => 'Niue',
          'NF' => 'Norfolk Island',
          'MP' => 'Northern Mariana Islands',
          'NO' => 'Norway',
          'OM' => 'Oman',
          'PK' => 'Pakistan',
          'PW' => 'Palau',
          'PS' => 'Palestinian Territory, Occupied',
          'PA' => 'Panama',
          'PG' => 'Papua New Guinea',
          'PY' => 'Paraguay',
          'PE' => 'Peru',
          'PH' => 'Philippines',
          'PN' => 'Pitcairn',
          'PL' => 'Poland',
          'PT' => 'Portugal',
          'PR' => 'Puerto Rico',
          'QA' => 'Qatar',
          'RE' => 'Réunion',
          'RO' => 'Romania',
          'RU' => 'Russian Federation',
          'RW' => 'Rwanda',
          'SH' => 'Saint Helena',
          'KN' => 'Saint Kitts and Nevis',
          'LC' => 'Saint Lucia',
          'PM' => 'Saint Pierre and Miquelon',
          'VC' => 'Saint Vincent and the Grenadines',
          'WS' => 'Samoa',
          'SM' => 'San Marino',
          'ST' => 'Sao Tome and Principe',
          'SA' => 'Saudi Arabia',
          'SN' => 'Senegal',
          'RS' => 'Serbia',
          'SC' => 'Seychelles',
          'SL' => 'Sierra Leone',
          'SG' => 'Singapore',
          'SK' => 'Slovakia',
          'SI' => 'Slovenia',
          'SB' => 'Solomon Islands',
          'SO' => 'Somalia',
          'ZA' => 'South Africa',
          'GS' => 'South Georgia and the South Sandwich Islands',
          'ES' => 'Spain',
          'LK' => 'Sri Lanka',
          'SD' => 'Sudan',
          'SR' => 'Suriname',
          'SJ' => 'Svalbard and Jan Mayen',
          'SZ' => 'Swaziland',
          'SE' => 'Sweden',
          'CH' => 'Switzerland',
          'SY' => 'Syrian Arab Republic',
          'TW' => 'Taiwan, Province of China',
          'TJ' => 'Tajikistan',
          'TZ' => 'Tanzania, United Republic of',
          'TH' => 'Thailand',
          'TL' => 'Timor-Leste',
          'TG' => 'Togo',
          'TK' => 'Tokelau',
          'TO' => 'Tonga',
          'TT' => 'Trinidad and Tobago',
          'TN' => 'Tunisia',
          'TR' => 'Turkey',
          'TM' => 'Turkmenistan',
          'TC' => 'Turks and Caicos Islands',
          'TV' => 'Tuvalu',
          'UG' => 'Uganda',
          'UA' => 'Ukraine',
          'AE' => 'United Arab Emirates',
          'GB' => 'United Kingdom',
          'US' => 'United States',
          'UM' => 'United States Minor Outlying Islands',
          'UY' => 'Uruguay',
          'UZ' => 'Uzbekistan',
          'VU' => 'Vanuatu',
          'VE' => 'Venezuela',
          'VN' => 'Viet Nam',
          'VG' => 'Virgin Islands, British',
          'VI' => 'Virgin Islands, U.S.',
          'WF' => 'Wallis and Futuna',
          'EH' => 'Western Sahara',
          'YE' => 'Yemen',
          'ZM' => 'Zambia',
          'ZW' => 'Zimbabwe',
        );

        return array_search($country_name, $country_codes);
    }

    public static function createRandomPhoneNumber()
    {
        $number = rand(1111111111, 9999999999);
        $mask   = rand(1,8);

        return SELF::formatPhoneNumber($number, $mask);
    }

    /**
     * [formatPhoneNumber description]
     * @param  [type] $number [description]
     * @param  [type] $mask   [description]
     * @return [type]         [description]
     * TODO: Fix \n (return) issue
     */
    public static function formatPhoneNumber($number, $mask)
    {
        // $val_num = SELF::validatePhoneNumber( $number );
        $val_num = true;

        if(!$val_num && !is_string ( $number ) ) {
            echo "Number $number is not a valid phone number! \n";
            return false;
        }

        if(( $mask == 1 ) || ( $mask == 'xxx xxx xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1 $2 $3', $number);
            return $phone;
        }

        if(( $mask == 2 ) || ( $mask == 'xxx xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1 $2.$3', $number);
            return $phone;
        }

        if(( $mask == 3 ) || ( $mask == 'xxx.xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1.$2.$3', $number);
            return $phone;
        }

        if(( $mask == 4 ) || ( $mask == '(xxx) xxx xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2 $3', $number);
            return $phone;
        }

        if(( $mask == 5 ) || ( $mask == '(xxx) xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2.$3', $number);
            return $phone;
        }

        if(( $mask == 6 ) || ( $mask == '(xxx).xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1).$2.$3', $number);
            return $phone;
        }

        if(( $mask == 7 ) || ( $mask == '(xxx) xxx-xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2-$3', $number);
            return $phone;
        }

        if(( $mask == 8 ) || ( $mask == '(xxx)-xxx-xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1)-$2-$3', $number);
            return $phone;
        }

        return false;
    }

}
