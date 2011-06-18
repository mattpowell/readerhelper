<?php

$action=$_GET["action"];


if ($action=="authorize"){


    require_once('OAuth.php');

    if (isset($_SESSION['uid'])){
        $access = new Access("google", $_SESSION['uid'], Config::GOOGLE_SERVICE_ID);
        $oauth_access_token = $access->get("oauth_access_token");
        $oauth_access_token_secret = $access->get("oauth_access_token_secret");
    }

    $CONSUMER_KEY = Config::GOOGLE_CONSUMER_KEY;
    $CONSUMER_SECRET = Config::GOOGLE_CONSUMER_SECRET;

    $consumer = new OAuthConsumer($CONSUMER_KEY, $CONSUMER_SECRET);
    $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();


    //$scopes = array('http://www.google.com/reader/api/'/*,'http://www-opensocial.googleusercontent.com/api/people/'*/);
    $scopes = array('http://www.google.com/reader/atom/','http://www.google.com/reader/api/');

    $openid_params = array(
        'openid.ns' => 'http://specs.openid.net/auth/2.0',
        'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
        'openid.return_to' => "http://{$CONSUMER_KEY}/authorize/google",//?{$_SERVER["QUERY_STRING"]}",
        'openid.realm' => "http://{$CONSUMER_KEY}",
        'openid.mode' => "checkid_setup", //@$_REQUEST['openid_mode'],
        'openid.ns.ui' => 'http://specs.openid.net/extensions/ui/1.0',
        'openid.ns.ext1' => 'http://openid.net/srv/ax/1.0',
        'openid.ext1.mode' => 'fetch_request',
        'openid.ext1.type.email' => 'http://axschema.org/contact/email',
        'openid.ext1.type.first' => 'http://axschema.org/namePerson/first',
        'openid.ext1.type.last' => 'http://axschema.org/namePerson/last',
        'openid.ext1.required' => 'email,first,last',
        'openid.ns.oauth' => 'http://specs.openid.net/extensions/oauth/1.0',
        'openid.oauth.consumer' => $CONSUMER_KEY,
        'openid.oauth.scope' => implode(' ', $scopes)
    );

    $openid_ext = array(
        'openid.ns.ext1' => 'http://openid.net/srv/ax/1.0',
        'openid.ext1.mode' => 'fetch_request',
        'openid.ext1.type.email' => 'http://axschema.org/contact/email',
        'openid.ext1.type.first' => 'http://axschema.org/namePerson/first',
        'openid.ext1.type.last' => 'http://axschema.org/namePerson/last',
        'openid.ext1.required' => 'email,first,last',
        'openid.ns.oauth' => 'http://specs.openid.net/extensions/oauth/1.0',
        'openid.oauth.consumer' => $CONSUMER_KEY,
        'openid.oauth.scope' => implode(' ', $scopes),
        'openid.ui.icon' => 'true'
    );
    $uri = '';
    foreach ($openid_params as $key => $param) {
        $uri .= $key . '=' . urlencode($param) . '&';
    }


    
    if (isset($oauth_access_token, $oauth_access_token_secret)) {
        $access_token = new OAuthToken($oauth_access_token, $oauth_access_token_secret);

        $feedUri = 'http://www.google.com/reader/api/0/user-info';
        $req = OAuthRequest::from_consumer_and_token($consumer, $access_token, 'GET', $feedUri, NULL);
        $req->sign_request($hmac_method, $consumer, $access_token);

        if (success(send_signed_request($req->get_normalized_http_method(), $feedUri, $req->to_header(), NULL, false))) {
            header("Location: /");
        }

    }else if ($_REQUEST["openid_mode"] == "id_res") {


        
        $userId = str_replace("https://www.google.com/accounts/o8/id?id=","",$_REQUEST["openid_identity"]);
        $_SESSION['uid']=$uid=$userId;

        $access = new Access("google", $uid, Config::GOOGLE_SERVICE_ID);
        $access->put($userId,"uid");



        $request_token = @$_REQUEST['openid_ext2_request_token'];
        if ($request_token) {
            $access_token = getAccessToken($request_token);
            if ($access_token){
                $access->put($access_token[0], "oauth_access_token");
                $access->put($access_token[1], "oauth_access_token_secret");

                $access_token = new OAuthToken($access_token[0], $access_token[1]);

                $feedUri = 'http://www.google.com/reader/api/0/user-info';
                $req = OAuthRequest::from_consumer_and_token($consumer, $access_token, 'GET', $feedUri, NULL);
                $req->sign_request($hmac_method, $consumer, $access_token);
                if (success(send_signed_request($req->get_normalized_http_method(), $feedUri, $req->to_header(), NULL, false))){
                    
                    header("Location: /?uid=$uid");
                }else echo "Unsuccessful. Try <a href='/authorize/google'>again</a>";
            }else header("Location: https://www.google.com/accounts/o8/ud" . '?' . rtrim($uri, '&'));

        }else header("Location: https://www.google.com/accounts/o8/ud" . '?' . rtrim($uri, '&'));


    }else if (!isset($_SESSION['uid'])) header("Location: https://www.google.com/accounts/o8/ud" . '?' . rtrim($uri, '&'));

}else echo "Action not available";

function success($c){
    $c=json_decode($c);
    if ($c) {
        $GLOBALS['access']->put($c->userId,"userid");
        $GLOBALS['access']->put($c->userName,"name");
        $GLOBALS['access']->put($c->userEmail,"email");
        $GLOBALS['access']->put($c->publicUserName,"username");

        return true;

    }else return false;

}
?>