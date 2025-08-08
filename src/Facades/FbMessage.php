<?php

namespace Mortezamasumi\FbMessage\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void markAsRead(Model $record)
 * @method statis void archive(Model $record)
 * @method statis void unarchive(Model $record)
 * @method statis void trash(Model $record)
 * @method statis void restore(Model $record)
 * @method statis void forget(Model $record)
 *
 * @see \Mortezamasumi\FbMessage\FbMessage
 */
class FbMessage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mortezamasumi\FbMessage\FbMessage::class;
    }
}
