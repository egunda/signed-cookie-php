<?php
function createSignedCookie($streamHostUrl, $resourceKey, $timeout){
    $keyPairId = "APKAIWCOMK3FSRZUS4CQ"; // Key Pair
    $expires = time() + $timeout; // Expire Time
    $url = $streamHostUrl . '/' . $resourceKey; // Service URL
    $ip=$_SERVER["REMOTE_ADDR"] . "\/24"; // IP
    $json = '{"Statement":[{"Resource":"'.$url.'","Condition":{"DateLessThan":{"AWS:EpochTime":'.$expires.'}}}]}';
 
    $fp=fopen("private_key.pem", "r");
    $priv_key=fread($fp, 8192);
    fclose($fp);
 
    $key = openssl_get_privatekey($priv_key);
    if(!$key){
        echo "<p>Failed to load private key!</p>";
        return;
    }
    if(!openssl_sign($json, $signed_policy, $key, OPENSSL_ALGO_SHA1)){
        echo '<p>Failed to sign policy: '.opeenssl_error_string().'</p>';
        return;
    }
 
    $base64_signed_policy = base64_encode($signed_policy);
 
    $policy = strtr(base64_encode($json), '+=/', '-_~'); //Canned Policy
 
    $signature = str_replace(array('+','=','/'), array('-','_','~'), $base64_signed_policy);
 
    //In case you want to use signed URL, just use the below code
    //$signedUrl = $url.'?Expires='.$expires.'&Signature='.$signature.'&Key-Pair-Id='.$keyPairId; //Manual Policy
    $signedCookie = array(

                        "CloudFront-Key-Pair-Id" => $keyPairId,
                        "CloudFront-Policy" => $policy,
                        "CloudFront-Signature" => $signature
                    );
 
    return $signedCookie;
}
?>
 
<html lang="en">
<head>
        <meta charset="utf-8" />
        <title>Signed cookie Test</title>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/clappr@latest/dist/clappr.min.js"></script>
</head>
<body>
  <h2>Cookie Detailes </h2>
</br>
    <?php
    $signedCookieCustomPolicy = createSignedCookie('https://d3mijot0yweq40.cloudfront.net', 'live/bharat_hls/*', 300);
    foreach ($signedCookieCustomPolicy as $name => $value) {
        setcookie($name, $value, 0, "/", "d3mijot0yweq40.cloudfront.net", false, false);
    }
    print_r($signedCookieCustomPolicy);
    ?>
<h2> Testing Signed Cookie On HLS Stream</h2>
  <div id="player"></div>
  <script>
    var player = new Clappr.Player({source: "https://d3mijot0yweq40.cloudfront.net/live/bharat_hls/chunklist_2.m3u8", autoPlay: true, parentId: "#player"});
  </script>
</body>
</html>
