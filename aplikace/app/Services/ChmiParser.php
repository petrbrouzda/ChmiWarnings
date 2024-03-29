<?php

/**
 * Projde stazene XML a sestavi z nej JSON data pro zadane parametry.
 */

declare(strict_types=1);

namespace App\Services;

use Nette;
use Nette\Utils\DateTime;

use \SimpleXMLElement;

use \App\Services\Logger;

class ChmiParser 
{
    use Nette\SmartObject;

	public function __construct(  )
	{
	}

    private $odhackuj = false;
    private $kratke = 0;

    /**
     * Odstrani hacky a carky, pokud je to pozadovane.
     */
    private function textCnv( $text ) 
    {
        return !$this->odhackuj ? $text : iconv("utf-8", "us-ascii//TRANSLIT", $text );
    }

    private $dny = [ ' ', 'po', 'út', 'st', 'čt', 'pá', 'so', 'ne' ];

    private function hezkeDatum( $date )
    {
        $today = new Nette\Utils\DateTime();
        $dateT = $date->format('Y-m-d');

        if( strcmp( $today->format('Y-m-d') , $dateT)==0 ) {
            return "dnes " . $date->format('H:i');
        }

        if( strcmp( $today->modifyClone('+1 day')->format('Y-m-d') , $dateT)==0 ) {
            return "zítra " . $date->format('H:i');
        }

        return $this->dny[$date->format('N')] . ' ' . $date->format( 'j.n. H:i' );
    }

    /*
    <info>
        <language>cs</language>
        <category>Met</category>
        <event>SilnĂŠ bouĹ.ky</event>
        <responseType>Shelter</responseType>
        <responseType>Execute</responseType>
        <urgency>Future</urgency>
        <severity>Moderate</severity>
        <certainty>Likely</certainty>
        <audience>veĹ.ejnost, HZS, web, Meteoalarm</audience>
        <eventCode>
            <valueName>SIVS</valueName>
            <value>X.1</value>
        </eventCode>
        <onset>2021-05-12T13:00:00+02:00</onset>
        <expires>2021-05-12T22:00:00+02:00</expires>
        <senderName>Ä.HMĂ., Racko</senderName>
        <description>OÄ.ekĂĄvĂĄ se ojedinÄ.lĂ˝ vĂ˝skyt silnĂ˝ch bouĹ.ek s Ăşhrny ojedinÄ.le nad 40 mm a kroupami.</description>
        <instruction>LokĂĄlnÄ. se oÄ.ekĂĄvĂĄ pĹ.Ă.valovĂ˝ dĂŠĹĄĹĽ s ojedinÄ.lĂ˝m rozvodnÄ.nĂ.m malĂ˝ch tokĹŻ, zatopenĂ. podchodĹŻ, podjez...
        <web>http://www.chmi.cz/files/portal/docs/meteo/om/zpravy/index.html</web>
        <parameter>
            <valueName>situation</valueName>
            <value>ZvlnÄ.nĂĄ studenĂĄ fronta postupuje pĹ.es NÄ.mecko zvolna k vĂ˝chodu. PĹ.ed nĂ. k nĂĄm proudĂ. velmi teplĂ˝ vzduch od ji
        </parameter>
        <parameter>
            <valueName>eventEndingTime</valueName>
            <value>2021-05-12T22:00:00+02:00</value>
        </parameter>
        <parameter>
            <valueName>awareness_level</valueName>
            <value>2; yellow; Moderate</value>
        </parameter>
        <parameter>
            <valueName>awareness_type</valueName>
            <value>3; Thunderstorm</value>
        </parameter>
    </info>

ale taky

<info>
<language>cs</language>
<category>Met</category>
<event>Výhled nebezpečných jevů</event>
<urgency>Future</urgency>
<severity>Unknown</severity>
<certainty>Unknown</certainty>
<audience>veřejnost, HZS, web</audience>
<eventCode>
<valueName>SIVS</valueName>
<value>OUTLOOK</value>
</eventCode>
<onset>2021-06-18T12:00:00+02:00</onset>
<expires>2021-06-22T00:00:00+02:00</expires>
<senderName>ČHMÚ, Racko</senderName>
<description>Od pátku 18.6. do pondělí 21.6. budou překračovat nejvyšší teploty vzduchu 31 °C zpočátku v Čechách, postupně i na ostatním území. Místy mohou překročit maximální teploty až 34 °C. Současně bude růst i nebezpečí vzniku a šíření požárů.</description>
<instruction>Toto je předběžné varování, doporučujeme sledovat upřesňované předpovědi počasí.</instruction>
<web>http://www.chmi.cz/files/portal/docs/meteo/om/zpravy/index.html</web>
<parameter>
<valueName>situation</valueName>
<value>Po okraji tlakové výše se středem nad Pobaltím k nám bude proudit velmi teplý vzduch od jihu.</value>
</parameter>
<parameter>
<valueName>eventEndingTime</valueName>
<value>2021-06-22T00:00:00+02:00</value>
</parameter>
<parameter>
<valueName>awareness_level</valueName>
<value>2; yellow; Moderate</value>
</parameter>
<parameter>
<valueName>awareness_type</valueName>
<value>11; unknown</value>
</parameter>
<area>
<areaDesc>Hlavní město Praha</areaDesc>
<geocode>
<valueName>CISORP</valueName>
<value>1100</value>
</geocode>
</area>


    */

