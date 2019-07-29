<?php

class CassandraProducer implements ProducerInterface
{
    /**
     * Query used to insert data to Cassandra db
     */
    const INSERT_QUERY = "INSERT INTO clicks (id, vnative_id, agent, created_at, ip, offer_id, publisher_id, advertiser_id, referer, target_url, url, p1, p2, p3, p4, country, bot, device, platform, browser, date, created_at_microtime, dead, source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) IF NOT EXISTS";

    /**
     * Query used to update counter in Cassandra
     */
    const UPDATE_COUNTER_QUERY = "UPDATE clicks_count SET count = count + 1 WHERE date = ? AND offer_id = ? AND publisher_id = ?";

    /**
     * Cassandra session
     *
     * @var \Cassandra\Session
     */
    protected $connection;

    /**
     * DirectCassandraProducer constructor.
     */
    public function __construct()
    {
        $this->connection = DB::getInstance();
    }

    /**
     * Process producer message
     *
     * @param Click $click
     *
     * @return void
     */
    public function send(Click $click)
    {
        $stmt = new \Cassandra\SimpleStatement(self::INSERT_QUERY);

        $arguments = [
            'id'                   => new \Cassandra\Uuid($click->id),
            'vnative_id'           => $click->vnative_id,
            'agent'                => $click->agent,
            'created_at'           => new \Cassandra\Timestamp($click->created_at),
            'ip'                   => new \Cassandra\Inet($click->ip),
            'offer_id'             => (int)$click->offer_id,
            'publisher_id'         => $click->publisher_id,
            'advertiser_id'        => $click->advertiser_id,
            'referer'              => $click->referer,
            'target_url'           => $click->target_url,
            'url'                  => $click->url,
            'p1'                   => $click->p1,
            'p2'                   => $click->p2,
            'p3'                   => $click->p3,
            'p4'                   => $click->p4,
            'country'              => $click->country,
            'bot'                  => (bool)$click->bot,
            'device'               => $click->device,
            'platform'             => $click->platform,
            'browser'              => $click->browser,
            'date'                 => \Cassandra\Date::fromDateTime($click->date),
            'created_at_microtime' => new \Cassandra\Decimal($click->created_at_microtime),
            'dead'                 => $click->dead,
            'source'               => $click->source,
        ];

        $result = $this->connection->execute($stmt, ['arguments' => $arguments]);

        if (!$result[0]['[applied]']) {
            writeLog('error', ['arguments' => $arguments, 'request' => $_REQUEST, 'server' => $_SERVER]);
        } else {
            $stmt = new \Cassandra\SimpleStatement(self::UPDATE_COUNTER_QUERY);

            $this->connection->execute($stmt, [
                'arguments' => [
                    'date'         =>  \Cassandra\Date::fromDateTime($click->date),
                    'offer_id'     => (int)$click->offer_id,
                    'publisher_id' => $click->publisher_id,
                ]]);
        }
    }
}