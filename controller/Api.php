<?php
class Api {
    private static $hosts = array(
        "film.qq.com",
        "v.qq.com",
        "www.youku.com",
        "movie.youku.com",
        "tv.youku.com",
        "v.youku.com",
        "movie.tudou.com",
        "tv.tudou.com",
        "vip.tudou.com",
        "www.tudou.com",
        "www.iqiyi.com",
        "vip.iqiyi.com",
        "list.iqiyi.com",
        "so.iqiyi.com",
        "www.le.com",
        "list.le.com",
        "tv.le.com",
        "tv.sohu.com",
        "film.sohu.com",
        "www.amazon.cn",
        "www.dangdang.com",
        "product.dangdang.com",
        "book.dangdang.com",
        "v.dangdang.com",
        "e.dangdang.com",
        "category.dangdang.com",
        "item.jd.com",
        "sale.jd.com",
        "e.jd.com",
        "book.jd.com",
        "list.jd.com",
        "search.e.jd.com",
        "movie.douban.com",
        "book.douban.com",
        "read.douban.com",
        "book.tmall.com",
        "detail.tmall.com"
    );

    public function __construct() {
        set_time_limit(0);
        date_default_timezone_set("Asia/Shanghai");
        include_once(BASEPATH."lib/Snoopy.php");
        include_once(BASEPATH."lib/Log.php");

        $this->snoopy = new Snoopy();
        $this->log = new Log();
    }

    public function get_rules() {
        header("Content-type: application/json");
        header("Access-Control-Allow-Origin: *");
        $host = trim(htmlspecialchars($_POST["host"]));
        include_once(BASEPATH."controller/Rule.php");
        $rule = new Rule();
        $output = $rule->get_rule($host);
        echo json_encode(array(
            "ret" => $output ? 0 : 1,
            "data" => $output ? (object) array(
                "rules" => $output
            ) : (object) array()
        ));
    }

    /**
     * 获取豆瓣简介
     */
    public function get_intro() {
        header("Content-type: application/json");
        header("Access-Control-Allow-Origin: *");
        $name = trim(htmlspecialchars($_POST["name"]));
        $type = trim(htmlspecialchars($_POST["type"]));
        $href = trim(htmlspecialchars($_POST["href"]));
        $isbn = trim(htmlspecialchars($_POST["isbn"]));

        $output = NULL;
        if ($name !== "" &&                                             // 名称非空验证
            in_array($type, array("movie", "book")) &&                  // 类型验证
            !is_null($_SERVER["HTTP_REFERER"]) &&                       // referer 非空验证
            in_array($this->match_host($_SERVER["HTTP_REFERER"]), self::$hosts)  // referer 白名单验证
            ) {

            if ($href !== "") {
                if ($this->match_host($href) === "item.jd.com") {
                    $href = str_replace("https://", "http://", $href);
                }
                $this->snoopy->fetch($href);
                $html_str = $this->snoopy->results;
                preg_match('/97[89]\d{9}[xX\d]/i', $html_str, $matches);

                if ($matches) {
                    $name = $matches[0];
                }
                $suggest_arr = $this->get_suggest($name, $type);
                if (count($suggest_arr) !== 0) {
                    $suggest_obj = $suggest_arr[0];
                    $output = $suggest_obj;
                }
            } else {
                $suggest_arr = $this->get_suggest($name, $type);
                if (count($suggest_arr) !== 0) {
                    $suggest_obj = $suggest_arr[0];
                    $output = $suggest_obj;
                }
            }
        }

        echo json_encode(array(
            "ret" => $output ? 0 : 1,
            "data" => $output ? $output : (object) array()
        ));
    }

