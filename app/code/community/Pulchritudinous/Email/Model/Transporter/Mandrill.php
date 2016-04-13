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
class Pulchritudinous_Email_Model_Transporter_Mandrill
    extends Pulchritudinous_Email_Model_Transporter_Abstract
{
    /**
     *
     *
     * @return string
     */
    protected function _getUrl()
    {
        return 'https://mandrillapp.com/api/1.0/messages/send.json';
    }

    /**
     *
     *
     * @return array
     */
    protected function _getExtraHeader()
    {
        return [];
    }

    /**
     *
     *
     * @return string
     */
    protected function _getFormat()
    {
        return ($this->_isHtml) ? 'html' : 'text';
    }

    /**
     *
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

    public function setOrigModel()
    {
        return $this;
    }

    public function _sendMail()
    {
        $mail = $this->_mail;

        $test = Zend_Mime_Decode::splitMessage('To', $mail->getHeaders(), $null);

        $headers = $mail->getHeaders();

        $subject = $headers['Subject'][0];
        $charset = $mail->getCharset();

        $prefix          = '=?' . $charset . '?B?';
        $suffix          = '?=';

        $apa            = substr($subject, strlen($prefix), strlen($subject)-strlen($suffix));

        dahbug::dump(base64_decode($apa));

        dahbug::dump($mail->getRecipients());
        dahbug::dump($mail->getHeaders());
        dahbug::dump($this->body);
        dahbug::dump($this->header);
    }
}

