<?php
namespace HBI\Helpers;

use HBI\HBIHelper;
use HBI\Utils\RestClient;

/**
 *
 */
class CoreAPI
{

    public static function getPageObject()
    {
        // http://dev.losethebackpain.com/api/funnel/get-funnel-page
    }

    /**
     * [getProductObject description]
     * @return [type] [description]
     */
    public static function getProductObject()
    {
        // We are especially looking for shipping rates
    }

    /**
     * [coreData description]
     * @param  String $api    [description]
     * @param  Array $fields [description]
     * @return [JSON]         [description]
     */
    protected static function coreData($api, $fields=array())
    {
        $base_url = sprintf('%s/%s', CORESERVER['development'], $api);
        $qstr     = 'key='.APIKEY;

        foreach($fields as $k => $v) {
            $qstr = sprintf('%s&%s=%s', $qstr, $k, $v);
        }

        $rest = new RestClient();
        $url  = $base_url . '?' . $qstr;

        return $rest->get( $url );
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

        $json = SELF::coreData(
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
        $json = SELF::coreData(
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
        $json = SELF::coreData(
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
        $json = SELF::coreData(
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
        $json = SELF::coreData(
                    'api/funnel/get-funnel-pages',
                    array('funnel_id'=>$fid, 'stage_id'=>$sid)
                );
        $obj  = (object)$json;
        $p    = json_decode($obj->scalar);

        return null;
    }

}
