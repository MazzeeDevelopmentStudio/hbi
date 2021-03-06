<?php
namespace HBI;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

// TODO: We should have logging levels, using PHP Error like levesl.
// This way we can have output for only those things we truly need... and 'debug'
// levels when we need that


/**
*
*/
class HBILog
{
    private $logfile;

    function __construct($path, $id)
    {
        $this->setLogFilePath($path, $id);
    }

    private function setLogFilePath($path, $id) {
        $date = date('Ymd');
        $dir  = sprintf('%s/%s', $path, $date);

        if(!file_exists($dir)) {
            mkdir($dir);
        }

        $this->logfile = sprintf('%s/%s.json', $dir, $id);
    }

    public function setLogFilePathByID($path, $id, $filename)
    {
        $dir  = sprintf('%s/%s', $path, $id);

        if(!file_exists($dir)) {
            mkdir($dir);
        }

        $this->logfile = sprintf('%s/%s.json', $dir, $filename);
    }

    public function writeToMonoLog()
    {
        $log = new \Monolog\Logger('name');
        $log->pushHandler(new Monolog\Handler\StreamHandler('app.log', Monolog\Logger::WARNING));
        $log->addWarning('Foo');
    }

    public function writeExceptionToLogFile($exception, $e)
    {
        $content = array(
            "Exception"=>"Exception Thrown: $exception",
            print_r($e, true)
        );

        // print("EXCEPTION: $exception".PHP_EOL);
        $this->writeToLogFile($content);
    }

    public function writeToLogFile($content)
    {
        $data = array();

        if(file_exists($this->logfile)) {
            $file = file_get_contents($this->logfile);
            $data = json_decode($file);

            unset($file);//prevent memory leaks for large json.
        }

        //insert data here
        $data[] = $content;

        //save the file
        file_put_contents($this->logfile,json_encode($data),LOCK_EX);
        unset($data);//release memory
    }

}

