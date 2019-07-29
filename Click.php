<?php

class Click
{
    public $id;
    public $vnative_id;
    public $publisher_id;
    public $advertiser_id;
    public $offer_id;
    public $ip;
    public $referer;
    public $agent;
    public $created_at;
    public $target_url;
    public $url;
    public $p1;
    public $p2;
    public $p3;
    public $p4;
    public $country;
    public $bot;
    public $device;
    public $platform;
    public $browser;
    public $created_at_microtime;
    public $dead;
    public $date;
    public $source;

    /**
     * Save click record
     *
     * @param Tracking $tracking
     *
     * @return void
     */
    public static function save(Tracking $tracking)
    {
        $click = new Click();

        $click->id = $tracking->click_id;
        $click->publisher_id = $tracking->publisher_id;
        $click->advertiser_id = $tracking->advertiser_id;
        $click->offer_id = $tracking->offer_id;
        $click->vnative_id = $tracking->short_click_id;
        $click->ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        $click->referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null;
        $click->agent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : null;

        $click->target_url = $tracking->target_url;

        $click->created_at = time();

        $click->dead = $tracking->active !== null && !$tracking->active ? true : null;

        $click->url = !empty($_GET['url']) ? $_GET['url'] : null;
        $click->p1 = !empty($_GET['p1']) ? $_GET['p1'] : null;
        $click->p2 = !empty($_GET['p2']) ? $_GET['p2'] : null;
        $click->p3 = !empty($_GET['p3']) ? $_GET['p3'] : null;
        $click->p4 = !empty($_GET['p4']) ? $_GET['p4'] : null;

        //Fetch location from IP
        $click->country = $tracking->country;

        //Parse user info from UserAgent
        $results = parse_user_agent($click->agent);
        $click->bot = $results['bot'];
        $click->device = $results['device'];
        $click->platform = $results['platform'];
        $click->browser = $results['browser'];

        $click->created_at_microtime = microtime(true);

        $dtNow = new \DateTime();
        $dtNowStr = $dtNow->format('Y-m-d H:i:s');

        //FIXME: Save current dateTime (tz Asia/Kuala_Lumpur) as UTC to prevent date difference in cassandra
        $click->date = new DateTime($dtNowStr, new \DateTimeZone('+0'));
        $click->source = $tracking->source;

        ProducerFactory::createProducer()->send($click);
    }

}