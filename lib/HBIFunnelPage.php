<?php
namespace HBI;

use HBI\HBIHelper;

/**
*
*/
class HBIFunnelPage
{
    public $pageId;

    public static function getFunnelPageOffers($pageId)
    {
        $fpo = SELF::getFunnelPageObject($pageId);

        return $fpo->page->offers;
    }

    public static function getFunnelPageObject($pageId)
    {
        $json = HBIHelper::getDataFromHBICoreAPI(
                    'api/funnel/get-funnel-page',
                    array('page_id'=>$pageId)
                );

        return (object)json_decode($json);
    }

    public static function getFunnelPageObject($pageId)
    {

    }

    public static function processStage($stageType)
    {

    }

}
