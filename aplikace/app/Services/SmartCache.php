<?php

/**
 * Nadstavba nad caching providerem FileStorage z Nette.
 * Cache v Nette nekontroluje klic a spoleha jen na hash.
 * Pokud by doslo ke kolizi hashu, vrati cizi data.
 */

declare(strict_types=1);

namespace App\Services;

use Nette;
use Nette\Caching\Cache;

class SmartCache 
{
    use Nette\SmartObject;

    /** @var Nette\Caching\Cache */
	private $cache;
    
	public function __construct( \App\Services\Config $config )
	{
        $storage = new Nette\Caching\Storages\FileStorage( $config->getAppDir() . '/temp/cache' );
        $this->cache = new Cache($storage, 'chmi');
	}

    public function get( $key )
    {
        $object = $this->cache->load($key);
        if( $object==NULL || $object['key'] !== $key ) {
            return NULL;
        }
        return $object['value'];
    }

    public function put( $key, $value, $params )
    {
        $object['key'] = $key;
        $object['value'] = $value;
        $this->cache->save( $key, $object, $params );
    }
}