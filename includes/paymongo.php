<?php

define('PAYMONGO_SECRET_KEY', 'sk_test_EEzQb72uwow28BeTq7qRVuLC'); 
define('PAYMONGO_PUBLIC_KEY', 'pk_test_m1H6Xk6hU9evwkRb1DbB6HKi'); 

define('PAYMONGO_API_URL', 'https://api.paymongo.com/v1');

define('SITE_URL', 'https://aurorasbymimie.online'); 

if (PAYMONGO_SECRET_KEY === 'sk_test_EEzQb72uwow28BeTq7qRVuLC') {
    error_log('WARNING: PayMongo secret key not configured! Please update includes/paymongo.php');
}
?>