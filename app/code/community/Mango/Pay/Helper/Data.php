<?php
class Mango_Pay_Helper_Data extends Mage_Core_Helper_Abstract
{
  protected $_methodCode = 'mango';

    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    public function getPublicKey()
    {
        if ($this->_isTestMode()) {
            return Mage::getStoreConfig('payment/mango/test_public_key');
        }
        return Mage::getStoreConfig('payment/mango/public_key');
    }

    public function getSecretKey()
    {
        if ($this->_isTestMode()) {
            return Mage::getStoreConfig('payment/mango/test_secret_key');
        }
        return Mage::getStoreConfig('payment/mango/secret_key');
    }

    protected function _isTestMode()
    {
        return (bool) Mage::getStoreConfig('payment/mango/test');
    }
}
