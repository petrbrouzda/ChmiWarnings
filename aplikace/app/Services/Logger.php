<?php

declare(strict_types=1);

namespace App\Services;

use Nette;
use Nette\Utils\DateTime;


/**
 * Logger s denně rotovanými soubory.
 * 
 * Možné je statické použití:
 *      Logger::log( 'soubor', Logger::ERROR , "Zprava" ); 
 * které zapíše do 
 *      log/soubor.YYYY-MM-DD.txt
 * obsah
 *      HH:MM:SS ERR Zprava
 * 
 * Dále je možné dynamické použití:
 *      $logger = new Logger( 'soubor' );
 *      $logger->write( Logger::ERROR, "Zprava" );
 * které udělá totéž.
 * Nicméně dynamické použití umožňuje dále toto:
 *      $logger->setContext( 'user1,192.168.32.1' );
 *      $logger->write( Logger::ERROR, "Zprava" );
 * a to zapíše 
 *      HH:MM:SS ERR [user1,192.168.32.1] Zprava
 * Tj. pro paralelní zpracování dat z více zdrojů je možné je odlišit kontextem, kontext se přidává ke všem
 * dalším ->write() až do okamžiku ->setContext(NULL);
 */
class Logger 
{
    use Nette\SmartObject;

    private $fileBase;

    /**
     * self::NONE = nic
     * self::ERROR = jen chyby
     * self::INFO = vse bez debugu
     * self::DEBUG = vse
     */
    const LOG_LEVEL = self::DEBUG;

    const
        NONE = 0,
		DEBUG = 1,
		INFO = 2,
		WARNING = 3,
		ERROR = 4;

    const LEVEL_TEXT = array( '-?-', '-d-', '-i-', 'WRN', 'ERR' );

    
    public static function log( $fileName, $level, $msg ) 
    {
        if( self::LOG_LEVEL > $level ) return;

        $fileBase = __DIR__ . '/../../log/' . $fileName ;

        $time = new DateTime();
        $namePart = $time->format('Y-m-d');
        $timePart = $time->format('H:i:s');
        $file = "{$fileBase}.{$namePart}.txt";

        if( is_array($msg) ) {
            $out = array();
            foreach ($msg as $k => $v) { 
                $out[] = "$k=$v"; 
            } 
            $msg = '[ ' . implode ( ', ' , $out ) . ']';
        }
        $pid = getmypid();
        $line = "{$timePart} " . self::LEVEL_TEXT[$level] . " [{$pid}] {$msg}";

        if (!@file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX)) { // @ is escalated to exception
			throw new \RuntimeException("Unable to write to log file '$file'. Is directory writable?");
		}
    }

    private $fileName;
    private $context;

    public function __construct( $fileName, $context=NULL )
    {
        $this->fileName = $fileName;
        if( $context==NULL ) {
            $this->context = getmypid();
        } else {
            $this->context = getmypid() . ';' . $context;
        }
    }

    public function setContext( $context )
    {
        $this->context = getmypid() . ';' . $context;
    }

    public function write( $level, $msg )
    {
        if( self::LOG_LEVEL > $level ) return;

        if( is_array($msg) ) {
            $out = array();
            foreach ($msg as $k => $v) { 
                $out[] = "$k=$v"; 
            } 
            $msg = '[ ' . implode ( ', ' , $out ) . ']';
        }

        if( $this->context!=NULL ) {
            $msg = "[{$this->context}] {$msg}";
        }
        self::log( $this->fileName, $level, $msg );
    }
}