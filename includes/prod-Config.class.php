<?php

class Config{
    const env="PROD";
    const dev=false;
    const qa=false;
    const prod=true;

    const URL="readerhelper.com";
    const DB_HOST="localhost:/tmp/mysql/helena.sock";
    const DB_USER="L/pSXy+zrJVcjjW9u/mC7IPI0jfs6CKjfseEJqvKySc=";
    const DB_NAME="eaK48FQj7VJiHqQEoKMrFCw5gr8/E4vJ2C28pav0Ij8=";
    const DB_PASSWORD="FYelO0B969YkQPpX8zs881YDmtAIJRDsYWYmSNt+izQ=";

    const MEMCACHEURL="T2XEYHZy2fNUD7Mx7sxZY2El3iT8lHq1Kf8zf5McwnI=";
    const GOOGLE_ANALYTICS_UID="tp6r0E7NMNUEtV8VnIZi3UquYD8pH3CZdUbuLzlik5E=";
    const GOOGLE_SERVICE_ID="Hqcp35nJAEPsjzAMveNb+qSoM09KcU9NzzBnny5JUa8=";
    const GOOGLE_CONSUMER_KEY="rwnWTfujit3pkkCF0co4eOqIjKMVFPOekDRx5oU5Ulo=";
    const GOOGLE_CONSUMER_SECRET="JC58QK/jDY08yV4M8cznVGgfcF5g+kTdjLkBOEMUNC4=";
    const GOOGLE_APIS_KEY="bMIz/LoNZoCMGRwMaffqUpyfk2fWjxrNNDAfeyIBKlqhGNA7UdBTnFA6Nl1ppRY++utWw5QNHaOcJwl9/Q+rcw==";
    

    public static function __callStatic($name,$args){
        return decrypt(constant("Config::$name"));//call_user_func("static::$name",$args);
    }
    public function __get($name){
        return call_user_func("static::$name");
    }
}

?>