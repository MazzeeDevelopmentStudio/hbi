<?php
namespace HBI;

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
    public static function getDataFromHBICoreAPI($api, $fields=array())
    {        
        $url           = sprintf('%s/%s', APISERVER[ENVIRONMENT], $api);
        $fields['key'] = APIKEY[ENVIRONMENT];
        $qstr          = http_build_query($fields);
        $ch            = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $qstr);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // print( 'Grabbing Data From URI: '.$url . '?' . $qstr.PHP_EOL  );

        $json = curl_exec($ch);

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

}
