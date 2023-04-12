<?php

namespace App\TaxDebt;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class TaxDebtRequest
{
    /**
     * TaxDebtRequest
     *
     * This class provides the ability to make requests to the service
     * for checking tax debt value of specific INN.
     * Delay for each request is 1200 ms.
     * TTL for each cached success response is 1 day.
     * Unsuccessful requests are not cached.
     *
     */

    /**
     * A debt request will be make for this INN
     * @access private
     * @var string
     */
    private $inn;

    /**
     * Debt request result
     * @access private
     * @var string
     */
    private $response;

    /**
     * Success request flag
     * @access private
     * @var bool
     */
    private $success;

    /**
     * Debt request error
     * @access private
     * @var string
     */
    private $error;

    /**
     * Fail count
     * @access private
     * @var string
     */
    private $failcount = 1;

    /**
     * Calls constructor. Fill INN value for request,
     * than make request to service
     * @access public
     * @param string $inn
     */
    public function __construct(string $inn)
    {
        $this->inn = preg_replace('/[^0-9]/', '', $inn);
        $this->request();
    }

    /**
     * Get cached response from Redis. If cached response not exists,
     * make TCP request and than store response to cache.
     * @access private
     * @return void
     */
    private function request()
    {
        $response = $this->getFromRedis();
        if ($response) {
            $this->responseSet($response);
        } else {
            $this->requestTCP();
        }
    }

    /**
     * Make request to service and fill response or error variable.
     * Also store success response to Redis cache.
     * @access private
     * @return void
     */
    private function requestTCP()
    {
        try {
            $ua = new Client([
                'headers' => ['Content-Type' => 'text/xml'],
                'body' => $this->reqBody()
            ]);
            $req = $ua->post($this->reqURL(), config('taxdebt.req_cfg'));
            $res = $req->getBody()->getContents();
            $this->storeToRedis($res);
            $this->responseSet($res);
        } catch (RequestException $e) {
            Log::error('TaxDebtRequest ' . $e->getMessage());
            //            if (preg_match("/Requests/i", $e->getMessage())) {
            //                sleep(++$this->failcount + 5);
            //                $this->requestTCP();
            //            }
            //            if($this->failcount > 3) exit(1);
            $this->errorSet($e->getMessage());
        }
    }

    /**
     * Setter for response body content value.
     * Also sets the request success flag to 1.
     * @access private
     * @param string $response response body
     * @return void
     */
    private function responseSet($response)
    {
        $this->success = 1;
        return $this->response = $response;
    }

    /**
     * Setter for error value.
     * Also sets the request success flag to 0.
     * @access private
     * @param string $error request error
     * @return void
     */
    private function errorSet($error)
    {
        $this->success = 0;
        $this->error = $error;
    }

    /**
     * This method store response for specific INN to Redis (TTL is 8 hour)
     * 3600 ×  8 = 28800
     * 3600 × 24 = 86400
     * @access private
     * @param string $response
     * @return void
     */
    private function storeToRedis($response)
    {
        Redis::set('taxdebt:' . $this->inn, $response, 'EX', config('taxdebt.redis_ttl_sec'));
    }

    /**
     * This method get cached response for specific INN from Redis
     * @access private
     * @return string
     */
    private function getFromRedis()
    {
        return Redis::get('taxdebt:' . $this->inn);
    }

    /**
     * XML body for request
     * @access private
     * @return array
     */
    private function reqBody()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
            <sign:request xmlns:sign="http://xmlns.kztc-cits/sign">
            <sign:iinBin>' . $this->inn . '</sign:iinBin></sign:request>';
    }

    /**
     * Tax debt service url.
     * @access private
     * @return string
     */
    private function reqURL()
    {
        return config('taxdebt.url') . '/?token=' . config('taxdebt.token');
    }

    /**
     * Getter for request success flag
     * If returns 1 the request is successful
     * If returns 0 the request isn't successful
     * @access public
     * @return bool
     */
    public function success()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function error()
    {
        return $this->error;
    }

    public function response()
    {
        return $this->response;
    }


    /**
     * USAGE:
     *
     * <code>
     * $req = new TaxDebtRequest($inn);
     * if(!$req->success())
     *     print $req->error();
     * if($req->success())
     *     print $req->response();
     * </code>
     */
}
