<?php
class Api {
    private static $hosts = array(
        "film.qq.com",
        "v.qq.com",
        "v.youku.com",
        "www.tudou.com",
        "www.iqiyi.com",
        "www.le.com",
        "tv.sohu.com",
        "film.sohu.com",
        "www.amazon.cn",
        "product.dangdang.com",
        "item.jd.com"
    );

    public function __construct() {
        set_time_limit(0);
        date_default_timezone_set("Asia/Shanghai");
        include_once(BASEPATH."lib/Snoopy.php");
        $this->snoopy = new Snoopy();
    }

    /**
     * 匹配指定链接中的host
     */
    private function match_host($url) {
        $result = parse_url($url);
        return $result["host"];
    }

    /**
     * 抓取详情页
     */
    private function fetch_douban_detail($url, $type) {
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
     * 获取豆瓣评论
     */
    public function get_review() {
        header("Content-type: application/json");
        header("Access-Control-Allow-Origin: *");
        $id = (int) trim($_POST["id"]);

        if ($id > 0 &&
            !is_null($_SERVER["HTTP_REFERER"]) &&                       // referer 非空验证
            in_array($this->match_host($_SERVER["HTTP_REFERER"]), self::$hosts)  // referer 白名单验证
            ) {
            $url = "https://www.douban.com/feed/subject/$id/reviews";
            $this->snoopy->fetch($url);
            $xml_str = $this->snoopy->results;
            $xml_str = preg_replace('/\sxmlns="(.*?)"/', ' _xmlns="${1}"', $xml_str);
            $xml_str = preg_replace('/<(\/)?(\w+):(\w+)/', '<${1}${2}_${3}', $xml_str);
            $xml_str = preg_replace('/(\w+):(\w+)="(.*?)"/', '${1}_${2}="${3}"', $xml_str);
            $xml_obj = simplexml_load_string($xml_str);
            $xml_arr = json_decode(json_encode($xml_obj), true);
            $items = $xml_arr["channel"]["item"];

            $output = array();
            foreach($items as $item) {
                $creator = $item["dc_creator"];
                $title = preg_replace('/\s\(评论:.*\)$/', '', $item["title"]);
                $link = $item["link"];
                $date = strtotime($item["pubDate"]);

                array_push($output, (object) array(
                    "creator" => $creator,
                    "title" => $title,
                    "link" => $link,
                    "date" => $date
                ));
            }
        } else {
            $output = array();
        }

        echo json_encode(array(
            "ret" => !empty($output) ? 0 : 1,
            "data" => $output
        ));
    }

    /**
     * 获取豆瓣信息
     */
    public function get_rate() {
        header("Content-type: application/json");
        header("Access-Control-Allow-Origin: *");
        $name = trim(htmlspecialchars($_POST["name"]));
        $type = trim(htmlspecialchars($_POST["type"]));
        $force = !!$_POST["force"];

        if ($name !== "" &&                                             // 名称非空验证
            in_array($type, array("movie", "book")) &&                  // 类型验证
            !is_null($_SERVER["HTTP_REFERER"]) &&                       // referer 非空验证
            in_array($this->match_host($_SERVER["HTTP_REFERER"]), self::$hosts)  // referer 白名单验证
            ) {

            // 验证通过才连接数据库
            include_once(BASEPATH."model/DoubanX.php");
            $this->doubanx = new DoubanX();

            // 强制更新
            if ($force) {
                $rate = $this->get_rate_online($name, $type);
            } else {
                $rate = $this->get_rate_offline($name, $type);
                $rate = (isset($rate) && !empty($rate)) ? $rate : $this->get_rate_online($name, $type);
            }
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
        $name = urlencode($name);
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
