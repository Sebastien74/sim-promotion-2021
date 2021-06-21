<?php

if (!(in_array($_SERVER['REMOTE_ADDR'], ['::1', '127.0.0.1', 'fe80::1', '77.158.35.74', '176.135.112.19'], true))
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
}

phpinfo();