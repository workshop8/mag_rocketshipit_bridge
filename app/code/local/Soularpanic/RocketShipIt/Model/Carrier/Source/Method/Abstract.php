<?php
abstract class Soularpanic_RocketShipIt_Model_Carrier_Source_Method_Abstract 
extends Soularpanic_RocketShipIt_Model_Carrier_Source_Abstract {
  
  public function getSourceArray($model) {
    return $model->getMethods();
  }
}

