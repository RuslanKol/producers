<?php

class RESTProducer implements ProducerInterface
{
    /**
     * REST API server host
     *
     * @var string
     */
    protected $host;

    /**
     * RESTProducer constructor.
     *
     * @param $host
     */
    public function __construct($host)
    {
        $this->host = $host;
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
        $json = json_encode($click, JSON_NUMERIC_CHECK);

        $ch = curl_init($this->host);

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
