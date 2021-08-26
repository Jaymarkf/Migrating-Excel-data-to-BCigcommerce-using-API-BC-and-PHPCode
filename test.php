   <?php
   $storehash = 't0676dlrio';

$v = "test";



   $headers = array(
      //   'X-Auth-Client: iyhwjdyol0uamlpheeb1juma64pfkdj',
      //   'X-Auth-Token: em16zkoebf6o9ioq57h4kic88vprfr',
        'Content-Type: application/json'
        );
        $url = 'https://api.bigcommerce.com/stores/' .$storehash. '/v3/catalog/products/114/modifiers/586';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_PUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json', 'Accept: application/json', 'Content-Length: 0','X-Auth-Client: iyhwjdyol0uamlpheeb1juma64pfkdj','X-Auth-Token: em16zkoebf6o9ioq57h4kic88vprfr'));
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($v,JSON_UNESCAPED_SLASHES));
      //   curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        var_dump($response);