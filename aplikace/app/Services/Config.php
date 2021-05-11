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
    public $expectedFileSize = 850000;

    /**
     * URL sluzby CHMI
     */
    public $url = 'https://www.chmi.cz/files/portal/docs/meteo/om/bulletiny/XOCZ50_OKPR.xml';

    /**
     * Jak dlouho plati stazeny soubor, sekundy
     */
    public $fileValiditySec = 3600;

}




