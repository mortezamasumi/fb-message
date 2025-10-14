<?php

namespace Mortezamasumi\FbMessage\Tests\Services;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mortezamasumi\FbMessage\Models\FbMessage as BaseFbMessage;

#[UseFactory(FbMessageFactory::class)]
class FbMessage extends BaseFbMessage
{
    use HasFactory;
}
