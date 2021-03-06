<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;

use HBI\Admin\Actions;
use HBI\Admin\Helpers;
use HBI\HBIBrowser;
use HBI\HBIHelper;
use HBI\HBIProduct;
use HBI\HBIProducts;

/**
 *
 */
class AddProduct extends Actions
{
    private $browser;
    private $product;
    private $log;

    /**
     * [__construct description]
     * @param HBIBrowser $browser [description]
     * @param [type]     $person  [description]
     * @param [type]     $type    [description]
     */
    function __construct(HBIBrowser $browser, $product=null)
    {
        parent::__construct($browser);
        
        $this->browser = $browser;
        $this->log     = &$GLOBALS['HBILog'];
        $this->product = !empty($product) ? $product : $this->defineRandomProduct();
    }

    /**
     * [__destruct description]
     */
    function __destruct()
    {
        // TODO: Change this into a dynamic parent method
        error_log( print_r($this->product, true) );
    }

    public function add()
    {
        print("FUNCTION : AddProduct::add".PHP_EOL);

        $this->openAddPanel();
        $this->addProductDataToForm();

        $this->clickSaveButton();

        
        $this->clickDoneButton();
    }

    // TODO: We should just create very random data instead of
    // relying on the json file
    public function defineRandomProduct()
    {
        print("FUNCTION : AddProduct::defineRandomProduct".PHP_EOL);

        $prod    = new HBIProducts;
        $product = $prod->buildCollection(1);

        $this->testifyProductDetails($product);

        $product->retail   = Helpers::getRandomDollarAmount();
        $product->cogs     = Helpers::getRandomDollarAmount();

        $this->openAddPanel();
        $product->category = Helpers::getRandomProductCategory($this->browser);

        $this->clickDoneButton();

        print("ACTION   : ADDED CATEGORY VALUE".PHP_EOL);

        if(!isset($product->type)) {
            print("WARNING  : NO PRODUCT TYPE IS SET".PHP_EOL);
            $product->type = Helpers::getRandomProductType($this->browser);
        }


        $this->log->writeToLogFile($product);

        print("DATA     : ".json_encode( $product ).PHP_EOL);

        return $product;

    }

    public function addProductDataToForm()
    {
        print("FUNCTION : AddProduct::addProductDataToForm".PHP_EOL);

        // Check if modal is now visible
        $this->browser->waitForElement(
            WebDriverBy::cssSelector('div.modal-content')
        );

        // Enter field Values
        // TODO: Move to dynamic referencing model like in Funnels
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('sku'),
            $this->product->sku
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="name"]'),
            $this->product->name
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('description'),
            $this->product->description
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('retail'),
            !rand(0,3) ?  floatval($this->product->retail) : $this->product->retail
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('cogs'),
            !rand(0,3) ?  floatval($this->product->cogs) : $this->product->cogs
        );

        $this->setProductCategory( $this->product->category );
        $this->setProductType( $this->product->type );

        if($this->product->type == "Physical Product") {
            // if Physical -> Provide Product Dimensions
            $this->setProductDimensions();
        } elseif($this->product->type == "Digital Product") {
            // If Digital -> Provide Download URL
            $this->setProductDownloadUrl();
        }
    }

    public static function testifyProductDetails(HBIProduct &$product, $prefix="QA-")
    {
        $product->sku         = sprintf('%s%s%s', $prefix, $product->sku, rand(0,100));
        $product->name        = sprintf('[%sTEST] %s', $prefix, $product->name);
        $product->description = sprintf('[%sTEST Product]%s%s', $prefix, PHP_EOL, $product->description);
    }

    protected function setProductCategory($category)
    {
        $this->browser->webui()->clearField("s2id_autogen1","id");
        $this->browser->webui()->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen1"),
            $category
        );
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".select2-match")
        );
    }

    protected function setProductType($type)
    {
        $this->browser->webui()->clearField("s2id_autogen2","id");
        // $this->browser->webui()->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen2"),
            $type
        );
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".select2-match")
        );
    }

    protected function setProductDimensions()
    {
        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="length"]'),
            $this->product->length
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="height"]'),
            $this->product->height
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="depth"]'),
            $this->product->depth
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="weight"]'),
            $this->product->weight
        );
    }

    protected function setProductDownloadUrl()
    {
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('download_url'),
            $this->product->downloadurl
        );
    }


}
