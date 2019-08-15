<?php
namespace Infomodus\Caship\Model\Src\Connection;
class DHLHttpConnection
{
    private $api_url = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet';
    public function __construct()
    {
        if (! function_exists("curl_init")) {
            throw new \Infomodus\Caship\Model\Src\Exception\DHLConnectionException("Curl module is not available on this system");
        }
    }

    /**
     * @param string $data
     */
    public function execute($data)
    {
        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $exception = new \Infomodus\Caship\Model\Src\Exception\DHLConnectionException(curl_error($ch), curl_errno($ch));
            curl_close($ch);
            throw $exception;
        }

        curl_close($ch);

        return $response;
    }

    public function setApiUrl($production=1){
        if($production==0){
            $this->api_url = 'https://xmlpi-ea.dhl.com/XMLShippingServlet';
        }
    }
}
