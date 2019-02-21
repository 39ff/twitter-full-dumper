<?php
require './vendor/autoload.php';
$to = new \koulab\UltimateTwitter\Client();
$screen_name = $argv[1];
$max_crawl_day = 60;

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
        'q' => 'from:' . $screen_name . ' since:' . $date->format('Y-m-d') . ' until:' . $date->modify('+1 days')->format('Y-m-d'),
        'count' => '100'
    ];
    $date->modify('-1 days');
    do {
        var_dump($q);
        //sleep(1);
        $rs = fopen($save_path . DIRECTORY_SEPARATOR . 'json' . DIRECTORY_SEPARATOR . time() . '.json', 'w');
        $response = $to->get('https://api.twitter.com/1.1/search/tweets.json', [
            'headers' => [
                'authorization' => 'Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA'
            ],
            'query' => $q
        ]);
        fwrite($rs, $response);
        fclose($rs);
        $json = json_decode($response);

        foreach ($json->statuses as $status) {
            echo $status->text . ':' . $status->id_str . PHP_EOL;
            fputcsv($stream, [
                $status->id_str,
                $status->text,
                $status->created_at
            ],"\t");
            if (isset($status->entities->media)) {
                foreach ($status->entities->media as $media) {
                    $generalEntity = $save_path . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . basename($media->media_url);
                    if (file_exists($generalEntity)) {
                        continue;
                    }
                    $to->get($media->media_url, ['sink' => fopen($generalEntity, 'w')]);
                }
            }
            if (isset($status->extended_entities->media)) {
                foreach ($status->extended_entities->media as $media) {
                    $generalEntity = $save_path . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . basename($media->media_url);
                    if (file_exists($generalEntity)) {
                        continue;
                    }
                    $to->get($media->media_url, ['sink' => fopen($generalEntity, 'w')]);
                }
            }
        }
        $q['max_id'] = null;
        if(isset($json->search_metadata->next_results)) {
            parse_str(parse_url($json->search_metadata->next_results, PHP_URL_QUERY), $query);
            $q['max_id'] = $query['max_id'];
        }


        sleep(1);
    } while (!empty($q['max_id']) && count($json->statuses) > 1);
}
