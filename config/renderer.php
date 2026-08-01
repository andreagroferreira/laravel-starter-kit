<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Public Renderer
    |--------------------------------------------------------------------------
    |
    | The Nuxt renderer resolves the site for each request by hostname:
    | an exact match on sites.domain wins; otherwise, when the host ends
    | with one of the suffixes below, the leading label is used as the
    | site slug (e.g. demo.wizardincode.site -> slug "demo").
    |
    */

    'public_suffix' => env('RENDERER_PUBLIC_SUFFIX', '.wizardincode.site'),

    'local_suffixes' => [
        '.wizcms.test',
    ],
];
