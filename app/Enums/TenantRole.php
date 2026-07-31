<?php

declare(strict_types=1);

namespace App\Enums;

enum TenantRole: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Marketeer = 'marketeer';
    case Journalist = 'journalist';
    case EditorInChief = 'editor_in_chief';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
