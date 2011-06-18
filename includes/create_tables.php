<?php

    $conn = mysql_connect(Config::DB_HOST, Config::DB_USER(), Config::DB_PASSWORD(), true);
    mysql_select_db(Config::DB_NAME());
echo $conn?"success":"fail";
    if ($conn && 1>2) {
        $sql="
            CREATE TABLE IF NOT EXISTS `notes_auth_table` (
              `username` varchar(255) NOT NULL,
              `auth_key` varchar(255) NOT NULL,
              `uid` varchar(255) NOT NULL,
              PRIMARY KEY (`username`,`auth_key`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

            CREATE TABLE IF NOT EXISTS `access` (
              `service` varchar(255) NOT NULL,
              `service_id` varchar(255) NOT NULL,
              `key` varchar(255) NOT NULL,
              `value` varchar(255) NOT NULL,
              `uid` varchar(255) NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
        ";

        $sql=mysql_query($sql,$conn);

        if ($sql) {
            $_SERVER['tables_exist']=true;
            die("tables created");
        }else die("tables not created.");

    }

?>