<?php
namespace HBI;

/**
 *
 */
class HBIHelper
{
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

    public static function createPassword($length = 6)
    {
        $pwd = bin2hex(openssl_random_pseudo_bytes($length));

        return $pwd;
    }

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

    public static function getPageObjectFromApi()
    {
        // http://dev.losethebackpain.com/api/funnel/get-funnel-page
    }

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
        $url  = sprintf('%s/%s', APISERVER, $api);
        $qstr = 'key='.APIKEY;

        foreach($fields as $k => $v) {
            $qstr = sprintf('%s&%s=%s', $qstr, $k, $v);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $qstr);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $json = curl_exec($ch);

        return $json;
    }

    public static function getCollectionOfFunnels()
    {
        $ptyp = array(
            "SalesPage",
            "Presell"
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

            // print_r($funnel);
        }

        return $fids;
    }

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

    public static function getListofFunnelPageDetails($fid, $sid)
    {
        $json = HBIHelper::getDataFromHBICoreAPI(
                    'api/funnel/get-funnel-pages',
                    array('funnel_id'=>$fid, 'stage_id'=>$sid)
                );
        $obj  = (object)$json;
        $p    = json_decode($obj->scalar);

        print_r($p);

        return null;

    }

// Street Number      = div.pac-container div.pac-item span.pac-item-query span.pac-matched
// Street Name        = div.pac-container div.pac-item span span.pac-matched
// City,State,Country = div.pac-container div.pac-item span

}
