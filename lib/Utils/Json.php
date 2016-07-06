<?php
namespace HBI\Utils;

use HBI\HBIHelper;
use HBI\Utils\RestClient;

/**
 *
 */
class Json
{

    /**
     * [randItemFromJsonFile description]
     * @param  [type] $file [description]
     * @return [type]       [description]
     */
    public static function randItemFromFile(File $file) {
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

    public static function randItemFromURI($uri)
    {
        // Grab the response, and save it as a temp file
        // Call SELF::randItemFromFile
        // destroy the temp file
        // return the item
    }

}
