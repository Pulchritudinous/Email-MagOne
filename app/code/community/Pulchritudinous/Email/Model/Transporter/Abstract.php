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
 * Abstract transporter model for Zend Framework 1.
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
     * Decodes headers.
     *
     * @var array
     */
    protected $_preparedHeaders = [];

    /**
     * Prepared attachments.
     *
     * @var array
     */
    protected $_preparedAttachments = [];

    /**
     * API URL.
     *
     * @return string
     */
    abstract protected function _getUrl();

    /**
     * Extra request headers to append to CURL.
     *
     * @return array
     */
    abstract protected function _getExtraHeader();

    /**
     * Messages string to send through CURL.
     *
     * @return string
     */
    abstract protected function _getAssembledMessage();

    /**
     * Check the API response for any errors something unexpected.
     *
     * @param  string $response
     *
     * @throws Mage_Core_Exception
     *
     * @return boolean
     */
    abstract protected function _processResponse($response);

    /**
     * Transporter code.
     *
     * @var string
     */
    protected $_code;

    /**
     * Initial configuration.
     */
    public function __construct()
    {
        $config         = Mage::helper('pulchemail/config');
        $transporter    = $config->getTransporter();
        $settings       = $config->getTransporterSettings($this->_code);

        $this->setConfig($settings);
    }

    /**
     * Set transporter configuration.
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
     * Get transporter configuration.
     *
     * @return Varien_Object
     */
    public function getConfig()
    {
        return $this->_settings;
    }

    /**
     * Get API Key.
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
     * Decode Zend headers.
     *
     * @param  array $headers
     *
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    protected function _prepareHeaders($headers)
    {
        foreach ($headers as $header => $content) {
            if (isset($content['append'])) {
                unset($content['append']);
            }

            array_walk(
                $content,
                'iconv_mime_decode'
            );

            $this->_prepareHeaders[strtolower($header)] = implode(
                ',',
                $content
            );
        }

        return $this;
    }

    /**
     * Prepare attachments.
     *
     * @param  array $parts
     *
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    protected function _prepareAttachments($parts)
    {
        foreach ($parts as $part) {
            $this->_preparedAttachments[] = [
                'type'      => $part->type,
                'filename'  => $part->filename,
                'content'   => $part->getContent(Zend_Mime::LINEEND),
                'raw'       => $part->getRawContent(),
            ];
        }

        return $this;
    }

    /**
     * Returns content of attachments.
     *
     * @return array
     */
    protected function _getAttachments()
    {
        return $this->_preparedAttachments;
    }

    /**
     * Parse the from address.
     *
     * @return Varien_Object
     */
    protected function _getFrom()
    {
        $from = (isset($this->_prepareHeaders['from'])) ? $this->_prepareHeaders['from'] : '';
        return new Varien_Object($this->_parseFlattenRecipient($from));
    }

    /**
     * Message subject.
     *
     * @return string
     */
    protected function _getSubject()
    {
        return (isset($this->_prepareHeaders['subject']))
            ? $this->_prepareHeaders['subject']
            : '';
    }

    /**
     * Parse all email recipients.
     *
     * If email hogging is enabled all emails will go to the specifed address.
     *
     * @return array
     */
    protected function _getRecipients()
    {
        $config = Mage::helper('pulchemail/config')
            ->getDevelopmentSettings();

        if ($config->getHogAllEmails()) {
            return [
                [
                    'email' => $config->getToEmail(),
                    'name'  => $config->getToName(),
                ]
            ];
        }

        $recipients = [];
        $to = (isset($this->_prepareHeaders['to'])) ? $this->_prepareHeaders['to'] : '';

        foreach (explode(',', $to) as $recipient) {
            $recipients[] =  $this->_parseFlattenRecipient($recipient);
        }

        return $recipients;
    }

    /**
     * Parse all cc email recipients.
     *
     * If email hogging is enabled no cc addresses will be added.
     *
     * @return array
     */
    protected function _getCcRecipients()
    {
        $config = Mage::helper('pulchemail/config')
            ->getDevelopmentSettings();

        if ($config->getHogAllEmails()) {
            return [[]];
        }

        $recipients = [];
        $to = (isset($this->_prepareHeaders['cc'])) ? $this->_prepareHeaders['cc'] : '';

        foreach (explode(',', $to) as $recipient) {
            $recipients[] =  $this->_parseFlattenRecipient($recipient);
        }

        return $recipients;
    }

    /**
     * Parse all bcc email recipients.
     *
     * If email hogging is enabled no bcc addresses will be added.
     *
     * @return array
     */
    protected function _getBccRecipients()
    {
        $config = Mage::helper('pulchemail/config')
            ->getDevelopmentSettings();

        if ($config->getHogAllEmails()) {
            return [[]];
        }

        $recipients = [];
        $to = (isset($this->_prepareHeaders['bcc'])) ? $this->_prepareHeaders['bcc'] : '';

        foreach (explode(',', $to) as $recipient) {
            $recipients[] =  $this->_parseFlattenRecipient($recipient);
        }

        return $recipients;
    }

    /**
     * Converts a recipient string into an array.
     *
     * @param  string $recipient
     *
     * @return array
     */
    protected function _parseFlattenRecipient($recipient)
    {
        if (preg_match('/(.*)<(.*)>/', $recipient, $matches)) {
            list(, $name, $email) = $matches;

            return [
                'name'  => iconv_mime_decode($name),
                'email' => $email,
            ];
        }

        return [
            'email' => $recipient,
        ];
    }

    /**
     * Flattens an array of recipients to a comma separated string.
     *
     * @param  array $recipients
     *
     * @return string
     */
    protected function _getFlattenRecipients(array $recipients)
    {
         foreach ($recipients as &$recipient) {
            $recipient = $this->_getRecipientString($recipient);
        }

        return implode(',', $recipients);
    }

    /**
     * Returns an recipient as a string.
     *
     * @param  string|array $email
     * @param  string       $name
     *
     * @return string
     */
    protected function _getRecipientString($email, $name = null)
    {
        if (is_array($email)) {
            $name   = (isset($email['name'])) ? $email['name'] : null;
            $email  = (isset($email['email'])) ? $email['email'] : '';
        }

        if ($name) {
            return $recipient = "{$name} <{$email}>";
        }

        return $email;
    }

    /**
     * Prepare email to be sent.
     *
     * @param  Zend_Mail $mail
     */
    public function send(Zend_Mail $mail)
    {
        $this->_mail    = $mail;
        $mime           = $mail->getMime();
        $message        = new Zend_Mime_Message();

        $this->_buildBody();

        $parts = $mail->getParts();

        $this->_prepareAttachments($parts);

        $message->setParts($this->_parts);
        $message->setMime($mime);

        $this->body = $message->generateMessage($this->EOL);

        $this->_prepareHeaders($mail->getHeaders());

        return $this->_processResponse($this->_sendMail());
    }

    /**
     * Parses the email body.
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
     * CURL headers.
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
     * Checks if the value is a valid JSON string.
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
     * Sending API call through CURL.
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

        return $curl->read();
    }
}

