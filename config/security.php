<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hostname yang boleh menjadi tujuan redirect HTTP -> HTTPS
    |--------------------------------------------------------------------------
    |
    | Daftar hostname persis (tanpa skema/port), dipisahkan koma.
    | Kosong = redirect HTTPS nonaktif.
    |
    */

    'https_redirect_hosts' => array_values(array_filter(array_map(
        fn (string $host) => strtolower(trim($host)),
        explode(',', (string) env('HTTPS_REDIRECT_HOSTS', ''))
    ))),

];
