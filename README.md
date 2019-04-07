# twitter-full-dumper
crawl all publicTweets without api-limit,full user dump(images,videos,tweets) 


## Install
PHP7+
```text
wget https://getcomposer.org/composer.phar
chmod +x composer.phar
./composer.phar install
```

```text
Usage: php run.php screen_name maxCrawlDay proxylist.txt(option)
Example: php run.php youtube 60
```

proxylist.txt format example
```text
http://lum-customer-...-zone-static-ip-185.158.103.xx:password@zproxy.lum-superproxy.io:22225
http://lum-customer-...-zone-static-ip-185.158.103.xx:password@zproxy.lum-superproxy.io:22225
http://lum-customer-...-zone-static-ip-185.158.103.xx:password@zproxy.lum-superproxy.io:22225
...
protocol(http/https/socks5)://username:password@ipAddress:port
```
you can buy proxy from [luminati](https://luminati.io/?affiliate=ref_5b8920ee6a9af5c0e0b39d36)

## Save Directory 
```text
./dump
  /screen_name
   /timestamp/tweets.tsv (tweet_id,text,created)
             /json/xxx.json      (raw json files)
             /media/xxx.mp4,.jpg     (include image and videos)
 
```