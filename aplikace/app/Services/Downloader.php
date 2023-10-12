<?php

/**
 * Zajisti stazeni souboru ze serveru a ulozeni do pracovniho adresare.
 * Pokud se stazeni nepodari a v adresari je stara verze, vrati alespon ji.
 */

declare(strict_types=1);

namespace App\Services;

use Nette;
use Nette\Utils\FileSystem;

use \App\Services\Logger;

class Downloader 
{
    use Nette\SmartObject;

    /** @var \App\Services\Config */
    private $config;
    
	public function __construct( \App\Services\Config $config )
	{
        $this->config = $config;
	}

    /**
     * Datový soubor 
     */
    public function getDataFileName()
    {
        return $this->config->getAppDir() . '/data/data.xml';
    }

    /**
     * Temp soubor
     */
    public function getTempFileName()
    {
        return $this->config->getAppDir() . '/data/download-temp-' . getmypid() . '.tmp';
    }

    /**
     * Stahne soubor do tmp souboru a pokud se uspesne stahne cely, prejmenuje ho na cilove jmeno.
     */
    private function download( $file )
    {
        Logger::log( 'app', Logger::DEBUG ,  "stahuji" ); 

        $tmpName = $this->getTempFileName();
        if( $this->config->expectedFileSize > @file_put_contents( $tmpName, fopen($this->config->url, 'r')) ) {
            throw new \Exception( 'Nemohu stahnout soubor, je moc maly' );
        }
        FileSystem::rename( $tmpName, $file );
    }

    /**
     * Je potreba stahnout novy soubor?
     */
    private function shouldDownload( $file )
    {
        if( ! file_exists($file) ) return true;
        $filetime = filemtime($file);
        if( $filetime + $this->config->fileValiditySec < time() ) return true;
        return false;
    }

    /**
     * Vraci jmeno souboru s daty
     */
    public function getFile()
    {
        $file = $this->getDataFileName();
        if( $this->shouldDownload($file) ) {
            try {
                $this->download( $file );
            } catch( \Exception $ee ) {
                Logger::log( 'app', Logger::ERROR,  "Chyba stahovani: " . get_class($ee) . ": " . $ee->getMessage() );
                if( ! file_exists($file) ) {
                    // pokud mame soubor z minula, pojedeme na predesle verzi
                    // pokud ho ale nemame -> chyba
                    throw $ee;
                }
            }
        }
        return $file;
    }
}