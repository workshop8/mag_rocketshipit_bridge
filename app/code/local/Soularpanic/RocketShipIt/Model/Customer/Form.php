<?php 
class Soularpanic_RocketShipIt_Model_Customer_Form
extends Mage_Customer_Model_Form {
  public function validateData(array &$data) {
    //$dataHelper = Mage::helper('rocketshipit/data');
    
    $validationCarrierCode = Mage::getStoreConfig('carriers/rocketshipit_global/validation_carrier');
    $errors = array();
    if ($validationCarrierCode !== null && $validationCarrierCode !== 'none') {
      $helper = Mage::helper('rocketshipit/validation_'.$validationCarrierCode);
      //$validate = new \RocketShipIt\AddressValidate('stamps');
      // $validate = new \RocketShipIt\AddressValidate('ups');

      // $validate->setParameter('toAddr1', $data['street'][0]);
      // $validate->setParameter('toAddr2', $data['street'][1]);
      // $validate->setParameter('toCity', $data['city']);
      // $validate->setParameter('toCode', $data['postcode']);
      

      //$validate = $helper->getRsiValidate($data);
      
      $errors = $helper->validate($data);
    }



    //$response = $validate->validate();
    //$response = $validate->validateStreetLevel(); //ups
    //$dataHelper->log("Validation:\n".print_r($response, true));

    //$errors = array();
    // if ($response->CityStateZipOK === false) { //stamps
    //   $errors[] = "This shit is all fucked.  I have no idea what you were even trying to do here.";
    // }

    // if ($response->AddressMatch === true) { // stamps
    //   $verifiedAddress = $response->Address;
    //   $data['street'][0] = $verifiedAddress->Address1;
    //   $data['street'][1] = $verifiedAddress->Address2;
    //   $data['city'] = $verifiedAddress->City;
    //   $data['postcode'] = $verifiedAddress->ZIPCode;
    // }
    // else {
    //   $errors[] = "Unable to verify your street address.  Please check it and try again.";
    // }

    // $addrResp = $response['AddressValidationResponse'];
    // if ($addrResp['Response']['ResponseStatusCode'] === '1') { 
    //   // ups
    //   // or maybe $addrResp['ResponseStatusDescription' === 'Success'
    //   $verifiedAddress = $addrResp['AddressKeyFormat'];
    //   if ($verifiedAddress === null) { 
    // 	// this seems to happen when the street addr is botched
    // 	errors[] = "Unable to verify your street address.  Please check it and try again.";
    //   }
    //   else {
    // 	$data['street'][0] = $verifiedAddress['AddressLine'];
    // 	//$data['street'][1] = $verifiedAddress->Address2;
    // 	$data['city'] = $verifiedAddress['PoliticalDivision2'];
    // 	$verifiedStateCode = $verifiedAddress['PoliticalDivision1'];
    // 	if ($verifiedStateCode !== $stateCode) {
    // 	  $correctedRegion = Mage::getModel('directory/region')
    // 			 ->load($verifiedStateCode, 'code');
    // 	  $data['region_id'] = $correctedRegion->getId();
    // 	}
    // 	$data['postcode'] = $verifiedAddress['PostcodePrimaryLow'];
    //   }
    // }
    // else {
    //   $errors[] = $addrResp['Response']['Error']['ErrorDescription'];
    // }

    $parentValidation =  parent::validateData($data);
    if ($parentValidation !== true) {
      $errors = array_merge($errors, $parentValidaton);
    }

    if (empty($errors)) {
      return true;
    }

    return $errors;
  }
}
?>