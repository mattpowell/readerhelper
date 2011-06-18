<?php
    require_once("top.php");
/**
 * it's ok to tear up a little bit when looking at this class. I wrote this junk when I was like 12 (so I tear up for a different reason ;). It needs a SERIOUS upgrade instead of just monkey-patching crap. grrr :(
 *
 */
class Access{
	protected $service, $key, $uid, $service_id;
	private $conn,$db;
	 public function __construct($service=NULL, $uid=NULL, $service_id=NULL, $key=NULL) {

      $this->conn=mysql_connect(Config::DB_HOST,Config::DB_USER(),Config::DB_PASSWORD(),true) or die("false");
      mysql_select_db(Config::DB_NAME());
      $this->memcache=new Memcache;
      $this->memcache->connect(Config::MEMCACHEURL(), 11211) or die ("Could not connect");
         /*
          need to populate `$this->memcache->get("access-$uid");` if it's not already set (in memcache) from the database, here.

          also, need to truly do DB fn's in background to reap full benefits of memcache!
          */
	  if (isset($service)) $this->service=$service;
	  if (isset($key)) $this->key=$key;
	  if (isset($uid)) $this->uid=$uid;
	  if (isset($service_id) && $service_id!==true && isset($this->uid) && isset($this->service)) $this->service_id=$service_id;
	  elseif ($service_id=="") $this->service_id="";
	  elseif(!isset($service_id)) {//grab service id

        $this->service_id=mysql_query(sprintf("SELECT service_id FROM services WHERE service='%s' AND `uid`='%s'",
                 mysql_real_escape_string($this->service),
                 mysql_real_escape_string($this->uid)
            )
        );
	  	$this->service_id=current(mysql_fetch_row($this->service_id));
	  }
     //if (isset($service,$uid,$service_id,$key))return $this->get();
   }
	public function get($key=NULL, $service_id=NULL, $service=NULL, $uid=NULL) {
		if (!isset($service_id)) $service_id=$this->service_id;
        if (!isset($service_id)) throw new Exception("[SERVICE_ID] must be defined");
        if (!isset($service)) $service=$this->service;
        if (!isset($service)) throw new Exception("[SERVICE] must be defined");
        if (!isset($uid)) $uid=$this->uid;
        if (!isset($uid)) throw new Exception("[uid] must be defined");
        if (!isset($key)) $key=$this->key;
        if (!isset($key)) throw new Exception("[key] must be defined");
        /* service_id isn't required here	*/

		/*$sql="SELECT value FROM access WHERE service='$service' AND `key`='$key' AND uid='$uid'".($service_id?" AND service_id='".$service_id."'":"")."";
		$result=mysql_query($sql,$this->conn) or die("MYSQL FAILURE [GET].".mysql_error());
		if ($result) {
			$row = mysql_fetch_row($result);
			return $row[0];
		}else return NULL;*/

        $storedMem=$this->memcache->get("access-$uid");
        return $storedMem[$service_id][$key];
	}
	public function put($val=NULL, $key=NULL, $service_id=NULL, $service=NULL, $uid=NULL) {
		if (!isset($service_id)) $service_id=$this->service_id;
		if (!isset($service_id)) throw new Exception("[SERVICE_ID] must be defined");
		if (!isset($service)) $service=$this->service;
		if (!isset($service)) throw new Exception("[SERVICE] must be defined");
		if (!isset($uid)) $uid=$this->uid;
		if (!isset($uid)) throw new Exception("[uid] must be defined");
		if (!isset($key)) $key=$this->key;
		if (!isset($key)) throw new Exception("[key] must be defined");
		if (!isset($val)) throw new Exception("[val] must be defined");


        $storedMem=$this->memcache->get("access-$uid");
        if (!$storedMem)$storedMem=array();
        if (isset($service_id)&&!$storedMem[$service_id])$storedMem[$service_id]=array();
        if (isset($service_id,$key,$val))$storedMem[$service_id][$key]=$val;
        $this->memcache->set("access-$uid",$storedMem);

		//this could proally be upgraded :/
        $count_=mysql_query(sprintf("SELECT count(*) FROM access WHERE service='%s' AND `key`='%s' AND uid='%s' AND service_id='%s'",
                 mysql_real_escape_string($service),
                 mysql_real_escape_string($key),
                 mysql_real_escape_string($uid),
                 mysql_real_escape_string($service_id)
            ),$this->conn
        );
		if (current(mysql_fetch_row($count_))>0) {
            $result=mysql_query(sprintf("UPDATE access SET value='%s' WHERE service='%s' AND `key`='%s' AND uid='%s' AND service_id='%s'",
                     mysql_real_escape_string($val),
                     mysql_real_escape_string($service),
                     mysql_real_escape_string($key),
                     mysql_real_escape_string($uid),
                     mysql_real_escape_string($service_id)
                ),$this->conn
            ) or die("MYSQL FAILURE [PUT].".mysql_error());
		}else {
            $result=mysql_query(sprintf("INSERT INTO access (service,`key`,value,uid,service_id) VALUES('%s','%s','%s','%s','%s')",
                    mysql_real_escape_string($service),
                    mysql_real_escape_string($key),
                    mysql_real_escape_string($val),
                    mysql_real_escape_string($uid),
                    mysql_real_escape_string($service_id)
                ),$this->conn
            ) or die("MYSQL FAILURE [PUT].".mysql_error());
		}
		return $result;

	}
	public function delete($key=NULL, $service=NULL, $service_id=NULL, $uid=NULL) {
		if (!isset($service_id)) $service_id=$this->service_id;
		if (!isset($service_id)) throw new Exception("[SERVICE_ID] must be defined");
		if (!isset($service)) $service=$this->service;
		if (!isset($service)) throw new Exception("[SERVICE] must be defined");
		if (!isset($uid)) $uid=$this->uid;
		if (!isset($uid)) throw new Exception("[uid] must be defined");
		if (!isset($key)) $key=$this->key;
		if (!isset($key)) throw new Exception("[key] must be defined");


        $storedMem=$this->memcache->get("access-$uid");
        if (!$storedMem)$storedMem=array();
        if (isset($service_id)&&!$storedMem[$service_id])$storedMem[$service_id]=array();
        if (isset($service_id,$key))unset($storedMem[$service_id][$key]);
        $this->memcache->set("access-$uid",$storedMem);

        $result=mysql_query(sprintf("DELETE FROM access WHERE service='%s' AND `key`='%s' AND uid='%s'",
                mysql_real_escape_string($service),
                mysql_real_escape_string($key),
                mysql_real_escape_string($uid)
            ),$this->conn
        ) or die("MYSQL FAILURE.".mysql_error());
		return $result;
	}
	public function set($what,$val) {
		switch($what) {
			case "service": $this->service=$val; break;
			case "key": $this->key=$val; break;
			case "uid": $this->uid=$val; break;
			case "service_id": $this->service_id=$val; break;
		}
	}
	public function grab($what) {
		switch($what) {
			case "service": return $this->service;
			case "key": return $this->key;
			case "uid": return $this->uid;
			case "service_id": return $this->service_id;
		}
	}
	public function __destruct() {
      mysql_close($this->conn);
      $this->memcache->close();
    }
}
?>