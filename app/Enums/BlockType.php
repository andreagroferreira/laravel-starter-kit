<?php

declare(strict_types=1);

namespace App\Enums;

enum BlockType: string
{
    case Hero = 'hero';
    case RichText = 'rich_text';
    case Image = 'image';
    case Cta = 'cta';
    case Features = 'features';
    case Testimonials = 'testimonials';
    case Pricing = 'pricing';
    case Faq = 'faq';
    case Form = 'form';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
