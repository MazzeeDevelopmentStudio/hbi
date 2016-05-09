<?php
namespace HBI;

use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverBy;

/**
*
*/
class HBIFunnelHelper
{
    // For right now this is empty - will extend this class later

    private $_total;
    private $_items;

    public function totalAmountTally()
    {

    }

    public function randomlySelectAddons(RemoteWebDriver $driver)
    {
        // OPT FOR ADDONS RANDOMALY
        $addons = $driver->findElements(
            WebDriverBy::cssSelector(".addon-checkbox1.filled-in")
        );

        foreach ($addons as $addon) {
            if (rand(0, 1)) {
                $label = $driver->findElement(
                    WebDriverBy::cssSelector(
                        sprintf('label[for=%s]', $addon->getAttribute('id')) )
                );

                $label->getLocationOnScreenOnceScrolledIntoView();
                $label->click();
            }
        }
    }


}