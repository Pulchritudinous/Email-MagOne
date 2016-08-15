<?php
/**
 *
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Anton Samuelsson <samuelsson.anton@gmail.com>
 */
class Pulchritudinous_Email_Adminhtml_PulchemailController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     *
     *
     * @return void
     */
    public function previewAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}

