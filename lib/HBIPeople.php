<?php
namespace HBI;

/**
*
*/
class HBIPeople
{

    private $_data;
    private $_vdir;

    /**
     * [__construct description]
     */
    function __construct()
    {
        $this->vdir = dirname(dirname(__FILE__));

        $json = file_get_contents($this->vdir.'/data/test.json');
        $this->_data = json_decode($json);
    }

    function buildCollection($collection_size = 1000) {
        $collection = new HBIPeopleCollection;

        for ($i=0;$i<$collection_size;$i++) {
            $person = new HBIPerson;
            $pos    = array_rand($this->_data); // Grab data Randomly
            $raw    = $this->_data[$pos];
            $attrib = (object)$raw;

            $person->SetAttributes($attrib);
            $collection->add($attrib);

            $person = NULL;
            unset($person);
        }

        // Return the collection object
        return $collection;
    }
}



