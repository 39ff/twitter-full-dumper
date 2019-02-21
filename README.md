# twitter-full-dumper
crawl all tweets without api-limit,full user dump(images,videos,tweets) 


## Install
PHP7+
```text
wget https://getcomposer.org/composer.phar
chmod +x composer.phar
./composer.phar install
```

```text
Usage: php run.php screen_name maxCrawlDay
Example: php run.php youtube 60
```


## Save Directory 
```text
./dump
  /screen_name
   /timestamp/tweets.tsv (tweet_id,text,created)
             /json/xxx.json      (raw json files)
             /media/xxx.mp4,.jpg     (include image and videos)
 
```