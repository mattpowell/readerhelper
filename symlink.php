<?php

//for now, lets just pull the url in question on demand and spit out the contents here....

$conn=mysql_connect(Config::DB_HOST,Config::DB_USER,Config::DB_PASSWORD) or die("false");
mysql_select_db(Config::DB_NAME);
$username=$_GET["username"];
$auth=$_GET["auth"];
$uid=mysql_query(sprintf("SELECT uid FROM notes_auth_table WHERE username='%s' AND auth_key='%s'",
             mysql_real_escape_string($username),
             mysql_real_escape_string($auth)
        )
    );
if ($uid)$uid=mysql_result($uid,0);

if ($uid){

    require_once("MOauth.php");
    $access=new Access("google",$uid,Config::GOOGLE_SERVICE_ID);
    $oauthToken=$access->get("oauth_access_token");
    $oauthSecret=$access->get("oauth_access_token_secret");

    $to = new MOAuth(Config::GOOGLE_CONSUMER_KEY, Config::GOOGLE_CONSUMER_SECRET, $oauthToken, $oauthSecret);
    $to->setAPIURL("http://www.google.com");
    echo $to->OAuthRequest("http://www.google.com/reader/atom/user/-/state/com.google/created", array(), 'GET');


}
?>