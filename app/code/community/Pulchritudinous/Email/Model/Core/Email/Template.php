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
     * @var string
     */
    protected $_processedSubject = '';

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

    /**
     *
     *
     * @return array
     */
    public function getProcessedTemplateSubject(array $variables)
    {
        $result = parent::getProcessedTemplateSubject($variables);

        $this->_processedSubject = $result;
        return $result;
    }

    /**
     *
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->_processedSubject;
    }
}
