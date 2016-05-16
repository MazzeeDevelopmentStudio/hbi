<?php
namespace HBI;

/**
*
*/
class HBIProducts
{

    private $_data;
    private $_vdir;

    /**
     * [__construct description]
     */
    function __construct()
    {
        $this->vdir = dirname(dirname(__FILE__));

        $json = file_get_contents($this->vdir.'/data/products.json');
        $this->_data = json_decode($json);
    }

    function buildCollection($collection_size = 1000) {
        $collection = new HBICollection;

        for ($i=0;$i<$collection_size;$i++) {
            $product = new HBIProduct;
            $pos     = array_rand($this->_data); // Grab data Randomly
            $raw     = $this->_data[$pos];
            $attrib  = (object)$raw;

            $product->SetAttributes($attrib);
            $collection->add($attrib);

            $product = NULL;
            unset($product);
        }

        // Return the collection object
        return $collection;
    }
}



