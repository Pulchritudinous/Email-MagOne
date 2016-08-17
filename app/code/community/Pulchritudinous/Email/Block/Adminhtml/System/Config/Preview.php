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
 * Order email preview button renderer.
 *
 * @package Pulchritudinous_Email
 * @module  Pulchritudinous
 * @author  Anton Samuelsson <samuelsson.anton@gmail.com>
 */
class Pulchritudinous_Email_Block_Adminhtml_System_Config_Preview
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template to itself
     *
     * @return Pulchritudinous_Email_Block_Adminhtml_System_Config_Preview
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->getTemplate()) {
            $this->setTemplate('pulchritudinous/email/system/config/preview.phtml');
        }

        return $this;
    }

    /**
     * Unset some non-related element parameters.
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents.
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();

        $this->addData([
            'button_label'  => Mage::helper('pulchemail')->__($originalData['button_label']),
            'html_id'       => $element->getHtmlId(),
            'ajax_url'      => Mage::getSingleton('adminhtml/url')->getUrl('*/customer_system_config_validatevat/validate')
        ]);

        return $this->_toHtml();
    }

    /**
     * Get current store.
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) {
            return Mage::app()->getStore($code);
        } elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) {
            return Mage::app()->getWebsite($code)->getId()->getDefaultStore();
        }

        return Mage::app()->getStore();
    }
}

