<?php

class ProducerFactory
{
    /**
     * Create producer according to
     * application config
     *
     * @param $producer
     *
     * @return ProducerInterface
     *
     * @throws \Exception
     */
    public static function createProducer($producer = null)
    {
        $producer = $producer ?: Config::get('producer', 'default', 'cassandra');

        switch ($producer) {
            case 'kafka':
                return self::createKafkaProducer();
            case 'rest':
                return self::createRESTProducer();
            case 'bench':
                return self::createBenchProducer();
            case 'cassandra':
                return self::createCassandraProducer();
            default:
                throw new \Exception('Invalid Producer');
        }
    }

    /**
     * @return CassandraProducer
     */
    public static function createCassandraProducer()
    {
        require __DIR__ . '/CassandraProducer.php';

        return new CassandraProducer();
    }

    /**
     * @return KafkaProducer
     */
    public static function createKafkaProducer()
    {
        require __DIR__ . '/KafkaProducer.php';
        $config = Config::get('producer', 'kafka');

        return new KafkaProducer($config);
    }

    /**
     * @return RESTProducer
     */
    public static function createRESTProducer()
    {
        require __DIR__ . '/RESTProducer.php';
        $config = Config::get('producer', 'rest');

        return new RESTProducer($config['host']);
    }

    /**
     * @return BenchProducer
     */
    public static function createBenchProducer()
    {
        require __DIR__ . '/BenchProducer.php';
        $config = Config::get('producer', 'bench');

        return new BenchProducer($config['host'], $config['providers']);
    }
}
