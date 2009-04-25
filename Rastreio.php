<?php

class CorreiosTracking {
	const DATA_EMPTY = 0;
	const INFO_EMPTY = "No info";
	const MESSAGE_EMPTY = "Our system cannot find any information about this package. If it has been recently posted is normal it not appear so soon, please try again latter,, otherwise check if the given code are correctly typed";
	
	//parms: ?P_LINGUA=001&P_TIPO=001&P_COD_UNI=
	static public $searchUrl = "http://websro.correios.com.br/sro_bin/txect01$.QueryList";
	
	/**
	 * Track an object 
	 *
	 * @param String $identify
	 */
	public function trackItem($identify) {
		$object = new CorreiosTrackingItem($identify);
		
		$this->_getItemHistory($object);
		
		return $object;
	}
	
	/**
	 * It creates a new CorreiosTrackingItem object based on the identify parm (DEPRECATED)
	 *
	 * @param String $identify
	 * @param Integer $language
	 * @param Integer $type
	 * @return CorreiosTrackingItem
	 */
	public function newObject($identify, $language = 001, $type = 001) {
		return new CorreiosTrackingItem($identify, $language, $type);
	}
	
	/**
	 * It retreives the history of a range of items
	 * TODO Use a less band usage method as for example parse based on the multiple resource
	 * @param array $objects
	 */
	public function getFromArray(array &$objects) {
		foreach($objects as $index => $item) {
			$this->_getItemHistory($item);
		}
	}
	
	/**
	 * PROTECTED AND PRIVATE FUNCTIONS
	 */
	
	/**
	 * It gets the tracking history of a object at Correios
	 *
	 * @param CorreiosTrackingItem $object
	 */
	final private function _getItemHistory(CorreiosTrackingItem &$object) {
		$sentVars['P_LINGUA'] = $object->language;
		$sentVars['P_TIPO'] = $object->type;
		$sentVars['P_COD_UNI'] = $object->identify;
		
		//Creating the URL
		$url = self::$searchUrl . "?" . http_build_query($sentVars);
	
		$ch = curl_init();
		$timeout = 0; //zero for no timeout
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);
		$finalFile = array();
		$finalFile = explode("\n", $file_contents);
		
		$object->tracking = $this->parseContent($finalFile);	

	}
	
	protected function _parseDOMContent($html) {
		//Searching using MAIN STRUCTURE OF THE TABLE
		
		$tableOfContents = $html->find('table');
		
		foreach($tableOfContents->children() as $index => $tr) {
			if($index > 0) {
				foreach($tr->find('td') as $trIndex => $td) {
					print $td->innertext;
				}
			}
		}
	}
	
	/**
	 * This function parses the retreived content DEPRECATED
	 *
	 * @param String $content
	 * @return Array
	 */
	protected function parseContent($content) {
		
		$items = Array();
		/*
		 * It starts the replacement of the retreived data
		 * If there any changes in the retreived HTML it have to be changed
		 * TODO Alwasy check if the HTML corresponds to this patterns
		 */
		foreach ($content as $num => $line) {
			if (substr($line, 0, 7) == '<tr><td') {
				if (preg_match('/<td rowspan=[0-9]>.+?<\/td>/', $line, $match))
					$items[$num]['data'] = utf8_encode(strip_tags($match[0]));
				if (preg_match('/<td colspan=[0-9]>.+?<\/td>/', $line, $match))
					$items[$num-1]['to'] = utf8_encode(strip_tags($match[0]));
				if (preg_match('/<td>.+?<\/td>/', $line, $match))
					$items[$num]['message'] = utf8_encode(strip_tags($match[0]));
				if (preg_match('/<FONT.*>.+?<\/font>/', $line, $match))
					$items[$num]['info'] = utf8_encode(strip_tags($match[0]));
			}
		}
		
		if (!$items) {
			$items[0]['data'] =  self::DATA_EMPTY;
			$items[0]['infos'] = self::INFO_EMPTY;
			$items[0]['message'] = self::MESSAGE_EMPTY;
		}
		
		return $items;
	}
		
}

class CorreiosTrackingItem {
	//By default its Brazilian Portuguese but you can use 002 as english but the support isn't full
	public $language = "001";
	
	//CONSTANT DO NOT CHANGE IT
	public $type = "001";
	
	//Your track number
	public $identify;
	
	public $tracking = Array();
	
	final public function __construct($identify, $language = "001", $type = "001") {
		$this->identify = $identify;
		$this->language = $language;
		$this->type = $type;
	}
}