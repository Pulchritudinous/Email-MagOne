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
                'key'   => $key,
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
