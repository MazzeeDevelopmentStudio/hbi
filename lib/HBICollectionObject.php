<?php
namespace HBI;

use \ReflectionClass;

/**
*
*/
class HBICollectionObject
{
    private $_data_path;
    private $_objname;
    private $_data;
    private $_vdir;

    function __construct($datapath)
    {

        $this->_data_path = $datapath;
        $data             = file_get_contents($datapath);
        $this->_data      = json_decode($data);
    }

    function buildCollection($collection_size = 1000) {
        $collection = new HBICollection;

        $class = $this->objClassName;
        $obj   = new $class;

        for ($i=0;$i<$collection_size;$i++) {
            $item   = $obj->randItemFromJsonFile( $this->jsonDataFile );
print_r($item);
            $pos    = array_rand($this->_data); // Grab data Randomly
            $raw    = $this->_data[$pos];
            $attrib = (object)$raw;

            $collection->add($attrib);
        }

        // Return the collection object
        return $collection;
    }

    function getObjName()
    {
        return $this->objClassName;
    }

    function setObjectName()
    {
        $class              = get_class($this);
        $objName            = str_replace("HBI\HBI", null, $class);
        $this->_object_name = sprintf('HBI%s', $objName);
    }
}