    /**
     * Transformace dat z XML struktury vyse na jednoduchy JSON objekt
     */
    private function parseInfo( $info )
    {
        $event = array();
        $event['text'] = $this->textCnv( "{$info->event}" );
        
        if( $this->kratke<2 ) {
            $event['detailed_text'] = $this->textCnv(  "{$info->description}" );
        }
        if( $this->kratke==0 ) {
            $event['instruction'] = $this->textCnv( "{$info->instruction}" );
        }
        $event['time_start_i'] = "{$info->onset}";
        if( isset($info->expires) ) {
            $event['time_end_i'] = "{$info->expires}";
        }

        if( $this->kratke==0 ) {
            $category = array();
            $category['response'] = "{$info->responseType}";
            $category['urgency'] = "{$info->urgency}";
            $category['severity'] = "{$info->severity}";
            $category['certainty'] = "{$info->certainty}";
            $event['category'] = $category;
        }

        foreach( $info->parameter as $parameter ) {
            if( $parameter->valueName == 'eventEndingTime' ) {
                $event['time_end_i'] = "{$parameter->value}";
            }
            if( $parameter->valueName == 'awareness_type' ) {
                $vals = explode( ';', "{$parameter->value}" );
                $event['type'] = isset($vals[1]) ? trim($vals[1]) : 'Unknown';
            }
            if( $parameter->valueName == 'awareness_level' ) {
                $vals = explode( ';', "{$parameter->value}" );
                $event['color'] = isset($vals[1]) ? trim($vals[1]) : 'Unknown';
            }
        }

        $start = DateTime::from($event['time_start_i']);
        $event['time_start_e'] = $start->getTimestamp();
        $event['time_start_t'] = $this->textCnv( $this->hezkeDatum( $start ) );

        if( isset($event['time_end_i']) ) {
            $end = DateTime::from($event['time_end_i']);
            $event['time_end_e'] = $end->getTimestamp();
            $event['time_end_t'] = $this->textCnv( $this->hezkeDatum( $end ) );
        }

        if( $event['time_start_e'] <= time() && (!isset($event['time_end_e']) || $event['time_end_e'] >= time()) ) {
            $event['in_progress'] = 'Y';
        } else {
            $event['in_progress'] = 'N';
        }

        return $event;
    }

    private $logOnlyOnce = true;

    /**
     * Zkontroluje, zda se vystraha tyka zadaneho mista
     */
    private function overPozici( $info, $id )
    {
        foreach( $info->area as $area ) {
            foreach( $area->geocode as $geocode ) {
                if( $geocode->valueName=='CISORP' && $geocode->value==$id ) {
                    return true;
                }
                // kdyby se zacaly objevovat nezname kody, zapiseme do logu
                if( $this->logOnlyOnce && $geocode->valueName!='CISORP' ) {
                    $this->logOnlyOnce = false;
                    Logger::log( 'app', Logger::ERROR ,  "Neznamy geocode! name='{$geocode->valueName}' val='{$geocode->value}'" );             
                }
            }
        }
        return false;
    }

    public function parse( $file, $id, $odhackuj, $kratke )
    {
        Logger::log( 'app', Logger::DEBUG ,  "parse {$file}, {$id}, odhackuj:" . ($odhackuj ? 'Y' : 'N') . ", kratke:{$kratke}" ); 
        
        if( $id==NULL || !is_numeric($id) || $id<1000 || $id>9999 ) {
            throw new \Exception( "ID '{$id}' neni validni, musi to byt CISORP z http://apl.czso.cz/iSMS/cisdet.jsp?kodcis=65");
        }

        $this->kratke = $kratke;

        if( $odhackuj ) {
            $this->odhackuj = true; 
            // aby fungoval iconv
            setlocale(LC_ALL, 'czech'); // záleží na použitém systému
        } 

        $rc = array();
        $rc['current_time'] = time();

        $events = array();

        $xmlstr = file_get_contents ( $file );
        $xml = new SimpleXMLElement($xmlstr);
        foreach( $xml->info as $info ) {
            if( $info->language == 'cs' && !( $info->responseType=='None' || $info->responseType=='AllClear' || "{$info->responseType}"==='') ) {
                Logger::log( 'app', Logger::DEBUG ,  "  {$info->language} '{$info->event}' {$info->responseType} {$info->urgency} {$info->severity} {$info->certainty}" );
                if( ! $this->overPozici( $info, $id ) ) {
                    Logger::log( 'app', Logger::DEBUG ,  "    mimo moji pozici" );
                } else {
                    // udalosti, ktere skoncily drive nez pred ctyrmi hodinami, ignorujeme
                    // (to muze nastat, nyni 28.12. je v souboru varovani s platnosti do 21.12.)
                    $event = $this->parseInfo( $info );
                    if( $event['time_end_e']!=0 && ($event['time_end_e'] < (time()-(4*3600))) ) {
                        Logger::log( 'app', Logger::DEBUG ,  "    uz skoncilo ({$event['time_end_e']}  {$event['time_end_i']})" );
                    } else {
                        Logger::log( 'app', Logger::DEBUG ,  "    +" );
                        $events[] = $event;
                    }
                }
            }
        }

        $rc['events'] = $events;
        return $rc;
    }

}