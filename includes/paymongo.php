<?php

define('PAYMONGO_SECRET_KEY', ''); 
define('PAYMONGO_PUBLIC_KEY', ''); 

define('PAYMONGO_API_URL', 'https://api.paymongo.com/v1');

define('SITE_URL', 'https://aurorasbymimie.online'); 

if (PAYMONGO_SECRET_KEY === '') {
    error_log('WARNING: PayMongo secret key not configured! Please update includes/paymongo.php');
}
?>