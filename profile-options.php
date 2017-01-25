<?php


	$action = $_POST["action"]; 
	$goback = wp_get_referer(); 
	$options = "";

	$this->message = "Form are submitted";
	if( !empty( $_POST[ $this->prefix."_options" ] ) ){
		$options = $_POST[ $this->prefix."_options" ];
		$user = $options["user_id"];
		unset( $options["user_id"] ); 
		if( count( $options ) > 0 ){
			foreach( $options as $key => $option ){
				if( array_key_exists( $key, $this->fields ) ){
					$callback = !empty( $this->fields[ $key ][ "args" ][ "callback" ] ) ? $this->fields[ $key ][ "args" ][ "callback" ] : "sanitize_text";
					$type = !empty( $this->fields[ $key ][ "args" ][ "type" ] ) ? $this->fields[ $key ][ "args" ][ "type" ] : "text";
					
					$this->options[ $key ] = call_user_func_array( array( $this, $callback  ),
					 										 array( $option, $type ) )	;			
				}

			} //endforeach //
		}//end if count options //

		if( !empty( $this->options ) )
			update_user_meta( $user, $this->prefix."_options", $this->options );
		
	}//end if // empty POST[$this->prefix."-options"] //

	



