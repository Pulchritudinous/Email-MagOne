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
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array())
    {
        $config = Mage::helper('pulchemail/config');

        if (!$config->isEnabled()) {
            parent::send($email, $name, $variables);
            return;
        }

        $email = Mage::getModel('pulchemail/email');
    }
}
