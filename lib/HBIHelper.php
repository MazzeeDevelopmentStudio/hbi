<?php
namespace HBI;

/**
 *
 */
class HBIHelper
{
    public static function createRandomEmail($person)
    {
        return sprintf("TEST-%s%s%s@scoutpup.com", $person->first_name, $person->last_name, $person->postal_code);
    }
}