    /**
     * 获取豆瓣评论
     */
    public function get_review() {
        $time_a = time();
        header("Content-type: application/json");
        header("Access-Control-Allow-Origin: *");
        $id = (int) trim($_POST["id"]);

        if ($id > 0 &&
            !is_null($_SERVER["HTTP_REFERER"]) &&                       // referer 非空验证
            in_array($this->match_host($_SERVER["HTTP_REFERER"]), self::$hosts)  // referer 白名单验证
            ) {
            $time_b = time();
            // $this->log->message("INFO", "[get_review]\t验证参数耗时：".($time_b-$time_a));

            $url = "https://www.douban.com/feed/subject/$id/reviews";
            $this->snoopy->fetch($url);

            $time_c = time();
            // $this->log->message("INFO", "[get_review]\t抓取评论耗时：".($time_c-$time_b));

            $xml_str = $this->snoopy->results;
            $xml_str = str_replace("content:encoded", "encoded", $xml_str);
            $xml_str = str_replace("dc:creator", "creator", $xml_str);
            $xml_obj = simplexml_load_string($xml_str);
            $xml_arr = json_decode(json_encode($xml_obj), true);
            $items = $xml_arr["channel"]["item"];

            $output = array();
            $flag = false;
            foreach($items as $key => $item) {
                if (is_array($item)) {
                    array_push($output, $this->format_data($item));
                } else {
                    $flag = true;
                    break;
                }
            }

            if ($flag) {
                $output = array($this->format_data($items));
            }

            $time_d = time();
            // $this->log->message("INFO", "[get_review]\t处理数据耗时：".($time_d-$time_c));

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
        $time_a = time();
        header("Content-type: application/json");
        header("Access-Control-Allow-Origin: *");
        $name = trim(htmlspecialchars($_POST["name"]));
        $type = trim(htmlspecialchars($_POST["type"]));

        if ($name !== "" &&                                             // 名称非空验证
            in_array($type, array("movie", "book")) &&                  // 类型验证
            !is_null($_SERVER["HTTP_REFERER"]) &&                       // referer 非空验证
            in_array($this->match_host($_SERVER["HTTP_REFERER"]), self::$hosts)  // referer 白名单验证
            ) {

            $time_b = time();
            // $this->log->message("INFO", "[get_rate]\t验证参数耗时：".($time_b-$time_a));

            // 验证通过才连接数据库
            include_once(BASEPATH."model/DoubanX.php");
            $this->doubanx = new DoubanX();

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
     * 格式化数据
     */
    private function format_data($data) {
        $creator = $data["creator"];
        $title = preg_replace('/\s\(评论:.*\)$/', '', $data["title"]);
        $link = $data["link"];
        $date = strtotime($data["pubDate"]);

        return (object) array(
            "creator" => $creator,
            "title" => $title,
            "link" => $link,
            "date" => $date
        );
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
        preg_match('/<span property="v:itemreviewed">(.*)?<\/span>[\s\S]*<strong .*property="v:average">\s*([0-9]\.[0-9])?\s*<\/strong>[\s\S]*<div class="ll bigstar(\d{2})"><\/div>[\s\S]*<span property="v:votes">(\d+)?<\/span>/i', $detail_str, $matches);
        preg_match_all('/<span class="rating_per">([0-9]+\.[0-9])?\%<\/span>/i', $detail_str, $match_rate);

        $id = $match_id[1];
        $name = isset($matches[1]) ? $matches[1] : "";
        $average = isset($matches[2]) ? $matches[2] : "0.0";
        $star = isset($matches[3]) ? $matches[3] : "00";
        $vote = isset($matches[4]) ? $matches[4] : 0;
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
     * 录入数据库
     */
    private function set_rate($type, $id, $name, $average, $vote, $star, $rate) {
        $time_a = time();
        $this->doubanx->set_rate($type, $id, $name, $average, $vote, $star, $rate);
        $time_b = time();
        // $this->log->message("INFO", "[get_rate]\t更新数据耗时：".($time_b-$time_a));
    }

    /**
     * 从数据库里去读信息
     */
    private function get_rate_offline($name, $type) {
        $time_a = time();
        $rate = $this->doubanx->get_rate($name, $type);
        $time_b = time();
        // $this->log->message("INFO", "[get_rate]\t查询数据耗时：".($time_b-$time_a));

        return $rate;
    }

    /**
     * 从线上页面实时获取信息
     */
    private function get_rate_online($name, $type) {
        $suggest_arr = $this->get_suggest($name, $type);

        $output = NULL;
        if (count($suggest_arr) !== 0) {
            $suggest_obj = $suggest_arr[0];
            $id = $suggest_obj->id;
            $url = $suggest_obj->alt;

            $result = $this->fetch_douban_detail($url, $type);

            $time_c = time();
            // $this->log->message("INFO", "[get_rate]\t抓取详情耗时：".($time_c-$time_b));

            $average = $result["average"];
            $vote = $result["vote"];
            $star = $result["star"];
            $name = $result["name"];
            $rate = $result["rate"];

            if ($result["id"] !== "") {
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
        }

        return $output;
    }

    /**
     * 获取搜索结果
     */
    private function get_suggest($name, $type) {
        $name = urlencode($name);
        $url = "https://api.douban.com/v2/$type/search?count=1&q=$name";

        $time_a = time();
        $this->snoopy->fetch($url);

        $time_b = time();
        // $this->log->message("INFO", "[get_rate]\t抓取搜索耗时：".($time_b-$time_a));

        $search_str = $this->snoopy->results;
        $search_obj = json_decode($search_str);

        // 如果没有获取到重试一次
        if (
            $search_obj->count === 0 &&
            $search_obj->start === 0 &&
            $search_obj->total === -1
        ) {
            $this->snoopy->fetch($url);
            $search_str = $this->snoopy->results;
            $search_obj = json_decode($search_str);
        }

        $suggest_key = $type === "movie" ? "subjects" : "books";
        $suggest_arr = $search_obj->{$suggest_key};

        return $suggest_arr;
    }
}
