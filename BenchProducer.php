<?php

class BenchProducer implements ProducerInterface
{
    /**
     * Benchmark results
     *
     * @var array
     */
    protected $bench = [];

    /**
     * Statistics server host
     *
     * @var string
     */
    protected $host;

    /**
     * List of producers what should be tracked
     *
     * @var array
     */
    protected $producers = [];

    /**
     * BenchProducer constructor.
     *
     * @param $host
     * @param array $producers
     */
    public function __construct($host, array $producers)
    {
        $this->host = $host;
        $this->producers = $producers;
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
        if (empty($this->producers)) {
            return;
        }

        foreach ($this->producers as $producer) {
            $provider_time_start = microtime(true);
            ProducerFactory::createProducer($producer)->send($click);
            $provider_time_end = microtime(true);

            $this->bench[$producer] = ($provider_time_end - $provider_time_start);
        }

        $this->sendBenchResults();
    }

    /**
     *  Send bench results to statistics server
     */
    protected function sendBenchResults()
    {
        $ch = curl_init($this->host);

        $json = json_encode($this->bench, JSON_NUMERIC_CHECK);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json))
        );

        $response = curl_exec($ch);
        if($response === false) {
            writeLog('bench error', print_r(curl_error($ch), true));
        }

        curl_close($ch);

    }
}
