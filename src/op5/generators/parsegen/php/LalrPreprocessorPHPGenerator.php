<?php

class LalrPreprocessorPHPGenerator extends class_generator {
	private $grammar;
	
	public function __construct( $parser_name, $grammar ) {
		$this->classname = $parser_name . "Preprocessor";
		$this->grammar = $grammar->get_tokens();
		$this->set_library();
	}
	
	public function generate() {
		parent::generate();
		
		$this->init_class();
		foreach( $this->grammar as $name => $match ) {
			if( $name[0] != '_' ) {
				$this->generate_preprocessor( $name );
			}
		}
		$this->finish_class();
	}
	
	private function generate_preprocessor( $name ) {
		$this->init_function( 'preprocess_'.$name, array( 'value' ) );
		$this->write( 'return $value;' );
		$this->finish_function();
	}
}