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
            $email = sprintf("TEST-%s-%s%s@scoutpup.com", $rnd, $person->name, $person->surname);
        }

        return $email;

    }

    public static function createPassword($length = 6)
    {
        $pwd = bin2hex(openssl_random_pseudo_bytes($length));

        return $pwd;
    }

    public static function randItemFromJsonFile($file) {
        $maxLineLength = 4096;
        $handle        = @fopen($file, "r");
        $randomItem    = null;

        if ($handle) {
            $random_line = null;
            $line        = null;
            $count       = 0;

            while (($line = fgets($handle, $maxLineLength)) !== false) {
                $count++;
                // P(1/$count) probability of picking current line as random line
                if(rand() % $count == 0) {
                  $randomItem = rtrim( trim($line), ",");
                }
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
                fclose($handle);
                return null;
            } else {
                fclose($handle);
            }

        }

        // return json_decode( rtrim( trim($randomItem), ",") );
        return $randomItem;
    }

}
