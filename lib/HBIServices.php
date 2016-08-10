<?php
namespace HBI;

/**
*
*/
class HBIServices extends HBICollectionObject
{
    public $objClassName = 'HBI\HBIService';
    public $jsonDataFile = DATADIR.'/services.json';

    function __construct()
    {
    	// We need to make a random file now
    }

}