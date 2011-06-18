<?php
echo __DIR__."/includes";
   ini_set("include_path",__DIR__."/includes");
require_once("top.php");
    // all we're doing is providing a symlink to http://www.google.com/reader/atom/user/-/state/com.google/created
    // that can be accessed via: http://readerhelper.com/[USERNAME]/[RANDOM PRIVATE ID]/
    //...so far

    $uid=(isset($_SESSION["uid"])?$_SESSION["uid"]:$_GET['uid']);
    $product=$_GET["product"];
    $action=$_GET["action"];

    if (isset($uid)){
        $conn=mysql_connect(Config::DB_HOST,Config::DB_USER(),Config::DB_PASSWORD()) or die("Error. Try again later.");
        mysql_select_db(Config::DB_NAME());
        $access=new Access("google",$uid,Config::GOOGLE_SERVICE_ID());
        $name=$access->get("name");
        if (empty($name))$name="there";

        $usernameForNotes=$access->get("username");
        $privateKeyForNotes=$access->get("notes-private-key");
        if (!isset($privateKeyForNotes)||($action=="reset"&&$product=="notes")){
            $sql=!isset($privateKeyForNotes)?"INSERT INTO notes_auth_table VALUES('%s','%s','%s')":"UPDATE notes_auth_table SET auth_key='%s' WHERE username='%s' AND uid='%s'";
            $privateKeyForNotes=uniqid();
            $createPrivateKey=mysql_query(sprintf($sql,
                     mysql_real_escape_string($usernameForNotes),
                     mysql_real_escape_string($privateKeyForNotes),
                     mysql_real_escape_string($uid)
                 )
            );
            if ($createPrivateKey)$access->put($privateKeyForNotes,"notes-private-key");
            else die("Error. Try again later.");
        }

    }

?><!doctype html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>ReaderHelper.com: Helping Google Reader be all it can be</title>
  <meta name="description" content="Various utilities to add to the Google Readers featureset">
  <meta name="author" content="Matt Powell">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="/css/global.css">

</head>
<body>

  <div id="container">
    <header>
        <h1>ReaderHelper.com</h1>
    </header>
    <div id="main" role="main">
        <?php if (isset($name,$usernameForNotes,$privateKeyForNotes)){?>

            <strong>Hello <?=$name?>,</strong>
            <dl id="listOfOptions">
                <dt>Private Notes URL: <a href="/<?=$usernameForNotes?>/<?=$privateKeyForNotes?>">http://readerhelper.com/<?=$usernameForNotes?>/<?=$privateKeyForNotes?></a>. (<a href="/notes/reset">Reset private key</a>)</dt>
                <dd>Provides a url that you can follow in Google Reader of all the Notes kept in Google Reader</dd>
                <dt class="comingsoon">Note in Reader bookmarklet (coming soon).</dt>
                <dd>Provides a few minor updates to the <a href="http://www.google.com/reader/view/user/-/state/com.google/created" title="Link to Google's bookmarklet. Look on the right-hand side.">bookmarklet script</a>; automatically un-check "Add to shared items", and inlines style information of selected text.</dd>
            </dl>

        <?php }else {?>

            First you need to authorize <a href="/authorize/google">Google Reader</a> with us (or just <a href="/authorize/google">sign in</a> again).

        <?php }?>
    </div>
    <footer>

    </footer>
  </div>

  <script src="//ajax.googleapis.com/ajax/libs/mootools/1.3.1/mootools-yui-compressed.js"></script>
  <script>
      window.MooTools || document.write('<script src="/js/mootools-1.3.1-compressed.js">\x3C/script>');
      (function(de){
          de&&(de.className=de.className.replace(/no\-js\s?/,""))
      })(document.documentElement)
  </script>

  <script src="/js/global.js"></script>

  <script>
    var _gaq=[['_setAccount','<?=Config::GOOGLE_ANALYTICS_UID()?>'],['_trackPageview']];
    (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
    g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
    s.parentNode.insertBefore(g,s)}(document,'script'));
  </script>

</body>
</html>