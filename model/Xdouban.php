<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
CREATE TABLE movie(
     `id` INT(11) PRIMARY KEY,
     `name` VARCHAR(50) NOT NULL,
     `average` VARCHAR(10) DEFAULT "0.0",
     `vote` INT(11) DEFAULT 0,
     `star` VARCHAR(2) DEFAULT "00",
     `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */
class Xdouban {
    public function __construct() {
        include_once(BASEPATH."database/DB.php");
        include_once(BASEPATH."config/database.php");
        $source = $db["doubanx"]["dbdriver"]."://".
                  $db["doubanx"]["username"].":".
                  $db["doubanx"]["password"]."@".
                  $db["doubanx"]["hostname"]."/".
                  $db["doubanx"]["database"];
        $this->db =& load_database($source, true);
        $this->tb = 'movie';
    }

    public function set_rate($detail_id, $detail_name, $average, $vote, $star) {
        $this->db->replace($this->tb, array(
            "id" => $detail_id,
            "name" => $detail_name,
            "average" => $average,
            "vote" => $vote,
            "star" => $star
        ));
    }

    public function get_rate($name) {
        $result = $this->db->select("id, name, average, vote, star")
                           ->from($this->tb)
                           ->like("name", $name)
                           ->get()
                           ->first_row();
        return $result;
    }

    public function get_rates($names) {
        $this->db->select("id, name, average, vote, star")
                 ->from($this->tb);
        foreach ($names as $name) {
            $this->db->or_like("name", $name);
        }
        $result = $this->db->get()->result();

        return $result;
    }
}
