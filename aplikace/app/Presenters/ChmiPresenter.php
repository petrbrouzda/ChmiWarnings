<?php

/**
 * Prezenter.
 * - Pokud zaznam pro dane parametry nenajde v kesi:
 *      stahne aktualni soubor,
 *      zparsuje ho,
 *      vysledek ulozi do kese.
 * - Pokud najde, pouzije nakesovany.
 */

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Utils\Json;
use \App\Services\Logger;

use \App\Services\SmartCache;
// je potreba kvuli konstantam
use Nette\Caching\Cache;

final class ChmiPresenter extends Nette\Application\UI\Presenter
{
    use Nette\SmartObject;
    
    /** @var \App\Services\Downloader */
    private $downloader;

    /** @var \App\Services\ChmiParser */
    private $parser;

    /** @var \App\Services\SmartCache */
    private $cache;

    public function __construct(\App\Services\Downloader $downloader, \App\Services\ChmiParser $parser, \App\Services\SmartCache $cache  )
    {
        $this->downloader = $downloader;
        $this->parser = $parser;
        $this->cache = $cache;
    }


    /**
     * odhackuj=1 -> odstrani diakritiku
     * kratke=0 ... cely text, 
     *      =1 ... bez 'instruction' a bez sekce 'category'
     *      =2 ... bez 'instruction', 'detailed_text' a bez sekce 'category'
     */
    public function renderVystrahy( $id, $odhackuj=false, $kratke=0 )
    {
        try {
            $result = NULL;
            $key = "{$id}.{$kratke}." . ($odhackuj?'Y':'N');
            Logger::log( 'app', Logger::DEBUG , $key );

            // tohle zavolame vzdy; zajisti nacteni souboru, pokud je potreba
            $fileName = $this->downloader->getFile();

            $result = $this->cache->get( $key );
            if( $result==NULL ) {
                $result = $this->parser->parse( $fileName, $id, $odhackuj, $kratke );

                $this->cache->put($key, $result, [
                    Cache::EXPIRE => '60 minutes',
                    Cache::FILES => $this->downloader->getDataFileName(),
                ]);
            } else {
                Logger::log( 'app', Logger::DEBUG, 'cache hit' );    
            }

            $response = $this->getHttpResponse();
            $response->setHeader('Cache-Control', 'no-cache');
            $response->setExpiration('1 sec'); 

            Logger::log( 'app', Logger::DEBUG ,  "OK" );

            $this->sendJson($result);

        } catch (\Nette\Application\AbortException $e ) {
            // normalni scenar pro sendJson()
            throw $e;

        } catch (\Exception $e) {
            Logger::log( 'app', Logger::ERROR,  "ERR: " . get_class($e) . ": " . $e->getMessage() );
            
            $httpResponse = $this->getHttpResponse();
            $httpResponse->setCode(Nette\Http\Response::S500_INTERNAL_SERVER_ERROR );
            $httpResponse->setContentType('text/plain', 'UTF-8');
            $response = new \Nette\Application\Responses\TextResponse("ERR {$e->getMessage()}");
            $this->sendResponse($response);
            $this->terminate();
        }
    }

}