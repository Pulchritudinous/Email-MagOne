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
     * @var array
     */
    protected $_preparedHeaders = [];

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
     * @param array $headers
     */
    protected function _prepareHeaders($headers)
    {
        foreach ($headers as $header => $content) {
            if (isset($content['append'])) {
                unset($content['append']);
            }

            array_walk(
                $content,
                [$this, '_decodeBase64MimeCallback']
            );

            $this->_prepareHeaders[strtolower($header)] = implode(
                ',',
                $content
            );
        }

        dahbug::dump($this->_prepareHeaders);

        return $this;
    }

    /**
     *
     *
     * @return Varien_Object
     */
    protected function _getFrom()
    {
        preg_match('/(.*)<(.*)>/', $this->_prepareHeaders['from'], $matches);

        list(, $name, $email) = $matches;

        return new Varien_Object([
            'name'      => $name,
            'email'     => $email,
        ]);
    }

    /**
     *
     *
     * @return string
     */
    protected function _getSubject()
    {
        return $this->_prepareHeaders['subject'];
    }

    /**
     *
     *
     * @return array
     */
    protected function _getRecipients()
    {
        $recipients = [];

        foreach (explode(',', $this->_prepareHeaders['to']) as $recipient) {
            preg_match('/(.*)<(.*)>/', $recipient, $matches);
            list(, $name, $email) = $matches;

            $recipients[] = [
                'email' => $email,
                'name'  => $name
            ];
        }

        return $recipients;
    }

    /**
     *
     *
     * @param  Zend_Mail $mail
     */
    public function send(Zend_Mail $mail)
    {
        $this->_mail    = $mail;
        $mime           = $mail->getMime();
        $message        = new Zend_Mime_Message();

        $this->_buildBody();

        $message->setParts($this->_parts);
        $message->setMime($mime);

        $this->body = $message->generateMessage($this->EOL);

        $this->_prepareHeaders($mail->getHeaders());

        return $this->_processResponse($this->_sendMail());
    }

    /**
     *
     *
     * @return string
     */
    protected function _getBody()
    {
        $text = ($this->_mail->getBodyText())
            ? ($this->_mail->getBodyText(true))
            : ($this->_mail->getBodyHtml(true));

        return Zend_Mime_Decode::decodeQuotedPrintable($text);
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

    /**
     *
     *
     * @param string $string
     */
    protected function _decodeBase64MimeCallback(&$string)
    {
        $string = $this->_decodeBase64Mime($string);
    }

    /**
     *
     *
     * @param  string $string
     *
     * @return $string
     */
    protected function _decodeBase64Mime($string)
    {
        if (preg_match('/[a-zA-Z0-9+\/]+={0,2}/', $string)) {
            $string = preg_replace_callback(
                '/([\w\d]+=+)/',
                [$this, '_base64DecodeCallback'],
                preg_replace('/(=\?[\w\d-]+\?B\?([\w\d]+=+)\?=)/', '\2', $string)
            );
        }

        return $string;
    }

    /**
     *
     *
     * @param  array $data
     *
     * @return string
     */
    protected function _base64DecodeCallback(array $data)
    {
        return base64_decode($data[0]);
    }

    /**
     *
     *
     * @return string
     */
    public function _sendMail()
    {
        $curl       = new Varien_Http_Adapter_Curl();
        $httpVer    = Zend_Http_Client::HTTP_1;

        $curl->setConfig(['header' => false]);

        $curl->write(
            Zend_Http_Client::POST,
            $this->_getUrl(),
            $httpVer,
            $this->_getHeader(),
            $this->_getAssembledMessage()
        );

        return dahbug::dump($curl->read());
    }
}

