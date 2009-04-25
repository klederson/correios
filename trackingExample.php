<?php
/**
 * Tracking example
 * 
 * This example belongs to main repository of Add4 Comunicação - Correios System and its a part of a full package called Correios
 * The package Correios is also distributed under GNU v2 Licence.
 * 
 * @copyright Add4 Comunicação ( www.add4.com.br )
 * @author Kléderson Bueno
 * @package Correios
 */

require_once("Rastreio.php");

$tracking = new CorreiosTracking();

$myItem = $tracking->trackItem('S0456586463BR');

print "<pre>";
print_r($myItem);
print "</pre>";
?>