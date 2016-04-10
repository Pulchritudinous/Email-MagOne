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
    extends Varien_Object
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
     * @var array
     */
    protected $_recipients = [];

    /**
     *
     *
     * @var mixed
     */
    protected $_from;

    /**
     *
     *
     * @var boolean
     */
    protected $_isHtml = false;

    /**
     *
     *
     * @return string
     */
    abstract protected function _getUrl();

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
     *
     *
     * @param string $email
     * @param string $name
     *
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    public function addTo($email, $name)
    {
        $this->_recipients[$email] = $name;

        return $this;
    }

    /**
     *
     *
     * @param string $email
     * @param string $name
     *
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    public function setFrom($email, $name)
    {
        $this->_from = new Varien_Object(
            [
                'name'  => $name,
                'email' => $email,
            ]
        );
    }

    /**
     *
     *
     * @return Varien_Object
     */
    protected function _getFrom()
    {
        $from = $this->_from;

        if ($from instanceof Varien_Object) {
            return $from;
        }

        return new Varien_Object();
    }

    /**
     *
     *
     * @param string $value
     *
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    public function setBodyText($value)
    {
        $this->_isHtml = false;
        $this->setBody($value);

        return $this;
    }

    /**
     *
     *
     * @param string $value
     *
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    public function setBodyHTML($value)
    {
        $this->_isHtml = true;
        $this->setBody($value);

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

    public function getSubject()
    {
        return $this->getOrigModel()->getSubject();
    }

    /**
     *
     *
     * @return string
     */
    abstract protected function _getBody();

    /**
     *
     *
     * @return string
     */
    abstract protected function _getExtraHeader();

    /**
     *
     *
     * @return array
     */
    public function getRecipients()
    {
        return $this->_recipients;
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
     * @return mixed
     */
    public function send()
    {
        return $this->_sendEmail();
    }
}

