<?php
/**
 * Taxes Example
 * 
 * This example belongs to main repository of Add4 Comunicação - Correios System and its a part of a full package called Correios
 * The package Correios is also distributed under GNU v2 Licence.
 * 
 * @copyright Add4 Comunicação ( www.add4.com.br )
 * @author Kléderson Bueno
 * @package Correios
 */

require_once("Correios.php");

$teste = new CorreiosPostal();

$teste->from = "01302010";
$teste->to = "13015315";
$teste->weight = 1;
$teste->service = CorreiosPostal::FRETE_SEDEX;

$postalInfo[] = $teste->getTaxes();

print "<pre>";
print_r($postalInfo);
?>