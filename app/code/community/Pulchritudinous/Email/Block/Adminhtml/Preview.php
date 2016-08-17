<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Pulchritudinous
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
?>
<?php
/**
 *
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Puchtritudinous
 */
class Pulchritudinous_Email_Block_Adminhtml_Preview
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Order object.
     *
     * @return Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * Get order.
     *
     * @return Mage_Sales_Model_Order
     */
    protected function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->getIncrementId());
        }

        return $this->_order;
    }

    /**
     * Get store ID.
     *
     * @return integer
     */
    protected function getStoreId()
    {
        return $this->getRequest()->getParam('store_id', 0);
    }

    /**
     * Get store object.
     *
     * @return Mage_Core_Model_Store
     */
    protected function getStore()
    {
        return Mage::app()->getStore($this->getRequest()->getParam('store_id', null));
    }

    /**
     * Get increment ID.
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->getRequest()->getParam('increment_id', false);
    }

    /**
     * Get increment ID.
     *
     * @return string
     */
    public function getEmailTemplateId()
    {
        return $this->getRequest()->getParam('template', 'order');
    }

    /**
     * Returns email content.
     *
     * @return string
     */
    public function getEmailHtml()
    {
        try {
            switch ($this->getEmailTemplateId()) {
                case 'order_comment':
                    $html = '';
                    break;
                case 'invoice':
                    $html = '';
                    break;
                case 'invoice_comment':
                    $html = '';
                    break;
                case 'shipment':
                    $html = '';
                    break;
                case 'shipment_comment':
                    $html = '';
                    break;
                case 'credit_memo':
                    $html = $this->getCreditMemoEmailHtml();
                    break;
                case 'credit_memo_comment':
                    $html = '';
                    break;
                case 'order':
                default:
                    $html = $this->getOrderEmailHtml();
                    break;
            }
        } catch (Exception $e) {
            $html = $e->getMessage();
        }

        return $html;
    }

    /**
     * Get order email HTML.
     *
     * @return string
     */
    public function getOrderEmailHtml()
    {
        $order          = $this->getOrder();
        $storeId        = $this->getStoreId();
        $emailTemplate  = Mage::getModel('core/email_template');

        $appEmulation           = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);

            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        if ($order->getCustomerIsGuest()) {
            $templateId     = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName   = $order->getBillingAddress()->getName();
        } else {
            $templateId     = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName   = $order->getCustomerName();
        }

        $variables = [
            'order'        => $order,
            'billing'      => $order->getBillingAddress(),
            'payment_html' => $paymentBlockHtml
        ];

        $emailTemplate->setDesignConfig(['area' => 'frontend', 'store' => $this->getStoreId()]);

        if (is_numeric($templateId)) {
            $emailTemplate->load($templateId);
        } else {
            $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
            $emailTemplate->loadDefault($templateId, $localeCode);
        }

        return $emailTemplate->getProcessedTemplate($variables);
    }

    /**
     * Get Credit Memo email HTML.
     *
     * @return string
     */
    public function getCreditMemoEmailHtml()
    {
        $order          = $this->getOrder();
        $storeId        = $this->getStoreId();
        $emailTemplate  = Mage::getModel('core/email_template');

        $appEmulation           = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        if (!$order->hasCreditmemos()) {
            Mage::throwException("The order does not have any Credit Memo");
        }

        try {
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);

            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        if ($order->getCustomerIsGuest()) {
            $templateXpath  = Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_GUEST_TEMPLATE;
            $templateId     = Mage::getStoreConfig($templateXpath, $storeId);
            $customerName   = $order->getBillingAddress()->getName();
        } else {
            $templateXpath  = Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_TEMPLATE;
            $templateId     = Mage::getStoreConfig($templateXpath, $storeId);
            $customerName   = $order->getCustomerName();
        }

        $comment    = 'Nunc ornare imperdiet aliquet. Nunc quam arcu, consectetur eu purus a, vehicula tempus quam.';
        $creditMemo = $order->getCreditmemosCollection()->getFirstItem();

        $variables = [
            'order'        => $order,
            'billing'      => $order->getBillingAddress(),
            'payment_html' => $paymentBlockHtml,
            'creditmemo'   => $creditMemo,
            'comment'      => $comment,
        ];

        $emailTemplate->setDesignConfig(['area' => 'frontend', 'store' => $this->getStoreId()]);

        if (is_numeric($templateId)) {
            $emailTemplate->load($templateId);
        } else {
            $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
            $emailTemplate->loadDefault($templateId, $localeCode);
        }

        return $emailTemplate->getProcessedTemplate($variables);
    }
}

