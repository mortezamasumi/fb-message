<?php

namespace Mortezamasumi\FbMessage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mortezamasumi\FbMessage\FbMessage
 */
class FbMessage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mortezamasumi\FbMessage\FbMessage::class;
    }
}
