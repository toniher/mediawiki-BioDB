<?php
class ApiBioDB extends ApiBase {
	
	public function execute() {

		$params = $this->extractRequestParams();
		
		$param = null;
		$data = null;

		if ( array_key_exists( "param", $params ) ) {
			$param = $params["param"];
		}

		if ( array_key_exists( "query", $params ) ) {
			// Query new function in BioDB
			$output = BioDB::returnBioDB( $params["query"], $param );

		}
		
		if ( array_key_exists( $query, $output ) ) {
			
			$data = $output[$query];
		}
		
		$paramq = array();
		
		if ( $param ) {
			$paramq = explode( ",", $param );
		}

		$this->getResult()->addValue( null, $this->getModuleName(), array ( 'status' => "OK", 'query' => $query, 'param' => $paramq, 'biodata' => $data ) );

		return true;

	}
	
	public function getAllowedParams() {
		return array(
			'query' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'param' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			)
		);
	}
	public function getDescription() {
		return array(
			'API for BioDB queries'
		);
	}
	public function getParamDescription() {
		return array(
			'query' => 'Actual query name to retrieve, as defined in configuration',
			'param' => 'Parameter(s) to pass to query'
		);
	}
	public function getVersion() {
		return __CLASS__ . ': 1.1';
	}
	
}