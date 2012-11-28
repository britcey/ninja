<?php

abstract class class_generator {
	protected $fp;
	protected $indent_lvl = 0;
	protected $class_suffix = '';
	protected $class_dir = '.';
	protected $class_basedir = '.';
	protected $classname;
	
	public function generate( $skip_generated_note = false ) {
		$class_dir = dirname( $this->get_filename() );
		
		if( !is_dir( $class_dir ) && !mkdir( $class_dir, 0755, true ) )
			throw new GeneratorException( "Could not create dir $class_dir" );
		
		$this->fp = fopen( $this->get_filename(), 'w' );

		if( $this->fp === false )
			throw new GeneratorException( "Could not open ".$this->get_filename()." for writing" );
		
		/* Hardcode, so we don't accidentaly add whitespace before < */
		fwrite( $this->fp, "<?php\n\n" );
		
		if( !$skip_generated_note )
			$this->comment( "\nNOTE!\n\nThis is an auto generated file. Changes to this file will be overwritten!\n" );
	}
	
	public function set_class_suffix( $class_suffix ) {
		$this->class_suffix = $class_suffix;
	}
	
	public function set_class_dir( $class_dir ) {
		$this->class_dir = $class_dir;
	}
	
	public function set_basedir( $class_basedir ) {
		$this->class_basedir = $class_basedir;
	}
	
	public function set_library() {
		$this->set_class_suffix( '_Core' );
		$this->set_class_dir( 'libraries' );
	}
	
	public function set_model() {
		$this->set_class_suffix( '_Model' );
		$this->set_class_dir( 'models' );
	}
	
	public function exists() {
		return file_exists( $this->get_filename() );
	}
	
	public function get_classname() {
		return $this->classname . $this->class_suffix;
	}
	
	protected function get_filename() {
		return $this->class_basedir . DIRECTORY_SEPARATOR . $this->class_dir . DIRECTORY_SEPARATOR . $this->classname . '.php';
	}
	
	protected function classfile( $path ) {
		$this->write( 'requrire_once( '. var_export($path, true) . ' );' );
	}
	
	protected function init_class( $parent = false, $modifiers = array() ) {
		if( is_array( $modifiers ) ) {
			$modifiers = implode( ' ', $modifiers );
		}
		if( !empty( $modifiers ) ) {
			$modifiers = trim($modifiers)." ";
		}
		$this->write();
		$this->write( $modifiers."class ".$this->get_classname().($parent===false?"":" extends ".$parent.$this->class_suffix)." {" );
	}
	
	protected function finish_class() {
		$this->write( "}" );
	}
	
	protected function variable( $name, $default = null, $visibility = 'private' ) {
		$this->write( "$visibility \$$name = " . var_export( $default, true ) . ";" );
	}
	
	protected function init_function( $name, $args = array(), $modifiers = array() ) {
		if( is_array( $modifiers ) ) {
			$modifiers = implode( ' ', $modifiers );
		}
		if( !empty( $modifiers ) ) {
			$modifiers = trim($modifiers)." ";
		}
		$argstr = implode(', ', array_map(function($n){return '$'.$n;},$args));
		$this->write();
		$this->write( "${modifiers}public function $name($argstr) {" );
	}
	
	protected function finish_function() {
		$this->write( "}" );
	}
	
	protected function comment( $comment ) {
		$lines = explode( "\n", $comment );
		foreach( $lines as $line ) {
			fwrite( $this->fp, str_repeat( "\t", $this->indent_lvl ) . "// " . trim($line) . "\n" );
		}
	}
	protected function write( $block = '' ) {
		$lines = explode( "\n", $block );
		foreach( $lines as $line ) {
			$this->indent_lvl -= substr_count( $line, '}' );
			fwrite( $this->fp, str_repeat( "\t", $this->indent_lvl ) . $line . "\n" );
			$this->indent_lvl += substr_count( $line, '{' );
		}
	}
}