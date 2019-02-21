<?php
class TweetParser extends \koulab\UltimateTwitter\Parser {

    public static function parseLegacyTimeline($html) : array {
        $dom = new Symfony\Component\DomCrawler\Crawler();
        $dom->addHtmlContent($html,'UTF-8');
        $tweets = [];
        $dom->filter('li.stream-item')->each(function (\Symfony\Component\DomCrawler\Crawler $node, $i) use(&$tweets) {
            try{
                $id = $node->attr('data-item-id');

            }catch (InvalidArgumentException $e){ }
            $tweets[] =  $id;
        });
        return $tweets;
    }
}