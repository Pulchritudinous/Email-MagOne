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

