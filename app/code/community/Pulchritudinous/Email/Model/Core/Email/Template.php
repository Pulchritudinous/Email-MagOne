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
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    public function getMail()
    {
        $mail = parent::getMail();
        $mail::setDefaultTransport(new Pulchritudinous_Email_Model_Transporter_Mandrill);
        return $mail;
    }
}
