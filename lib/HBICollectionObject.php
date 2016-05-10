<?php
namespace HBI;

use \ReflectionClass;

/**
*
*/
class HBICollectionObject
{
    public $objClassName;
    public $jsonDataFile;

    function __construct()
    {

    }

    function buildCollection($collection_size = 1000) {
        $collection = new HBICollection;

        $class = $this->objClassName;

        for ($i=0;$i<$collection_size;$i++) {
            $item   = HBIHelper::randItemFromJsonFile( $this->jsonDataFile );
            $obj    = new $class($item);

            $collection->add($obj);

            unset($obj);
        }

        // Return the single object we the collection size is only 1
        if($collection_size === 1) {
            return $collection[0];
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
