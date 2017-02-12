<?php
class ApiBioDB extends ApiBase {
	
	public function execute() {

		$params = $this->extractRequestParams();

		if ( array_key_exists( "query", $params ) ) {
			// Query new function in BioDB

		}

		return true;

	}
	
	public function getAllowedParams() {
		return array(
			'query' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
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
			'query' => 'Actual query name to retrieve, as defined in configuration'
		);
	}
	public function getVersion() {
		return __CLASS__ . ': 1.1';
	}
	
}