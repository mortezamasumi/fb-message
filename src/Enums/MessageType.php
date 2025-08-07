<?php

namespace Mortezamasumi\FbMessage\Enums;

enum MessageType: string
{
    case FROM = 'from';
    case TO = 'to';
    case CC = 'cc';
    case BCC = 'bcc';
}
