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
     * Sends a message.
     *
     * @return Pulchritudinous_Email_Model_Core_Email
     */
    public function send()
    {
        $config = Mage::helper('pulchemail/config');

        if (!$config->isEnabled()) {
            return parent::send();
        }

        $helper = Mage::helper('pulchemail');

        $mail = new Zend_Mail();
        $mail::setDefaultTransport($helper->getActiveTransporter());

        if (strtolower($this->getType()) == 'html') {
            $mail->setBodyHtml($this->getBody());
        } else {
            $mail->setBodyText($this->getBody());
        }

        $mail->setFrom($this->getFromEmail(), $this->getFromName())
            ->addTo($this->getToEmail(), $this->getToName())
            ->setSubject($this->getSubject());
        $mail->send();

        return $this;
    }

}
