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
class Pulchritudinous_Email_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the active email transporter.
     *
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    public function getActiveTransporter()
    {
        $config         = Mage::helper('pulchemail/config');
        $transporter    = $config->getTransporter();
        $coreConfig     = Mage::getModel('core/config');

        switch ($transporter) {
            case "sparkpost":
                $model = Mage::getModel('pulchemail/transporter_sparkpost');
                break;
            case "postmark":
                $model = Mage::getModel('pulchemail/transporter_postmark');
                break;
            case "mandrill":
                $model = Mage::getModel('pulchemail/transporter_mandrill');
                break;
            default:
                Mage::throwException("No transporter model found");
                break;
        }

        return $model;
    }
}
