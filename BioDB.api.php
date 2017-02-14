<?php
class ApiBioDB extends ApiBase {
	
	public function execute() {

		$params = $this->extractRequestParams();
		
		$param = null;
		$data = null;
		$output = array();
		$table = false;
		$cols = null;

		if ( array_key_exists( "param", $params ) ) {
			$param = $params["param"];
		}
		
		if ( array_key_exists( "table", $params ) ) {
			$table = $params["table"];
		}

		if ( array_key_exists( "query", $params ) ) {
			// Query new function in BioDB
			$output = BioDB::returnBioDB( $params["query"], $param );
		}
		
		# TODO: No filtering now
		//if ( array_key_exists( $params["query"], $output ) ) {
			
		//	$data = $output[$params["query"]];
		// }
		
		$data = $output;
		
		if ( $table ) {
			
			$cols = array();
			
			$subs = $params["query"].".";
			$tablerows = array();
			
			$first = 0;
			foreach ( $data as $row ) {
				$tablerow = array();
				
				foreach( $row as $key => $val ) {
					array_push( $tablerow, $val );
					
					$key = str_replace( $subs, "", $key );
					
					if ( $first === 0 ) {
						array_push( $cols, $key );
					}
				}
				
				array_push( $tablerows, $tablerow );
				$first = $first + 1;
			}
			
			$data = $tablerows;
			
		}
		
		$paramq = array();
		
		if ( $param ) {
			$paramq = explode( ",", $param );
		}

#		var_dump( $data ); exit;
		$this->getResult()->addValue( null, $this->getModuleName(), array ( 'status' => "OK", 'query' => $params["query"], 'param' => $paramq, 'cols' => $cols, 'rows' => $data ) );

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
			),
			'table' => array(
				ApiBase::PARAM_TYPE => 'boolean',
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
			'param' => 'Parameter(s) to pass to query',
			'table' => 'Whether to show the format in table output'
		);
	}
	public function getVersion() {
		return __CLASS__ . ': 1.1';
	}
	
}
