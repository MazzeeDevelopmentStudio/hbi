<?php
namespace HBI;

/**
*
*/
class HBIStaff
{

    private $_data;
    private $_vdir;
	private $_name;
    private $_surname;

    /**
     * [__construct description]
     */
    function __construct()
    {
        $this->vdir = dirname(dirname(__FILE__));

        $json = file_get_contents($this->vdir.'/data/people.json');
        $this->_data = json_decode($json);
    }
	
	public function staff() {}

    protected function setName($name) { $this->_name = $name; }
    public function getName() { return $this->_name; }

    protected function setSurname($surname) { $this->_surname = $surname; }
    public function getSurname() { return $this->_surname; }
	
	function setAttributes($attributes) {
        $this->setName($attributes->first_name);
        $this->setSurname($attributes->last_name);
    }
	
    function buildCollection($collection_size = 1000) {
        $collection = new HBIPeopleCollection;

        for ($i=0;$i<$collection_size;$i++) {
            $pos    = array_rand($this->_data); // Grab data Randomly
            $raw    = $this->_data[$pos];
            $attrib = (object)$raw;

            $this->setAttributes($attrib);
            $collection->add($attrib);

            //$person = NULL;
            unset(setAttributes);
        }

        // Return the collection object
        return $collection;
    }
}