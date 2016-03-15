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
class Xdouban extends CI_Model {
    public function __construct() {
        $this->db = $this->load->database('xdouban', TRUE);
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
