<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
CREATE TABLE movie(
     `id` INT(11) PRIMARY KEY,
     `name` VARCHAR(50) NOT NULL,
     `average` FLOAT(3,1) DEFAULT 0.0,
     `vote` INT(11) DEFAULT 0,
     `star` VARCHAR(2) DEFAULT "00",
     `rate` VARCHAR(100) DEFAULT "{}",
     `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */
class DoubanX {
    public function __construct() {
        include_once(BASEPATH."database/DB.php");
        include_once(BASEPATH."config/database.php");
        $source = $db["doubanx"]["dbdriver"]."://".
                  $db["doubanx"]["username"].":".
                  $db["doubanx"]["password"]."@".
                  $db["doubanx"]["hostname"]."/".
                  $db["doubanx"]["database"];
        $this->db =& load_database($source, true);
    }

    public function set_rate($type, $detail_id, $detail_name, $average, $vote, $star) {
        $this->db->replace($type, array(
            "id" => $detail_id,
            "name" => $detail_name,
            "average" => $average,
            "vote" => $vote,
            "star" => $star
        ));
    }

    public function get_rate($name, $type) {
        $result = $this->db->select("id, name, average, vote, star")
                           ->from($type)
                           ->like("name", $name)
                           ->get()
                           ->first_row();
        return $result;
    }
}
