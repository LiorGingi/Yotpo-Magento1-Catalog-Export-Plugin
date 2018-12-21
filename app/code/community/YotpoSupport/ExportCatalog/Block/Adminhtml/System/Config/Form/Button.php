<?php

class YotpoSupport_ExportCatalog_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('yotpoexport/system/config/button.phtml');
    }
 
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
 
    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxExportUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_yotpoexport/exportCatalog');
        
    }
 
    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'yotposupport_button',
            'style'        => 'margin-top:5px;',
            'label'     => $this->helper('adminhtml')->__('Download Product Catalog'),
            'onclick'   => 'javascript:exportCatalog(); return false;'
        ));
 
        return $button->toHtml();
    }
}
