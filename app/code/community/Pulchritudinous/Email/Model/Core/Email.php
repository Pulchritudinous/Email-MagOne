<?php
/**
 * Core Email Override
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Puchtritudinous
 */
Class Pulchritudinous_Email_Model_Core_Email
    extends Mage_Core_Model_Email
{
    /**
     * Send Email
     *
     * @return Pulchritudinous_Email_Model_Core_Email
     */
    public function send()
    {
        $config = Mage::helper('pulchemail/config');

        if (!$config->isEnabled()) {
            return parent::send();
        }

        $email = Mage::getModel('pulchemail/email');
    }
}
