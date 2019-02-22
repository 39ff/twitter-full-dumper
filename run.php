<?php
require './vendor/autoload.php';
require 'TweetParser.php';

$to = new \koulab\UltimateTwitter\Client();
if(empty($argv[1])){
    echo 'php run.php screen_name(string) crawlDay(int)'.PHP_EOL;
    exit;
}
$screen_name = $argv[1];
$max_crawl_day = $argv[2] ? $argv[2] : 60;

$save_path = 'dump'.DIRECTORY_SEPARATOR.$screen_name.DIRECTORY_SEPARATOR.time();

mkdir($save_path,0777,true);
mkdir($save_path.DIRECTORY_SEPARATOR.'media',0777);
mkdir($save_path.DIRECTORY_SEPARATOR.'json',0777);
$date = new DateTime();

$stream = fopen($save_path.DIRECTORY_SEPARATOR.'tweets.tsv', 'wb');

stream_filter_prepend($stream, 'convert.iconv.UTF-8/UTF-16LE');
fwrite($stream, "\xEF\xBB\xBF");
fputcsv($stream, [
    "id_str",
    "text",
    "created_at"
],"\t");
for($i = 0; $i < $max_crawl_day; $i++){
    $date->modify('-1 days');
    $q = [
        'q' => 'from:' . $screen_name . ' since:' . $date->format('Y-m-d') . ' until:' . $date->modify('+2 days')->format('Y-m-d'),
        'count' => '100',
        'f'=>'tweets',
        'vertical'=>'default',
        'src'=>'typd',
        'include_available_features'=>'1',
        'include_entities'=>'1',
        'max_position'=>'',
        'reset_error_state'=>'false'
    ];
    var_dump($q['q']);
    $date->modify('-2 days');
    do {
        $rs = fopen($save_path . DIRECTORY_SEPARATOR . 'json' . DIRECTORY_SEPARATOR . time() . '.json', 'w');
        $response = $to->get('https://twitter.com/i/search/timeline', [
            'query' => $q,
            'connect_timeout'=>'30',
            'timeout'=>'30',
            'read_timeout'=>'30',
            'headers'=>[
                'authority'=>'twitter.com',
                'x-requested-with'=>'XMLHttpRequest',
                'x-twitter-active-user'=>'yes',
                'accept-encoding'=>'gzip, deflate, br',
                'user-agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.109 Safari/537.36',
                'accept-language'=>'ja-jp',
                'accept'=>'application/json, text/javascript, */*; q=0.01',
            ]
        ]);
        fwrite($rs, $response);
        fclose($rs);
        var_dump($response);
        $json = json_decode($response);
        $tweets = TweetParser::parseLegacyTimeline($json->items_html);
        foreach($tweets as $id) {
            $to = new \koulab\UltimateTwitter\Client();
            $single_tweet = $to->get('https://api.twitter.com/1.1/statuses/show.json', [
                'headers' => [
                    'authorization' => 'Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA'
                ],
                'query' => [
                    'id' => $id
                ]
            ]);
            $status = json_decode($single_tweet);
            printf("%s\t%s\t%s\r\n",$status->id_str,$q['q'],$status->text);
            fputcsv($stream, [
                $status->id_str,
                str_replace([PHP_EOL,"\r","\n","\r\n"],'',$status->text),
                $status->created_at
            ], "\t");
            if (isset($status->entities->media)) {
                foreach ($status->entities->media as $media) {
                    $generalEntity = $save_path . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . basename($media->media_url);
                    if (file_exists($generalEntity)) {
                        continue;
                    }
                    if (in_array(pathinfo($media->media_url, PATHINFO_EXTENSION), ['jpg','png'])) {
                        $media->media_url = $media->media_url.':orig';
                    }
                    $to->get($media->media_url, ['sink' => fopen($generalEntity, 'w')]);
                }
            }
            if (isset($status->extended_entities->media)) {
                foreach ($status->extended_entities->media as $media) {
                    $generalEntity = $save_path . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . basename($media->media_url);
                    if (!file_exists($generalEntity)) {
                        $to->get($media->media_url, ['sink' => fopen($generalEntity, 'w')]);
                    }
                    if (isset($media->video_info->variants)) {
                        foreach ($media->video_info->variants as $video) {
                            $generalEntity = $save_path . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . strtok(basename($video->url),'?');
                            if (!file_exists($generalEntity)) {
                                $to->get($video->url, ['sink' => fopen($generalEntity, 'w')]);
                            }
                        }
                    }

                }
            }
        }

        if(!empty($json->min_position)){
            $q['max_position'] = $json->min_position;
        }

    } while (!empty($json->min_position) && $json->has_more_items === true && count($json->statuses) > 1);
}
