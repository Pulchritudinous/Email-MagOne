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
class Pulchritudinous_Email_Helper_Config
{
    /**
     * Get configuraed transporter
     *
     * @param int $store
     *
     * @return string
     */
    public function getTransporter($store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore()->getId();
        }

        return Mage::getStoreConfig("pulchemail/general/transporter", $store);
    }

    /**
     * Get configuraed transporter
     *
     * @param string $transporter
     * @param int $store
     *
     * @return string
     */
    public function getTransporterSettings($transporter, $store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore()->getId();
        }

        $configKey  = "pulchemail/transporter_{$transporter}";
        $config     = Mage::getStoreConfig($configKey, $store);
        $config     = new Varien_Object($config);

        $apiKey = Mage::helper('core')->decrypt($config->getKey());
        $config->setKey($apiKey);

        Mage::dispatchEvent(
            'pulchemail_get_transporter_config',
            ['config' => $config]
        );

        return $config;
    }

    /**
     * Check if module is enabled
     *
     * @param int $store
     *
     * @return boolean
     */
    public function isEnabled($store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore()->getId();
        }

        return (bool)Mage::getStoreConfigFlag("pulchemail/general/enabled", $store);
    }

    /**
     * Get development settins.
     *
     * @return string
     */
    public function getDevelopmentSettings()
    {
        $config     = Mage::getStoreConfig('pulchemail/development');
        $config     = new Varien_Object($config);

        Mage::dispatchEvent(
            'pulchemail_get_development_config',
            ['config' => $config]
        );

        return $config;
    }
}

