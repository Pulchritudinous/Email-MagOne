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
 * Mandril transporter model for Zend Framework 1.
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Anton Samuelsson <samuelsson.anton@gmail.com>
 */
class Pulchritudinous_Email_Model_Transporter_Mandrill
    extends Pulchritudinous_Email_Model_Transporter_Abstract
{
    /**
     * Transporter code.
     *
     * @var string
     */
    protected $_code = 'mandrill';

    /**
     * API URL.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return 'https://mandrillapp.com/api/1.0/messages/send.json';
    }

    /**
     * Extra request headers to append to CURL.
     *
     * @return array
     */
    protected function _getExtraHeader()
    {
        return [];
    }

    /**
     * Parse all email recipients.
     *
     * @return array
     */
    public function _getRecipients()
    {
        $recipients = parent::_getRecipients();

        foreach ($this->_getCcRecipients() as $recipient) {
            if (!$recipient['email']) {
                continue;
            }

            $recipient['type'] = 'cc';
            $recipients[] = $recipient;
        }

        foreach ($this->_getBccRecipients() as $recipient) {
            if (!$recipient['email']) {
                continue;
            }

            $recipient['type'] = 'bcc';
            $recipients[] = $recipient;
        }

        return array_values($recipients);
    }

    /**
     * Checks if the email is based on HTML or text.
     *
     * @return string
     */
    protected function _getFormat()
    {
        return ($this->_mail->getBodyHtml()) ? 'html' : 'text';
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

        if ($response->hasData('code')) {
            Mage::throwException($response->getData('message'));
        }

        return true;
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
                'content'   => $attachment['content'],
                'type'      => $attachment['type'],
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
        $message = [
            'key'       => $this->getConfig()->getKey(),
            'message'   => [
                'subject'           => $this->_getSubject(),
                'from_name'         => $this->_getFrom()->getName(),
                'from_email'        => $this->_getFrom()->getEmail(),
                'to'                => $this->_getRecipients(),
                $this->_getFormat() => $this->_getBody()
            ],
        ];

        if ($replyTo = $this->_getRecipientString($this->_getReplyToRecipient())) {
            $message['message']['headers'] = ['Reply-To' => $replyTo];
        }

        if ($attachments = $this->_getAttachments()) {
            $message['message']['attachments'] = $attachments;
        }

        return json_encode($message);
    }
}

