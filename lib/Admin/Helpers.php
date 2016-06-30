<?php
namespace HBI\Admin;

use HBI\HBIBrowser;
use HBI\HBIHelper;

use \Facebook\WebDriver\Exception\NoSuchElementException;
use \Facebook\WebDriver\Exception\UnknownServerException;
use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverWindow;
use \Facebook\WebDriver\WebDriverActions;
use \Facebook\WebDriver\WebDriverSelect;
use \Facebook\WebDriver\WebDriverBy;

/**
*
*/
class Helpers
{

    static public function getRandomDollarAmount()
    {
        $isLargeFloat      = rand(1,-20) > 0 ? true : false;
        $isNegativeFloat   = rand(1,-100) > 0 ? true : false;
        $isRoundedToTwo    = rand(0,100) > 0 ? true : false;

        $amntBase = rand(0,9);
        $amntCent = mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
        $multiply = rand(0,10);
        $exp      = mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
        $amnt     = $amntBase+$amntCent;

        if($isLargeFloat) {
            $multiply = rand(5000,500000000) * $exp;
            print($multiply.PHP_EOL);
        }

        if($isNegativeFloat) {
            $multiply = $multiply * -1;
        }

        $amnt = round($amnt * $multiply, $isRoundedToTwo ? 2 : null);

        return $amnt;
    }

    static public function getRandomProductType(HBIBrowser $browser)
    {
        $options = $browser->webui()->getHiddenOptions("item_type_id");
        $rand    = rand(0, count($options)-1);

        return $options[$rand];
    }

    static public function getRandomProductCategory(HBIBrowser $browser)
    {
        $options = $browser->webui()->getHiddenOptions("category_ids[]");
        $rand    = rand(0, count($options)-1);

        return $options[$rand];
    }

}
