<?php
class Rule {
    private static $amazon = array(
        array(
            "match" => "$('#productTitle').length !== 0",
            "tagIsbn" => "#detail_bullets_id",
            "tagTitle" => "#productTitle",
            "type" => "book",
            "event" => "pageload"
        ),
        array(
            "match" => "$('#ebooksProductTitle').length !== 0",
            "tagIsbn" => "body",
            "tagTitle" => "#ebooksProductTitle",
            "type" => "book",
            "event" => "pageload"
        ),
        array(
            "match" => "/\/gp\/product\//i",
            "tags" => array(
                    ".zg_item_compact",
                    ".a-spacing-medium.p13n-sc-list-item",
                    ".a-carousel-card",
                    ".rhf-RVIs",
                    ".floor-hotasin-item",
                    ".a-fixed-left-grid-col",
                    ".a-link-normal"
                ),
            "type" => "book",
            "event" => "mouseover"
        ),
        array(
            "match" => "/\/dp\//i",
            "tags" => array(
                    ".a-carousel-card",
                    "#recentlyViewed td.text",
                    ".ch-tabwidget-pc-contentAsin",
                    ".feed-carousel-card",
                    ".a-fixed-left-grid-col .a-link-normal",
                    ".a-unordered-list .a-link-normal",
                    ".acsUxWidget .bxc-grid__column"
                ),
            "type" => "book",
            "event" => "mouseover"
        )
    );

    private static $dangdang = array(
        array(
            "match" => "$.trim($('.breadcrumb a').eq(0).text()) === '图书' && $('.name_info h1').length !== 0",
            "tagIsbn" => "#detail_describe",
            "tagTitle" => ".name_info h1",
            "type" => "book",
            "event" => "pageload"
        ),
        array(
            "match" => "/\b2\d{7}\.html/i",
            "tags" => array(
                    ".group_buy .over li",
                    ".mbox_another .over li",
                    ".product_content .aside li"
                ),
            "type" => "book",
            "event" => "mouseover"
        ),
        array(
            "match" => "/product\.dangdang\.com\/2\d{7}.*\.html/i",
            "tags" => array(
                    ".product_ul li",
                    ".list_aa li",
                    ".list_ab li",
                    ".content li",
                    ".shoplist .pic"
                ),
            "type" => "book",
            "event" => "mouseover"
        ),
        array(
            "match" => "/a\.dangdang\.com\/jump\.php\?.*/i",
            "tags" => array(
                    ".product_content .aside li"
                ),
            "type" => "book",
            "event" => "mouseover"
        ),
        array(
            "match" => "/\/product\/\.\/\d+\.html/i",
            "tags" => array(
                    ".limitCell",
                    ".bookCell",
                    ".bigCell",
                    ".topRightBookCell a",
                    ".list_content .book_content"
                ),
            "type" => "book",
            "event" => "mouseover"
        )
    );

    private static $jd = array(
        array(
            "match" => "(window.location.host === 'item.jd.com') && $.trim($('.breadcrumb strong a').text()) === '图书'",
            "tagIsbn" => "#parameter2",
            "tagTitle" => "#name h1",
            "type" => "book",
            "event" => "pageload"
        ),
        array(
            "match" => "(window.location.host === 'e.jd.com') && ($('.bookInfo').length > 0)",
            "tagIsbn" => ".bookInfo",
            "tagTitle" => ".sku-name",
            "type" => "book",
            "event" => "pageload"
        ),
        array(
            "match" => "(window.location.host === 'read.jd.com') && ($('.works h1').length > 0)",
            "tagIsbn" => ".book-authorinfo",
            "tagTitle" => ".works h1",
            "type" => "book",
            "event" => "pageload"
        ),
        array(
            "match" => "/\/\/item\.jd\.com\/\d*/i",
            "tags" => array(
                    ".book_discount .list li",
                    ".book_new .list li",
                    ".book_act .list li",
                    ".book_hot .list li",
                    ".book_found .list li",
                    ".content-item li",
                    "#combine-con .p-name",
                    ".related-buy li",
                    ".rec-item li",
                    "#guess-scroll li",
                    "#plist li",
                    ".ab-goods li",
                    "#J_goodsList li",
                    ".user_k_spfy li",
                    ".newbook-shelves li",
                    ".best-sale-bot li",
                    ".floorbook li",
                    "#plist .p-name",
                    ".findgoods_list_content li",
                    ".j-module li",
                    ".p-blist li",
                    ".blist li",
                    ".layout-m li",
                    ".ui-switchable-pannel li",
                    ".m-list li",
                    ".public-list li"
                ),
            "type" => "book",
            "event" => "mouseover"
        ),
        array(
            "match" => "/\/\/e\.jd\.com\/\d*/i",
            "tags" => array(
                    ".best-sale-bot li"
                ),
            "type" => "book",
            "event" => "mouseover"
        ),
        array(
            "match" => "/\/\/read\.jd\.com\/\d*/i",
            "tags" => array(
                    "#reco-list li",
                    ".m-list li",
                    ".public-list .tabcon li"
                ),
            "type" => "book",
            "event" => "mouseover"
        )
    );

    private static $tmall = array(
        array(
            "match" => "location.href.match(/detail\.tmall\.com\/item\.htm.*spm=a22/i)",
            "tagIsbn" => "#attributes",
            "tagTitle" => ".tb-detail-hd h1",
            "type" => "book",
            "event" => "pageload"
        )
    );

    private static $douban = array(
        array(
            "match" => "location.href.match(/\/\/read\.douban\.com\/ebook\/\d+/i)",
            "tagIsbn" => ".article-profile-secondary",
            "tagTitle" => ".article-profile-bd h1",
            "type" => "book",
            "event" => "pageload"
        )
    );

    private static $qq = array(
        array(
            "match" => "(window.location.host === 'v.qq.com') && $('#tenvideo_player').length !== 0",
            "tagTitle" => "h1.video_title a",
            "type" => "movie",
            "event" => "pageload"
        ),
        array(
            "match" => "/\/\/v\.qq\.com\/x\/cover\/.*/i",
            "tags" => array(
                    ".figures_list li"
                ),
            "type" => "movie",
            "event" => "mouseover"
        ),
        array(
            "match" => "/\/\/v\.qq\.com\/detail\/.*/i",
            "tags" => array(
                    ".figures_list li"
                ),
            "type" => "movie",
            "event" => "mouseover"
        ),
        array(
            "match" => "/\/\/v\.qq\.com\/x\/search\/\?q.*/i",
            "tags" => array(
                    ".table_list li .item"
                ),
            "type" => "movie",
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

            case "www.dangdang.com":
            case "product.dangdang.com":
            case "book.dangdang.com":
            case "v.dangdang.com":
            case "e.dangdang.com":
            case "category.dangdang.com":
                $rule = self::$dangdang;
            break;

            case "item.jd.com":
            case "sale.jd.com":
            case "e.jd.com":
            case "book.jd.com":
            case "list.jd.com":
            case "mall.jd.com":
            case "fxhh.jd.com":
            case "read.jd.com":
                $rule = self::$jd;
            break;

            case "detail.tmall.com":
                $rule = self::$tmall;
            break;

            case "read.douban.com":
                $rule = self::$douban;
            break;

            case "v.qq.com":
            case "film.qq.com":
                $rule = self::$qq;
            break;
        }

        return $rule;
    }
}
?>
