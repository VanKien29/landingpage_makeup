<?php

return [
    'proxies' => array_values(array_filter(array_map(trim(...), explode(',', env('APP_TRUSTED_PROXIES', ''))))),
];
