<?php 
$json = new Abbey_Json(); 
$data_json = $json->get_data();
$profile_countries = ( !empty( $data_json[ "country" ] ) ) ? $data_json[ "country" ] : array();
$profile_states = ( !empty( $data_json[ "state" ] ) ) ? $data_json[ "state" ] : array();


	$sections[ "main" ] = array( 
				"id" => "main_section", 
				"title" => __( "Contact Infos", "abbey-author-profile" ), 
				"callback" => "author_main_section" 
	);

	$fields[ "phone_no" ] = array(
		"id" => "phone_no",
		"title" => __( "Enter your phone number", "abbey-author-profile" ), 
		"callback" => "author_profile_fields", 
		"section" => "main_section", 
		"args" => array( "name" => "options[phone_no]", "key" => "phone_no", 
		"callback" => "sanitize_text", "type" => "number" )
	);

	$fields[ "country" ] = array(
		"id" => "country", 
		"title" => __( "Nationality:", "abbey-author-profile" ), 
		"callback" => "author_profile_fields", 
		"section" => "main_section",
		"args" => array( "type" => "select", "choices" => $profile_countries )
	);

	$fields[ "state" ] = array(
		"id" => "state", 
		"title" => __( "State:", "abbey-author-profile" ), 
		"callback" => "author_profile_fields", 
		"section" => "main_section",
		"args" => array( "type" => "select", "choices" => array(), 
						"attributes" => array( "data-respond" => "country", "data-json" => "state" ) )
	);




