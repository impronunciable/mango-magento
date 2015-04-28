<?php
class Mango_Pay_Block_Standard_Form extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mango/pay/standard/form.phtml');
    }
}
