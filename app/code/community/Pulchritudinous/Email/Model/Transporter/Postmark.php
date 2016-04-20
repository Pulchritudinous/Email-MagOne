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
 * Postmark transporter model for Zend Framework 1.
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Anton Samuelsson <samuelsson.anton@gmail.com>
 */
class Pulchritudinous_Email_Model_Transporter_Postmark
    extends Pulchritudinous_Email_Model_Transporter_Abstract
{
    /**
     * Transporter code.
     *
     * @var string
     */
    protected $_code = 'postmark';

    /**
     * API URL.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return 'https://api.postmarkapp.com/email';
    }

    /**
     * Extra request headers to append to CURL.
     *
     * @return array
     */
    protected function _getExtraHeader()
    {
        $key = $this->getConfig()->getKey();

        return ["X-Postmark-Server-Token: {$key}"];
    }

    /**
     * Checks if the message is based on HTML or text.
     *
     * @return string
     */
    protected function _getFormat()
    {
        return ($this->_mail->getBodyHtml()) ? 'HtmlBody' : 'TextBody';
    }

    /**
     * Parse all email recipients.
     *
     * @return array
     */
    protected function _getRecipients()
    {
        $recipients = parent::_getRecipients();

        return $this->_getFlattenRecipients($recipients);
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
        $recipients = parent::_getBccRecipients();

        return $this->_getFlattenRecipients($recipients);
    }

    /**
     * Parse all cc email recipients.
     *
     * If email hogging is enabled no bcc addresses will be added.
     *
     * @return array
     */
    protected function _getCcRecipients()
    {
        $recipients = parent::_getCcRecipients();

        return $this->_getFlattenRecipients($recipients);
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
                'Name'          => $attachment['filename'],
                'Content'       => $attachment['content'],
                'ContentType'   => $attachment['type'],
            ];
        }

        return $attachments;
    }

    /**
     * Messages string to send through CURL.
     *
     * @return string
     */
    protected function _getAssembledMessage()
    {
        $from = "{$this->_getFrom()->getName()} <{$this->_getFrom()->getEmail()}>";

        $message = [
            'From'              => $from,
            'To'                => $this->_getRecipients(),
            'Subject'           => $this->_getSubject(),
            $this->_getFormat() => $this->_getBody()
        ];

        if ($replyTo = $this->_getRecipientString($this->_getReplyToRecipient())) {
            $message['ReplyTo'] = $replyTo;
        }

        if ($bcc = $this->_getBccRecipients()) {
            $message['Bcc'] = $bcc;
        }

        if ($cc = $this->_getCcRecipients()) {
            $message['Cc'] = $cc;
        }

        if ($attachments = $this->_getAttachments()) {
            $message['Attachments'] = $attachments;
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

        if ($response->getData('ErrorCode') != '0') {
            Mage::throwException($response->getData('Message'));
        }

        return true;
    }
}

