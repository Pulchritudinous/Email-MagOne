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
class Pulchritudinous_Email_Model_Transporter_Postmark
    extends Pulchritudinous_Email_Model_Transporter_Abstract
{
    /**
     *
     *
     * @return string
     */
    protected function _getUrl()
    {
        return 'https://api.postmarkapp.com/email';
    }

    /**
     *
     *
     * @return array
     */
    protected function _getExtraHeader()
    {
        $key = $this->getConfig()->getKey();

        return ["X-Postmark-Server-Token: {$key}"];
    }

    /**
     *
     *
     * @return string
     */
    protected function _getBody()
    {
        $body = [];

        foreach ($this->getRecipients() as $recipient) {
            $body[] = [
                'From'      => $this->getOrigModel()->getFromEmail(),
                'To'        => $recipient,
                'Subject'   => $this->getOrigModel()->getSubject(),
                'HtmlBody'  => $this->getOrigModel()->getBody()
            ];
        }

        return json_encode($body);
    }
}

