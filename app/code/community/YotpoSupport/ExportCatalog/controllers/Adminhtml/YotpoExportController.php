<?php

class YotpoSupport_ExportCatalog_Adminhtml_YotpoExportController extends Mage_Adminhtml_Controller_Action {

    public function getAttr($_product, $attr_keys){
        $attr_values = array();

        foreach ($attr_keys as $attr => $attr_key)
        {
            if (empty($attr_key)){ $attr_values[$attr] = ''; }
            else{ $attr_values[$attr] = $_product->getData($attr_key); }
        }

        return $attr_values;
    }

    public function exportCatalogAction() {
        try{
            require_once 'app/Mage.php';
            Mage::app(Mage_Core_Model_App::ADMIN_STORE_ID);

            $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; //read the url
            $parts = parse_url($url); //parse the url
            parse_str($parts['query'], $query); //query = params in url
            $store_code = $query['store'];

            foreach (Mage::app()->getStores() as $store) { //get the relevant store object
                if ($store->getCode() == $store_code) {
                    $current_store = $store;
                    break;
                }
            }

            $store_id = $current_store->getId();
            $attr_keys = array();
            $attr_keys["upc"]=Mage::getStoreConfig('exportcatalog/yotposupport_general_group/yotpo_upc', $current_store);
            $attr_keys["isbn"]=Mage::getStoreConfig('exportcatalog/yotposupport_general_group/yotpo_isbn', $current_store);
            $attr_keys["mpn"]=Mage::getStoreConfig('exportcatalog/yotposupport_general_group/yotpo_mpn', $current_store);
            $attr_keys["brand"]=Mage::getStoreConfig('exportcatalog/yotposupport_general_group/yotpo_brand', $current_store);

            $saveData = array();
            ob_start();
            header('Content-type: application/utf-8');
            header('Content-disposition: attachment; filename="Yotpo Catalog Export.csv"');
            $fp = fopen('php://output', 'w'); 
            
            $products = Mage::getModel('catalog/product')->getCollection(); //get all products
            $products->addStoreFilter($store_id); //filter all the products that are not relevant to the chosen store
            $currency = $current_store->getCurrentCurrencyCode();
            
            fputcsv($fp, ['Product ID', 'Product Name', 'Product Description', 'Product URL', 'Product Image URL',
            'Product Price', 'Currency', 'Spec UPC', 'Spec SKU', 'Spec ISBN', 'Spec Brand', 'Spec MPN', 'Blacklisted', 'Product Group']); //print headers to the file
            foreach ($products as $product) {
                $_product = Mage::getModel('catalog/product')->load($product->getId());

                $configurable_product_model = Mage::getModel('catalog/product_type_configurable'); //handle configurable products
                $parentIds = $configurable_product_model->getParentIdsByChild($_product->getId());
                if (count($parentIds) > 0) {
                    $_product = Mage::getModel('catalog/product')->load($parentIds[0]); //take the first one
                }
    
                $saveData['Product ID'] = $_product->getId();
                $saveData['Product Name'] = $_product->getName();
                $saveData['Product Description'] = $_product->getDescription();
                $saveData['Product URL'] = Mage::getBaseUrl().$_product->getUrlPath();
            
                if ($_product->getImage() != NULL && $_product->getImage() != 'no_selection'){ //Pull Image URL only in case there's a pic associated with the product
                    $saveData['Product Image URL'] = Mage::getModel('catalog/product_media_config')->getMediaUrl( $_product->getImage());
                }
                else { //If there's no image associated with the product, keep the column empty
                    $saveData['Product Image URL'] = '';
                }
            
                $attr_values = $this->getAttr($_product, $attr_keys);
                $saveData['Product Price'] = $_product->getPrice();
                $saveData['Currency'] = $currency;
                $saveData['Spec UPC'] = $attr_values['upc'];
                $saveData['Spec SKU'] = $_product->getData('sku');
                $saveData['Spec ISBN'] = $attr_values['isbn'];
                $saveData['Spec Brand'] = $attr_values['brand'];
                $saveData['Spec MPN'] = $attr_values['mpn'];
                $saveData['Blacklisted'] = 'false';
                $saveData['Product Group'] = '';
                fputcsv($fp, $saveData);
            }
            fclose($fp);
        } catch(Exception $e) {
            Mage::log('Failed to export product catalog. Error: '.$e);
        }
    }
}