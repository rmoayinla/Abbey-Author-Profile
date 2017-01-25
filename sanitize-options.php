<?php

class Abbey_Profile_Sanitizer{
	function __construct(){
		return $this;
	}
	public function sanitize( $text, $type ){
		if( $type === "text" || $type === "select" )
			return sanitize_text_field( $text );
		elseif( $type === "number" )
			return preg_replace( "/[^0-9.]/", '', $text );

	}
}