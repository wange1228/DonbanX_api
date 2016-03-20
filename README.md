# DoubanX - API
> 抓取豆瓣电影和豆瓣读书的评分信息，配合[DoubanX - Chrome 扩展](https://github.com/wange1228/DoubanX_crx)使用效果最佳。

## Params
```
  name      {String}    豆瓣电影或者豆瓣读书的标题  
  type      {String}    有 movie / book 两种可选类型  
  force     {Boolean}   是否强制更新，true 从豆瓣重新抓取，false 从数据库读取  
  token     {Number}    CSRF 校验
```

## Return
```
  ret       {Number}    返回码，0表示成功，非0表示失败  
  data      {Object}    返回数据  
    id      {String}    豆瓣的id，如：20645098  
    name    {String}    从豆瓣中获取到的实际标题，如：小王子 Le Petit Prince  
    average {String}    豆瓣平均分，如：8.1  
    vote    {String}    豆瓣评分人数，如：97720  
    star    {String}    豆瓣评星，用 00/05/10/15/20/25/30/45/50 表示，如：40  
    rate    {String}    豆瓣各评分区间的比例，以数组的 json 格式返回，如：["35.0","39.5","20.9","3.3","1.2"]  
    type    {String}    豆瓣类似，movie 或者 book  
    time    {String}    抓取豆瓣存入数据库的时间戳，如：2016-03-20 15:24:38
```

## License
Released under [MIT](http://rem.mit-license.org/)  LICENSE
