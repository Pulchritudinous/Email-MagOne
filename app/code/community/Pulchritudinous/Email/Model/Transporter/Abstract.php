<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Anton Samuelsson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
?>
<?php
/**
 *
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Anton Samuelsson <samuelsson.anton@gmail.com>
 */
abstract class Pulchritudinous_Email_Model_Transporter_Abstract
    extends Zend_Mail_Transport_Abstract
{
    /**
     * Transporter settings
     *
     * @var mixed
     */
    protected $_settings;

    /**
     *
     *
     * @return string
     */
    abstract protected function _getUrl();

     /**
     *
     *
     * @return array
     */
    abstract protected function _getExtraHeader();

     /**
     *
     *
     * @param  string $response
     *
     * @throws Mage_Core_Exception
     *
     * @return boolean
     */
    abstract protected function _processResponse($response);

    /**
     * Set transporter configuration
     *
     * @param Varien_Object $config
     *
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    public function setConfig($config)
    {
        $this->_settings = $config;

        return $this;
    }

    /**
     * Get transporter configuration
     *
     * @return Varien_Object
     */
    public function getConfig()
    {
        return $this->_settings;
    }

    /**
     * Get API Key
     *
     * @return string|null
     */
    protected function _getKey()
    {
        if (!$this->getConfig()) {
            return null;
        }

        return $this->getConfig()->getKey();
    }

    /**
     *
     *
     * @return array
     */
    protected function _getHeader()
    {
        return array_merge(
            $this->_getExtraHeader(),
            [
                'Accept: application/json',
                'Content-Type: application/json',
            ]
        );
    }

    /**
     *
     *
     * @return mixed
     */
    protected function _sendEmail()
    {
        $curl       = new Varien_Http_Adapter_Curl();
        $httpVer    = Zend_Http_Client::HTTP_1;

        $curl->setConfig(['header' => false] );

        $curl->write(
            Zend_Http_Client::POST,
            $this->_getUrl(),
            $httpVer,
            $this->_getHeader(),
            $this->_getBody()
        );

        $response = $curl->read();

        return $response;
    }

    /**
     *
     *
     * @param  string $string
     *
     * @return boolean
     */
    protected function _isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

