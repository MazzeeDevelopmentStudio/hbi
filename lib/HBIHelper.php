<?php
namespace HBI;

/**
 *
 */
class HBIHelper
{
    public static function createRandomEmail($person)
    {
        $rnd = rand(1, 9);
        return sprintf("TEST-%s-%s%s%s@scoutpup.com", $rnd, $person->first_name, $person->last_name, $person->postal_code);
    }
}
