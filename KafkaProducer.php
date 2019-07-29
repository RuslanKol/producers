<?php

class KafkaProducer implements ProducerInterface
{
    /**
     * Cassandra session
     *
     * @var \Cassandra\Session
     */
    protected $connection;

    /**
     * @var \RdKafka\ProducerTopic
     */
    protected $topic;

    /**
     * KafkaProducer constructor.
     */
    public function __construct(array $config)
    {
        $rk = new RdKafka\Producer();
        $rk->setLogLevel($config['log_level']);
        $rk->addBrokers($config['host']);

        $conf = new \RdKafka\Conf();
        $conf->set('compression.codec', 'gzip');
        $conf->set('compression.level', 9);

        $this->topic = $rk->newTopic($config['topic']);
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
        $jsonClick = json_encode($click, JSON_NUMERIC_CHECK);

        $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $jsonClick);
    }
}