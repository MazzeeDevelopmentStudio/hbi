<?php
namespace HBI;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

/**
*
*/
class HBILog
{

    function __construct()
    {
    }

    public function foo()
    {
        $log = new \Monolog\Logger('name');
        $log->pushHandler(new Monolog\Handler\StreamHandler('app.log', Monolog\Logger::WARNING));
        $log->addWarning('Foo');
    }
}


$log = new HBILog;
$log->foo();
