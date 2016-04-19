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
 * Sparkpost transporter model for Zend Framework 1.
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Anton Samuelsson <samuelsson.anton@gmail.com>
 */
class Pulchritudinous_Email_Model_Transporter_Sparkpost
    extends Pulchritudinous_Email_Model_Transporter_Abstract
{
    /**
     * Transporter code.
     *
     * @var string
     */
    protected $_code = 'sparkpost';

    /**
     * API URL.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return 'https://api.sparkpost.com/api/v1/transmissions';
    }

    /**
     * Extra request headers to append to CURL.
     *
     * @return array
     */
    protected function _getExtraHeader()
    {
        $key = $this->getConfig()->getKey();

        return ["Authorization: {$key}"];
    }

    /**
     * Parse all email recipients.
     *
     * @return array
     */
    public function _getRecipients()
    {
        $recipients = parent::_getRecipients();

        foreach ($recipients as &$recipient) {
            $recipient = [
                'address' => $recipient
            ];
        }

        return array_values($recipients);
    }

    /**
     * Checks if the message is based on HTML or text.
     *
     * @return string
     */
    protected function _getFormat()
    {
        return ($this->_mail->getBodyHtml()) ? 'html' : 'text';
    }

    /**
     * Returns content of attachments.
     *
     * @return array
     */
    protected function _getAttachments()
    {
        $attachments = parent::_getAttachments();

        foreach ($attachments as &$attachment) {
            $attachment = [
                'name'      => $attachment['filename'],
                'data'      => $attachment['content'],
                'type'      => $attachment['type'],
            ];
        }

        return $attachments;
    }

    /**
     *
     *
     * @return array
     */
    protected function _getExtraMessageHeaders()
    {
        $extra = [];

        if ($cc = $this->_getCcRecipients()) {
            $extra['cc'] = $this->_getFlattenRecipients($cc);
        }

        if ($bcc = $this->_getBccRecipients()) {
            $extra['bcc'] = $this->_getFlattenRecipients($bcc);
        }

        return array_filter($extra);
    }

    /**
     * Messages string to send through CURL.
     *
     * @return string
     */
    protected function _getAssembledMessage()
    {
        $message = [
            'content' => [
                'from'  => [
                    'name'  => $this->_getFrom()->getName(),
                    'email' => $this->_getFrom()->getEmail(),
                ],
                'subject'           => $this->_getSubject(),
                $this->_getFormat() => $this->_getBody()
            ],
            'recipients'    => $this->_getRecipients()
        ];

        if ($attachments = $this->_getAttachments()) {
            $message['content']['attachments'] = $attachments;
        }

        if ($extra = $this->_getExtraMessageHeaders()) {
            $message['content']['headers'] = $extra;
        }

        return json_encode($message);
    }

    /**
     * Check the API response for any errors something unexpected.
     *
     * @param  string $response
     *
     * @throws Mage_Core_Exception
     *
     * @return boolean
     */
    protected function _processResponse($response)
    {
        if (!$this->_isJson($response)) {
            Mage::throwException('Unable to send email');
        }

        $response =  new Varien_Object(
            json_decode($response, true)
        );

        if ($response->hasData('errors')) {
            Mage::throwException($response->getData('errors/0/description'));
        }

        return true;
    }
}

