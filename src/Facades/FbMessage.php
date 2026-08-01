<?php

namespace Mortezamasumi\FbMessage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void markAsRead(\Mortezamasumi\FbMessage\Models\FbMessage $record)
 * @method static void archive(\Mortezamasumi\FbMessage\Models\FbMessage $record)
 * @method static void unarchive(\Mortezamasumi\FbMessage\Models\FbMessage $record)
 * @method static void trash(\Mortezamasumi\FbMessage\Models\FbMessage $record)
 * @method static void restore(\Mortezamasumi\FbMessage\Models\FbMessage $record)
 * @method static void forget(\Mortezamasumi\FbMessage\Models\FbMessage $record)
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
