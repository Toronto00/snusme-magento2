<?php

/**
 * @author    Danail Kyosev <ddkyosev@gmail.com>
 * @copyright 2014, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
namespace Infomodus\Caship\Model\Src\Request;
abstract class AbstractRequest
{
    protected $xml;
    protected $currentRoot;

    private $siteId;
    private $password;
    private $messageTime;
    private $messageReference;

    public $response;

    protected $required;

    public function __construct($siteId, $password, $messageTime = null, $messageReference = null)
    {
        $this->siteId = $siteId;
        $this->password = $password;
        $this->messageTime = $messageTime;
        $this->messageReference = $messageReference;
    }

    /**
     * Build the root element, specific to each request
     * @return this
     */
    abstract protected function buildRoot();

    /**
     * Build the request type, used for requests that have a subtype
     * @return this
     */
    abstract protected function buildRequestType();

    private function buildAuthElement()
    {
        $messageTime = $this->messageTime != null ? $this->messageTime : new \DateTime('now');
        $messageReference = $this->messageReference != null ? $this->messageReference : $this->generateRandomString();

        $data = array('ServiceHeader' => array(
            'MessageTime' => $messageTime->format('c'),
            'MessageReference' => $messageReference,
            'SiteID' => $this->siteId,
            'Password' => $this->password
        ));

        $auth = $this->buildElement('Request', $data);
        $this->currentRoot->appendChild($auth);

        return $this;
    }

    public function __toString()
    {
        $this->buildRequest();

        return $this->xml->saveXML();
    }

    protected function buildElement($name, $data = null)
    {
        return \Infomodus\Caship\Model\Src\XMLSerializer::serialize($this->xml, $name, $data);
    }

    public function send($testing)
    {
        $this->buildRequest();

        $connection = new \Infomodus\Caship\Model\Src\Connection\DHLHttpConnection();
        /*print_r($this->xml->saveXML());*/
        if (0 == $testing) {
            $connection->setApiUrl(0);
        }

        $this->response = $this->xml->saveXML();
        $result = $connection->execute($this->response);

        return $result;
    }

    public function buildRequest()
    {
        $this->xml = new \DOMDocument('1.0', 'UTF-8');
        $this->xml->formatOutput = true;

        $this->buildRoot()
            ->buildRequestType();
        $this->buildAuthElement();
        foreach ($this->required as $key => $value) {
            if ($value) {
                if ($value instanceof \Infomodus\Caship\Model\Src\Request\Partials\RequestPartial) {
                    $element = $this->buildElement($key, $value->toArray());
                } else {
                    $element = $this->buildElement($key, $value);
                }

                $this->currentRoot->appendChild($element);
            }
        }

        return $this;
    }

    public function generateRandomString($length = 28)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}
