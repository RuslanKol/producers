<?php

interface ProducerInterface
{
    /**
     * Process producer message
     *
     * @param Click $click
     *
     * @return mixed
     */
    public function send(Click $click);
}