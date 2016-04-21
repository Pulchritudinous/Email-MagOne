<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Pulchritudinous
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
 * Transporter source model
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Puchtritudinous
 */
Class Pulchritudinous_Email_Model_Source_Transporter
{
    /**
     * Helper
     *
     * @var Pulchritudinous_Email_Helper_Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('pulchemail');
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $transporters   = $this->toOptionHash();
        $options        = [];

        foreach ($transporters as $key => $val) {
            $options[] = [
                'label' => $val,
                'value' => $key,
            ];
        }

        return $options;
    }

    /**
     * Get option hash array
     *
     * @return array
     */
    public function toOptionHash()
    {
        return [
            'sparkpost'     => $this->_helper->__('Sparkpost'),
            'mandrill'      => $this->_helper->__('Mandrill'),
            'postmark'      => $this->_helper->__('Postmark'),
        ];
    }
}
