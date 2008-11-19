<?php
require_once("Correios.php");

$teste = new CorreiosPostal();

$teste->from = "01302010";
$teste->to = "13015315";
$teste->weight = 1;

$postalInfo[] = $teste->getTaxes();

print "<pre>";
print_r($postalInfo);
?>