<?php
require_once(Mage::getBaseDir('lib') . DS . 'Mango' . DS . 'mango.php');

class Mango_Pay_Model_Standard extends Mage_Payment_Model_Method_Cc
{
    /**
     * Class properties
     */
    protected $_code          = 'mango';
    protected $_formBlockType = 'mango/standard_form';
    protected $_infoBlockType = 'payment/info';
    protected $_isGateway     = true;
    protected $_canCapture    = true;
    protected $_allowCurrencyCode = array('ARS');

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_code = Mage::helper('mango')->getMethodCode();
        $key         = Mage::helper('mango')->getSecretKey();
        $this->mango = new Mango\Mango(array("api_key" => Mage::helper('core')->decrypt($key)));
    }

    /**
     * Create and display credit card details form block
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('mango/standard_form', $name)
            ->setMethod('mango_standard')
            ->setPayment($this->getPayment())
            ->setTemplate('mango/pay/standard/form.phtml');
        return $block;
    }

    /**
     * Initialize payment process
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setStatus(self::STATUS_UNKNOWN);
        $stateObject->setIsNotified(false);
    }

    /**
     * Get payment configuration action
     */
    public function getConfigPaymentAction()
    {
        return $this->getConfigData('payment_action');
    }

    /**
     * Validate token is present
     */
    public function validate()
    {
        $params = Mage::app()->getRequest()->getParams();
        if (isset($params['payment']['mango_token']) && !empty($params['payment']['mango_token'])) {
            return $this;
        }
        return parent::validate();
    }

    /**
     * Capture funds creating a charge
     */
    public function capture(Varien_Object $payment, $amount)
    {	
        return $this->_charge($payment, $amount);
    }

    /**
     * Perform a charge over a ccard
     */
    protected function _charge($payment, $amount)
    {
        // Amount in cents
        $data = array('amount' => $amount * 100);

        $data = array_merge($data, $this->_preparePaymentData($payment));
        try {

          $charge = $this->mango->Charges->create($data);

          if($charge->paid == true) {

            $payment->setStatus(self::STATUS_SUCCESS)
              ->setTransactionId($charge->uid)
              ->setIsTransactionClosed(1);

          } else {

            $payment->setStatus(self::STATUS_ERROR)
              ->setIsTransactionPending(true)
              ->setTransactionId($charge->uid);
              $errorcode = $charge->failure_code;
              $errormsg = $this->_gethelper()->__('Error processing the request');
              mage::throwexception($errormsg);

          }

        } catch (Exception $e) {

          $payment->setStatus(self::STATUS_ERROR);
          Mage::logException($e);
          $errorcode = 'Bad request';
          $errormsg = $this->_gethelper()->__('Error processing the request');
          mage::throwexception($errormsg);

        }

        return $this;
    }

    /**
     * Merge data to prepare for charge
     */
    protected function _preparePaymentData($payment)
    {
        $params = Mage::app()->getRequest()->getParams();
        $token = $params['payment']['mango_token'];
        $email = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getEmail();
        return array(
            'email' => $email,
            'token' => $token,
            'description' => 'New charge for ' . $payment->getOrder()->getBillingAddress()->getName() . ' - Order #' . $payment->getOrder()->getId()
        );
    }

}
