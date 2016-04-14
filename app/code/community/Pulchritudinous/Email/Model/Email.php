<?php
/**
 * Transporter source model
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Puchtritudinous
 */
class Pulchritudinous_Email_Model_Email
    extends Varien_Object
{
    /**
     * Transporter Model
     *
     * @var Pulchritudinous_Email_Model_Transporter_Abstract
     */
    protected $_transporter;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $config         = Mage::helper('pulchemail/config');
        $transporter    = $config->getTransporter();
        $settings       = $config->getTransporterSettings($transporter);

        switch ($transporter) {
            case "sparkpost":
                $this->_transporter = Mage::getModel('pulchemail/transporter_sparkpost');
                break;
            case "postmark":
                $this->_transporter = Mage::getModel('pulchemail/transporter_postmark');
                break;
            case "mandrill":
                $this->_transporter = Mage::getModel('pulchemail/transporter_mandrill');
                break;
            default:
                Mage::throwException("No transporter model found");
                break;
        }

        $this->_transporter->setConfig($settings);
    }

    /**
     *
     *
     * @return Pulchritudinous_Email_Model_Transporter_Abstract
     */
    public function getTransporter()
    {
        return $this->_transporter;
    }
}

