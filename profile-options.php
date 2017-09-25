<?php
/**
 * Handles the form submittion of the user profile data
 *
 * Form values are validated, sanitized and stored in the user meta table 
 * A user_id is passed through POST
 * unlike WP Settings API which stores data in options table 
 * the form submittion and saving are handled here because we want to store them in User Meta table 
 *
 *@author: Rabiu Mustapha
 *@package: Abbey Author Profile wordpress plugin 
 *
 *
 */

	//action as set in tbe form //
	$action = $_POST["action"]; 
	
	/**
	 * The user profile datas in the form are stored as a single array 
	 * we clone the options to this array
	 */
	$options = array();

	if( empty( $_POST[ $this->prefix."_options" ] ) ) return;
	
	//get the data containing all the profile data in the form //
	$options = $_POST[ $this->prefix."_options" ];

	//get the user id from the form //
	$user = $options["user_id"];

	//remove the user_id from our options //
	unset( $options["user_id"] ); 
	
	/**
	 * The form datas are validated and sanitized here 
	 * the datas in the form are iterated 
	 *the valid datas are then copied to Abbey-author-profile $options property 
	 *the options will be used in Abbey-author-profile to prefill/fetch the value of fields 
	 */
	$this->options = processform( $options, $this->fields );
	
	//if our options are not empty, update the user meta for the user //
	if( !empty( $this->options ) )
		update_user_meta( $user, $this->prefix."_options", $this->options );
		
	
	/**
	 * Handle the actual sanitizing and validating of the fields 
	 *this function is recursive if the passed option is an array 
	 *flattens the array and make sure all values are validated and sanitized 
	 *@return: 	array 		sanitized and validated values 
	 *@param: 		$options 	array 		options containing datas that will be sanitized and validated
	 * 				$fields 	array 		field container from Abbey-Author-Profile class 
	 */
	function processform( $options, $fields ){
		
		//array that will store validated datas //
		$return = array();

		//bail if our options is empty array //
		if( empty( $options ) ) return;

		//start looping the options //
		foreach( $options as $key => $option ){
			
			if( empty( $option ) ) continue; //simply skip an empty option //
			
			//if the current option is an array, we flatten and process it too //
			if( is_array( $option ) ){
				$return[ $key ] = []; //clone the current $key to our $return container //
				$return[ $key ] = processform( $option, $fields ); //then recursively process the values //
			}
			//we have a string as $option, check if the key exist in our $fields container //
			elseif( array_key_exists( $key, $fields ) ){
				/**
				 * All values has to be validated and sanitized
				 * we do that by passing them through a callback
				 * we check if a callback is defined, if not we use the plugin default callback 
				 */
				$callback = !empty( $fields[ $key ][ "args" ][ "callback" ] ) ? $fields[ $key ][ "args" ][ "callback" ] : "sanitize_text";
				$type = !empty( $fields[ $key ][ "args" ][ "type" ] ) ? $fields[ $key ][ "args" ][ "type" ] : "text";
				
				//pass the data over the callback and store the return of the callback in our $return container //
				$return[ $key ] = call_user_func_array( $callback,
				 										 array( $option, $type ) );			
			}

		} //endforeach //
			
		return $return; //return our validated and sanitized data //
	}

	/**
	 * Sanitize and validate the data pass according to the data type 
	 *@param: 		$text 		mixed 		data to be validated and sanitized 
	 *				$type 		string 		the data type which $text should be validated/santized according to
	 *@return: 		$text 					sanitized data 
	 */
	function sanitize_text( $text, $type ){
		if( in_array( $type, [ "text", "select" ] ) )
			return sanitize_text_field( $text );
		elseif( $type === "number" )
			return preg_replace( "/[^0-9.]/", '', $text );

		return $text;

	}

