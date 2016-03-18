<?php
class Api {
    public function __construct(){
        // parent::__construct();
        set_time_limit(0);
        date_default_timezone_set("Asia/Shanghai");
        include_once(BASEPATH."model/Xdouban.php");
        include_once(BASEPATH."lib/Snoopy.php");
        $this->xdouban = new Xdouban();
        $this->snoopy = new Snoopy();
    }

    /**
     * 抓取详情页
     */
    private function fetch_douban_detail($url) {
        // $this->load->library("snoopy");
        // 替换http，避免302跳转
        $url = str_replace("http://", "https://", $url);
        $this->snoopy->fetch($url);
        $detail_str = $this->snoopy->results;

        preg_match('/movie\.douban\.com\/subject\/(\d+)/i', $url, $match_id);
        preg_match('/<strong .*property="v:average">([0-9]\.[0-9])?<\/strong>/i', $detail_str, $match_average);
        preg_match('/<span property="v:votes">(\d+)?<\/span>/i', $detail_str, $match_vote);
        preg_match('/<div class="ll bigstar(\d{2})"><\/div>/i', $detail_str, $match_star);
        preg_match('/<span property="v:itemreviewed">(.*)?<\/span>/i', $detail_str, $match_name);

        $id = $match_id[1];
        $name = isset($match_name[1]) ? $match_name[1] : "";
        $average = isset($match_average[1]) ? $match_average[1] : "0.0";
        $vote = isset($match_vote[1]) ? $match_vote[1] : 0;
        $star = isset($match_star[1]) ? $match_star[1] : "00";

        // log_message("info", "DONE: $id\t$name");

        return array(
            "id" => $id,
            "name" => $name,
            "average" => $average,
            "vote" => $vote,
            "star" => $star
        );
    }

    public function fetch_douban() {
        $type_tag = array(
            "movie" => array(
                "热门","最新","经典","可播放","豆瓣高分","冷门佳片","华语","欧美","韩国","日本","动作","喜剧","爱情","科幻","悬疑","恐怖","成长"
            ),
            "tv" => array(
                "热门","美剧","英剧","韩剧","日剧","国产剧","港剧","日本动画"
            )
        );
        $list_path = "https://movie.douban.com/j/search_subjects?";

        // $this->load->library("snoopy");

        // 循环电影和电视剧
        foreach ($type_tag as $type => $tags) {
            $sort = "recommend"; // time / rank 可选
            $limit = 10;
            $start = 0;
            // 循环标签
            foreach ($tags as $tag) {
                $list_query = http_build_query(array(
                    "type" => $type,
                    "tag" => $tag,
                    "sort" => $sort,
                    "page_limit" => $limit,
                    "page_start" => $start
                ));
                // 得到ajax请求的url
                $list_url = $list_path.$list_query;
                $this->snoopy->fetch($list_url);
                $list_str = $this->snoopy->results;
                $list_obj = json_decode($list_str);
                $list_len = count($list_obj->subjects);
                if ($list_len !== 0) {
                    ++$start;
                    foreach ($list_obj->subjects as $subjects) {
                        $detail_id = $subjects->id;
                        $detail_name = $subjects->title;
                        $detail_url = $subjects->url;


                        $rate = $this->fetch_douban_detail($detail_url);
                        $average = $rate["average"];
                        $vote = $rate["vote"];
                        $star = $rate["star"];

                        // 数据库更新
                        $this->set_rate($detail_id, $detail_name, $average, $vote, $star);
                    }
                    // if ($start === 10) {exit;}
                } else {
                    break;
                }
            }
        }
    }

    private function fetch_film_list($url) {
        // $this->load->library("snoopy");
        $this->snoopy->fetch($url);
        $list_str = $this->snoopy->results;

        preg_match_all('/<h4 class="name"><a href=".*?" target="_blank" .*?>(.*)?<\/a>/i', $list_str, $match_names);
        // preg_match('/<span class="num">\d+? \/ (\d+)?<\/span>/i', $list_str, $match_count);
        preg_match('/<a class="pager_arrow pager_arrow_next" href="(.*)?" _hot="liebiao.upnext"><i>/i', $list_str, $match_next);

        // $loop_count = intval($match_count[1]);
        $next_url = $match_next[1];

        foreach($match_names[1] as $name) {
            $this->get_rate_online($name);
        }

        if (isset($next_url)) {
            $this->fetch_film_list($next_url);
        }
    }

    public function fetch_film() {
        $url = "http://film.qq.com/paylist/0/pay_-1_-1_-1_1_0_0_40.html";
        $this->fetch_film_list($url);
    }

    /**
     * 获取豆瓣信息
     */
    public function get_rate() {
        $name = trim(htmlspecialchars($_POST["name"]));
        if ($name !== "") {
            $rate = $this->get_rate_offline($name);
            $rate = (isset($rate) && !empty($rate)) ? $rate : $this->get_rate_online($name);
        } else {
            $rate = NULL;
        }

        header("Content-type: application/json");
        header("Access-Control-Allow-Origin: *");
        echo json_encode(array(
            "ret" => $rate ? 0 : 1,
            "data" => $rate ? $rate : (object) array()
        ));
    }

    /**
     * 录入数据库
     */
    private function set_rate($id, $name, $average, $vote, $star) {
        $this->xdouban->set_rate($id, $name, $average, $vote, $star);
    }

    /**
     * 从数据库里去读信息
     */
    private function get_rate_offline($name) {
        $rate = $this->xdouban->get_rate($name);
        return $rate;
    }

    /**
     * 从线上页面实时获取信息
     */
    private function get_rate_online($name) {
        // $this->load->library("snoopy");
        $url = "https://movie.douban.com/subject_search?search_text=$name";
        $this->snoopy->fetch($url);
        $search_str = $this->snoopy->results;
        preg_match('/<a class="nbg" href="https:\/\/movie\.douban\.com\/subject\/(\d+)?\/" onclick/i', $search_str, $match_id);

        $output = NULL;
        if (isset($match_id[1])) {
            $id = $match_id[1];
            $url = "https://movie.douban.com/subject/$id/";
            $rate = $this->fetch_douban_detail($url);

            $average = $rate["average"];
            $vote = $rate["vote"];
            $star = $rate["star"];
            $name = $rate["name"];

            // 抓到数据后插入数据库
            $this->set_rate($id, $name, $average, $vote, $star);
            $output = (object) array(
                "id" => $id,
                "name" => $name,
                "average" => $average,
                "vote" => $vote,
                "star" => $star
            );
        }

        return $output;
    }
}
