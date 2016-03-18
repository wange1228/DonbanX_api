<?php
class Api {
    public function __construct(){
        // parent::__construct();
        set_time_limit(0);
        date_default_timezone_set("Asia/Shanghai");
        include_once(BASEPATH."model/DoubanX.php");
        include_once(BASEPATH."lib/Snoopy.php");
        $this->doubanx = new DoubanX();
        $this->snoopy = new Snoopy();
    }

    /**
     * 抓取详情页
     */
    private function fetch_douban_detail($url, $type) {
        // $this->load->library("snoopy");
        // 替换http，避免302跳转
        $url = str_replace("http://", "https://", $url);
        $this->snoopy->fetch($url);
        $detail_str = $this->snoopy->results;

        if ($type === "movie") {
            preg_match('/movie\.douban\.com\/subject\/(\d+)/i', $url, $match_id);
        } else if ($type === "book") {
            preg_match('/book\.douban\.com\/subject\/(\d+)/i', $url, $match_id);
        }
        preg_match('/<strong .*property="v:average">\s*([0-9]\.[0-9])?\s*<\/strong>/i', $detail_str, $match_average);
        preg_match('/<span property="v:votes">(\d+)?<\/span>/i', $detail_str, $match_vote);
        preg_match('/<div class="ll bigstar(\d{2})"><\/div>/i', $detail_str, $match_star);
        preg_match('/<span property="v:itemreviewed">(.*)?<\/span>/i', $detail_str, $match_name);
        preg_match_all('/<span class="rating_per">([0-9]+\.[0-9])?\%<\/span>/i', $detail_str, $match_rate);

        $id = $match_id[1];
        $name = isset($match_name[1]) ? $match_name[1] : "";
        $average = isset($match_average[1]) ? $match_average[1] : "0.0";
        $vote = isset($match_vote[1]) ? $match_vote[1] : 0;
        $star = isset($match_star[1]) ? $match_star[1] : "00";
        $rate = isset($match_rate[1]) ? $match_rate[1] : array();

        return array(
            "id" => $id,
            "name" => $name,
            "average" => $average,
            "vote" => $vote,
            "star" => $star,
            "rate" => json_encode($rate)
        );
    }

    /**
     * 获取豆瓣信息
     */
    public function get_rate() {
        header("Content-type: application/json");
        header("Access-Control-Allow-Origin: *");
        $name = trim(htmlspecialchars($_POST["name"]));
        $type = trim(htmlspecialchars($_POST["type"]));
        if ($name !== "" && in_array($type, array("movie", "book"))) {
            $rate = $this->get_rate_offline($name, $type);
            $rate = (isset($rate) && !empty($rate)) ? $rate : $this->get_rate_online($name, $type);
        } else {
            $rate = NULL;
        }

        echo json_encode(array(
            "ret" => $rate ? 0 : 1,
            "data" => $rate ? $rate : (object) array()
        ));
    }

    /**
     * 录入数据库
     */
    private function set_rate($type, $id, $name, $average, $vote, $star, $rate) {
        $this->doubanx->set_rate($type, $id, $name, $average, $vote, $star, $rate);
    }

    /**
     * 从数据库里去读信息
     */
    private function get_rate_offline($name, $type) {
        $rate = $this->doubanx->get_rate($name, $type);
        return $rate;
    }

    /**
     * 从线上页面实时获取信息
     */
    private function get_rate_online($name, $type) {
        $url = "https://$type.douban.com/subject_search?search_text=$name";
        $this->snoopy->fetch($url);
        $search_str = $this->snoopy->results;
        if ($type === "movie") {
            preg_match('/<a class="nbg" href="https:\/\/movie\.douban\.com\/subject\/(\d+)?\/"/i', $search_str, $match_id);
        } else if ($type === "book") {
            preg_match('/<a class="nbg" href="https:\/\/book\.douban\.com\/subject\/(\d+)?\/"/i', $search_str, $match_id);
        }

        $output = NULL;
        if (isset($match_id[1])) {
            $id = $match_id[1];
            $url = "https://$type.douban.com/subject/$id/";
            $result = $this->fetch_douban_detail($url, $type);

            $average = $result["average"];
            $vote = $result["vote"];
            $star = $result["star"];
            $name = $result["name"];
            $rate = $result["rate"];

            // 抓到数据后插入数据库
            $this->set_rate($type, $id, $name, $average, $vote, $star, $rate);
            $output = (object) array(
                "id" => $id,
                "name" => $name,
                "average" => $average,
                "vote" => $vote,
                "star" => $star,
                "rate" => $rate,
                "type" => $type,
                "time" => date("Y-m-d H:i:s")
            );
        }

        return $output;
    }
}
