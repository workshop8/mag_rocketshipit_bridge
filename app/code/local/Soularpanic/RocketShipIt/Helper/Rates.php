<?php 
class Soularpanic_RocketShipIt_Helper_Rates
extends Mage_Core_Helper_Abstract {

  public function getRSIRate($courier, $addrObj) {
    $helper = Mage::helper('rocketshipit/data');
    $rsiRate = new \RocketShipIt\Rate($courier);
    $addr = null;
    
    if ($addrObj instanceof Mage_Shipping_Model_Rate_Request) {
      $addr = $helper->_extractAddrFromMageShippingModelRateRequest($addrObj);
    }
    if ($addrObj instanceof Mage_Sales_Model_Order_Address) {
      $addr = $helper->_extractAddrFromMageSalesModelOrderAddress($addrObj);
    }

    $rsiRate->setParameter('toCode', $addr['zip']);
    $rsiRate->setParameter('toState', $addr['state']);
    $rsiRate->setParameter('toCountry', $addr['country']);
    $rsiRate->setParameter('weight', $addr['weight']);

    $rsiRate->setParameter('residentialAddressIndicator','0');

    return $rsiRate;
  }

  public function getSimpleRates($carrierCode,
				 $addrObj,
				 $useNegotiatedRate = false, 
				 $weight = null,
				 $handling = 0,
				 $codeMask = null,
				 $freeCodeMask = null) {
    $rsiRates = $this->getRSIRate($carrierCode, $addrObj);
    if ($weight != null) {
      $rsiRates->setParameter('weight', $weight);
    }

    $response = null;
    $result = Mage::getModel('shipping/rate_result');

    try {
      $response = $rsiRates->getSimpleRates();
    }
    catch (Exception $e) {
      $error = Mage::getModel('shipping/rate_result_error');
      $error->addData(array('error_message' => $e->getMessage()));
      $result->append($error);
      return $result;
    }

    Mage::log('Simple rate fetch raw results: '.print_r($response, true), null, 'rocketshipit_shipments.log');
    
    $errorMsg = $response['error'];
    if ($errorMsg != null) {
      $error = Mage::getModel('shipping/rate_result_error');
      $error->addData(array('error_message' => $errorMsg));
      $result->append($error);
      return $result;
    }

    $helper = Mage::helper('rocketshipit/data');    
    $fullCode = $helper->getFullCarrierCode($carrierCode);
    $carrierName = Mage::getStoreConfig('carriers/'.$fullCode.'/title');
    $rateKey = $useNegotiatedRate ? 'negotiated_rate' : 'rate';

    Mage::log('Code mask: '.print_r($codeMask, true), null, 'rocketshipit_shipments.log');

    foreach($response as $rsiMethod) {
      $serviceCode = $this->_getServiceCode($carrierCode, $rsiMethod);

      if ($codeMask && !in_array($serviceCode, $codeMask)) {
	continue;
      }

      if($useNegotiatedRate && $rsiMethod['negotiated_rate'] == null) {
	continue;
      }

      $method = Mage::getModel('shipping/rate_result_method');

      $method->setCarrier($fullCode);
      $method->setCarrierTitle($carrierName);

      $method->setMethod($serviceCode);
      $method->setMethodTitle($rsiMethod['desc']);

      $free = $addrObj->getFreeShipping() && (!$freeCodeMask || in_array($serviceCode, $freeCodeMask));
      Mage::log("Free? {$free}; Request: {$addrObj->getFreeShipping()}; free mask: ".print_r($freeCodeMask, true), null, 'rocketshipit_shipments.log');

      $method->setCost($free ? 0 : $rsiMethod[$rateKey]);
      $method->setPrice($free ? 0 : ($rsiMethod[$rateKey] + $handling));

      $result->append($method);
    }

    return $result;
    
  }

  function _getServiceCode($carrierCode, $rsiMethod) {
    $serviceCode = $rsiMethod['service_code'];
    if ($carrierCode === 'stamps') {
      $desc = $rsiMethod['desc'];
      $descArr = explode(' - ', $desc);
      $packageType = str_replace(' ', '-', $descArr[1]);
      $serviceCode.=':'.$packageType;
    }
    return $serviceCode;
  }
}
?>
