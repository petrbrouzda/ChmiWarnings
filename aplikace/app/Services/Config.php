<?php

declare(strict_types=1);

namespace App\Services;

use Nette;

class Config
{
    use Nette\SmartObject;

    /**
     * Minimální délka staženého souboru
     */
    public $expectedFileSize = 700000;

    /**
     * URL sluzby CHMI
     */
    public $url = 'https://www.chmi.cz/files/portal/docs/meteo/om/bulletiny/XOCZ50_OKPR.xml';

    /**
     * Jak dlouho plati stazeny soubor, sekundy
     */
    public $fileValiditySec = 3600;

    /**
     * Root adresar aplikace
     */
    public function getAppDir()
    {
        return substr( __DIR__, 0, strlen(__DIR__)-strlen('/app/Services/')+1 );
    }
}




