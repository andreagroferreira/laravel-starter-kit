<?php

declare(strict_types=1);

namespace App\Enums;

enum IntegrationProvider: string
{
    case GoogleAnalytics = 'google_analytics';
    case SearchConsole = 'search_console';
    case Meta = 'meta';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::GoogleAnalytics => 'Google Analytics 4',
            self::SearchConsole => 'Search Console',
            self::Meta => 'Meta (Facebook/Instagram)',
        };
    }

    /**
     * OAuth driver behind the provider — Google backs two integrations.
     */
    public function driver(): string
    {
        return match ($this) {
            self::GoogleAnalytics, self::SearchConsole => 'google',
            self::Meta => 'facebook',
        };
    }

    /**
     * @return list<string>
     */
    public function scopes(): array
    {
        return match ($this) {
            self::GoogleAnalytics, self::SearchConsole => [
                'https://www.googleapis.com/auth/analytics.readonly',
                'https://www.googleapis.com/auth/webmasters.readonly',
            ],
            self::Meta => [
                'pages_manage_posts',
                'pages_read_engagement',
                'instagram_content_publish',
            ],
        };
    }
}
