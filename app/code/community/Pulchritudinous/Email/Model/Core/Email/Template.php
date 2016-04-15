<?php
/**
 * Core Email Template Override
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Puchtritudinous
 */
Class Pulchritudinous_Email_Model_Core_Email_Template
    extends Mage_Core_Model_Email_Template
{
    /**
     *
     *
     * @return Zend_Mail
     */
    public function getMail()
    {
        $config = Mage::helper('pulchemail/config');
        $helper = Mage::helper('pulchemail');
        $mail   = parent::getMail();

        if ($config->isEnabled()) {
            $mail::setDefaultTransport($helper->getActiveTransporter());
        }

        return $mail;
    }
}

