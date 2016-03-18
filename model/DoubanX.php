<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
CREATE TABLE subject(
     `id` INT(11) PRIMARY KEY comment "豆瓣id",
     `name` VARCHAR(50) NOT NULL comment "名称",
     `average` FLOAT(3,1) DEFAULT 0.0 comment "评分数",
     `vote` INT(11) DEFAULT 0 comment "评分人数",
     `star` VARCHAR(2) DEFAULT "00" comment "评星数",
     `rate` VARCHAR(100) DEFAULT "[]" comment "以json的格式存储1-5星的评分比例",
     `type` VARCHAR(10) NOT NULL comment "movie或者book类型",
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
        $this->tb = "subject";
    }

    public function set_rate($type, $detail_id, $detail_name, $average, $vote, $star, $rate) {
        $this->db->replace($this->tb, array(
            "id" => $detail_id,
            "name" => $detail_name,
            "average" => $average,
            "vote" => $vote,
            "star" => $star,
            "type" => $type,
            "rate" => $rate
        ));
    }

    public function get_rate($name, $type) {
        $result = $this->db->select("id, name, average, vote, star, rate, type, time")
                           ->from($this->tb)
                           ->like("name", $name)
                           ->where("type", $type)
                           ->get()
                           ->first_row();
        return $result;
    }
}
