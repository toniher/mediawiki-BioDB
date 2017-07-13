<?php
class ApiBioDB extends ApiBase {
	
	public function execute() {

		$params = $this->extractRequestParams();
		
		$param = null;
		$data = null;
		$output = array();
		$table = false;
		$cols = null;
		$format = null;
		$sep = null;
		$typesolve = false;

		if ( array_key_exists( "param", $params ) ) {
			$param = $params["param"];
		}
		
		if ( array_key_exists( "table", $params ) ) {
			$table = $params["table"];
		}
		
		if ( array_key_exists( "fileformat", $params ) ) {
			$format = $params["fileformat"];
		}

		if ( array_key_exists( "sep", $params ) ) {
			$sep = $params["sep"];
		}
		
		if ( array_key_exists( "typesolve", $params ) ) {
			$typesolve = true;
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

		if ( $typesolve ) {
			$data = $this->processTyping( $data );
		}
		
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

		if ( $table && $format == 'csv' ) {
			
			// TODO: Fix this ugly solution
			if ( $sep ) {
				$csvstr = implode( $sep, $cols )."\n";
			} else {
				$csvstr = implode( "\t", $cols )."\n";
			}
			
			foreach ( $data as $row ) {
				
				if ( $sep ) {
					$csvstr = $csvstr . implode( $sep, $row ) ."\n";
				} else {
					$csvstr = $csvstr . implode( "\t", $row ) ."\n";
				}
				
			}
			
			header("Content-Type: application/csv");
			header("Content-Disposition: attachment; filename=".$params["query"].".csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo $csvstr;
			exit;
			
		} else {
		
			$this->getResult()->addValue( null, $this->getModuleName(), array ( 'status' => "OK", 'query' => $params["query"], 'param' => $paramq, 'cols' => $cols, 'rows' => $data ) );

		}
		
		return true;

	}
	
	private function processTyping( $data ) {
		
		$newdata = array( );
		
		foreach ( $data as $row ) {
			
			$newstruct = array();
			
			foreach ( $row as $prop => $value ) {
				
				$newstruct[$prop] = $this->fixType( $value );
				
			}
			
			array_push( $newstruct, $newdata );
			
		}
		
		return $newdata;
		
	}
	
	private function fixType( $value ) {
		
		if ( is_int( $value ) ) {
			$value = intval( $value );
		} else {
			if ( is_float( $value ) ) {
				$value = floatval( $value );	
			}
		}
		
		return $value;
		
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
			),
			'fileformat' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			),
			'sep' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			),
			'typesolve' => array(
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
			'table' => 'Whether to show the format in table output',
			'fileformat' => 'In table output, whether to download in csv format',
			'sep'	=> 'When using csv file format, the chosen separator (default tab)',
			'typesolve' => 'Adapt type (int, float) of values automatically'
		);
	}
	public function getVersion() {
		return __CLASS__ . ': 1.1';
	}
	
}
