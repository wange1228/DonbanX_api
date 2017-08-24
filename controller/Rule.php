<?php
class Rule {
    private static $amazon = array(
        array(
            "match" => "$('#productTitle').length !== 0",
            "tag" => "#detail_bullets_id",
            "type" => "book",
            "event" => "pageload"
        ),
        array(
            "match" => "$('#ebooksProductTitle').length !== 0",
            "tag" => "#ebooksProductTitle",
            "type" => "book",
            "event" => "pageload"
        ),
        array(
            "match" => "/\/gp\/product\//i",
            "tag" => ".zg_item_compact, .a-spacing-medium.p13n-sc-list-item, .a-carousel-card, .rhf-RVIs, .floor-hotasin-item, .a-fixed-left-grid-col, .a-link-normal",
            "type" => "book",
            "event" => "mouseover"
        ),
        array(
            "match" => "/\/dp\//i",
            "tag" => ".a-carousel-card, #recentlyViewed td.text, .ch-tabwidget-pc-contentAsin, .feed-carousel-card, .a-fixed-left-grid-col .a-link-normal, .a-unordered-list .a-link-normal, .acsUxWidget .bxc-grid__column",
            "type" => "book",
            "event" => "mouseover"
        )
    );

    public function __construct() {
        set_time_limit(0);
        date_default_timezone_set("Asia/Shanghai");
    }

    public function get_rule($host) {
        $rule = array();
        switch($host) {
            case "www.amazon.cn":
                $rule = self::$amazon;
            break;
        }

        return $rule;
    }
}
?>
