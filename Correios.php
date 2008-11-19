<?php
/**
 * Esta classe agrupa alguns serviços dos correios aos quais temos muita dificuldade ou curiosidade
 * para acesso, sempre que desenvolvemos uma aplicação nova que use estes recursos. 
 * Esta classe será incrementada com novos recursos e tecnologias sempre que possível.
 * 
 * @version 0.2a
 * @package webinsys.utils.brazil.correios
 * @author Kléderson Bueno <klederson@klederson.com>
 */

$teste = new CorreiosPostal();

$teste->from = "01302010";
$teste->to = "13015315";
$teste->weight = 1;

$postalInfo[] = $teste->getTaxes();

print "<pre>";
print_r($postalInfo);

class CorreiosPostal {
	
	const FRETE_PAC = 41106; //Normal shipping arround 7 to 18 days to delivery - Avaliable for all cities
	const FRETE_SEDEX = 40010; //Medium shipping arround 2-5 days - Avaliable for all cities
	const FRETE_SEDEX_10 = 40215; //Medium-Fast shipping arround 24 hours - Avalliable for most of big cities
	const FRETE_SEDEX_HOJE = 40290;//Faster shipping, delivered the same day - Have lots of limitations see www.correios.com.br for more information
	const FRETE_E_SEDEX = 81019;
	const FRETE_MALOTE = 44105;

	static $from, $to, $weight, $ensuranceValue;
	static $service = FRETE_SEDEX;
	static $returnAlert = 0;
	static $onlyOwner = 0;
	static $postalUrl = "http://www.correios.com.br/encomendas/precos/calculo.cfm";

	public function __construct() {
		
	}	
	
	/**
	 * This method is responsable for transmit all the requested configurations to the Correios and then returns
	 * it to the caller with a CorreiosPostalItem object
	 *
	 * @param String $responseType
	 * @return CorreiosPostalItem
	 */
	public function getTaxes($responseType = "xml") {
		
		//Parsing to REAL values just to ignore native language
		$correios = array();
		$correios['servico'] = $this->service;
		$correios['cepOrigem'] = $this->from;
		$correios['cepDestino'] = $this->to;
		$correios['peso'] = $this->weight;
		$correios['resposta'] = $responseType;
		$correios['MaoPropria'] = $this->onlyOwner;
		$correios['AvisoRecebimento'] = $this->returnAlert;
		$correios['valorDeclarado'] = $this->ensuranceValue;
		
		return $this->parseResponse($correios);
		
	}
	
	/**
	 * This method just resets the CorreiosPostal for a new clean consult
	 *
	 */
	public function reset() {
		$this->from = $this->to = $this->weight = $this->ensuranceValue = $this->postalData = null;
		$this->service = FRETE_SEDEX;
		$this->returnAlert = $this->onlyowner = 0;
		$this->postalUrl = "http://www.correios.com.br/encomendas/precos/calculo.cfm";
		$this->error = array();
	}
	
	/**
	 * This private method its main core for response parse, it organizes and request data information
	 * then creates a new CorreiosPostalItem object as response for it.
	 *
	 * @param array $correios
	 * @param String $postalUrl
	 * @param Boolean $translate
	 * @return CorreiosPostalItem
	 */
	private function parseResponse(array $correios) {
		//It serializes the array to a query string
		$serializedCorreios = http_build_query($correios);
		$xmlFile = utf8_encode(html_entity_decode(file_get_contents(self::$postalUrl . "?" . $serializedCorreios)));

		$content = new DOMDocument;
		$content->loadXML($xmlFile); //domxml_open_file($postalUrl . $serializedCorreios);
		
		//Parsing data
		$object = new CorreiosPostalItem($content);
		
		return $object;
	}
}

class CorreiosPostalItem {
	public $version, $service, $service_name, $from_state, $from_type, $from_postal, $to_state, $to_type, $to_postal, $weight, $toOwner, $receive_alert, $ensurance_value, $ensurance_tax, $postal_price;
	public $error = array();
	
	public function __construct(DOMDocument &$response) {
		$this->parseResponse($response);
	}
	
	/**
	 * It simple sets the object attributes with DOMDocument object response
	 *
	 * @param DOMDocument $content
	 */
	private function parseResponse(DOMDocument &$content) {
		$this->version = $content->getElementsByTagName('versao_arquivo')->item(0)->nodeValue;
		$this->service_name = $content->getElementsByTagName('servico_nome')->item(0)->nodeValue;
		$this->from_state = $content->getElementsByTagName('uf_origem')->item(0)->nodeValue;
		$this->from_type = $content->getElementsByTagName('local_origem')->item(0)->nodeValue;
		$this->from_postal = $content->getElementsByTagName('cep_origem')->item(0)->nodeValue;
		$this->to_state = $content->getElementsByTagName('uf_destino')->item(0)->nodeValue;
		$this->to_type = $content->getElementsByTagName('local_destino')->item(0)->nodeValue;
		$this->to_postal = $content->getElementsByTagName('cep_destino')->item(0)->nodeValue;
		$this->weight = $content->getElementsByTagName('peso')->item(0)->nodeValue;
		$this->toOwner = $content->getElementsByTagName('mao_propria')->item(0)->nodeValue;
		$this->receive_alert = $content->getElementsByTagName('aviso_recebimento')->item(0)->nodeValue;
		$this->ensurance_value = $content->getElementsByTagName('valor_declarado')->item(0)->nodeValue;
		$this->ensurance_tax = $content->getElementsByTagName('tarifa_valor_declarado')->item(0)->nodeValue;
		$this->postal_price = $content->getElementsByTagName('preco_postal')->item(0)->nodeValue;
		
		$this->error['code'] = $content->getElementsByTagName('codigo')->item(0)->nodeValue;
		$this->error['message'] = $content->getElementsByTagName('descricao')->item(0)->nodeValue;
	}
}
?>