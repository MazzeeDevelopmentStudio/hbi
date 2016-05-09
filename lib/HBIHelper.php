<?php
namespace HBI;

/**
 *
 */
class HBIHelper
{
    public static function createRandomEmail($person, $obscure = false)
    {
        $email = "TEST_BROKEN_EMAIL@scoutpup.com";

        if($obscure) {
            $email = sprintf("TEST-%s@scoutpup.com", uniqid());
        } else {
            $rnd = rand(1, 9);
            $email = sprintf("TEST-%s-%s%s%s@scoutpup.com", $rnd, $person->first_name, $person->last_name, $person->postal_code);
        }

        return $email;

    }
}
