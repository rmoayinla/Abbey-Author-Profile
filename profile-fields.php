<?php 
$json = new Abbey_Json(); 
$data_json = $json->get_data();
$profile_countries = ( !empty( $data_json[ "country" ] ) ) ? $data_json[ "country" ] : array();
$profile_states = ( !empty( $data_json[ "state" ] ) ) ? $data_json[ "state" ] : array();


	$sections[ "bio" ] = array( 
				"id" => "bio_section", 
				"title" => __( "Personal/Bio Data", "abbey-author-profile" )
	);

	$fields[ "phone_no" ] = array(
		"id" => "phone_no",
		"title" => __( "Enter your phone number", "abbey-author-profile" ), 
		"callback" => "author_profile_fields", 
		"section" => "main_section", 
		"args" => array(  "key" => "phone_no", 
		"callback" => "sanitize_text", "type" => "number" )
	);

	$fields[ "country" ] = array(
		"id" => "country", 
		"title" => __( "Country of Residence:", "abbey-author-profile" ), 
		"callback" => "author_profile_fields", 
		"section" => "main_section",
		"args" => array( "type" => "select", "choices" => array(), "others" =>  true,
			"attributes" => array( "data-json" => "country" ) )
	);

	$fields[ "state" ] = array(
		"id" => "state", 
		"title" => __( "State of Residence:", "abbey-author-profile" ), 
		"callback" => "author_profile_fields", 
		"section" => "main_section",
		"args" => array( "type" => "select", "choices" => array(), 
						"attributes" => array(  
							"data-json" => "state", "data-respond" => "country"
						) 
					)
	);

	$fields[ "date_of_birth" ] = array(
		"id" => "date_of_birth", 
		"title" => __( "Date of Birth:", "abbey-author-profile" ),
		"section" => "bio_section", 
		"args" => array( "type" => "date" )
	);

	$fields[ "sex" ] = array(
		"id" => "sex",
		"title" => __( "Sex:", "abbey-author-profile" ),
		"section" => "bio_section", 
		"args" => array( "type" => "radio", "choices" => array( "Male", "Female" ),
						"attributes" => array( "class" => [ "radio-inline" ] ) )
	);

	$fields[ "religion" ] = array(
		"id" => "religion", 
		"title" => __( "Religion:", "abbey-author-profile" ), 
		"section" => "bio_section", 
		"args" => array( "type" => "select", 
						"choices" => array( "christian" => "Christianity", 
											"muslim" => "Islam"
											), 
						"others" => true
		 ) 
	);

	$fields[ "languages" ] = array(
		"id" => "languages", 
		"title" => __( "Languages spoken:", "abbey-author-profile" ), 
		"section" => "bio_section", 
		"args" => array( "attributes" => array( 
						"placeholder" => __( "Let's know the languages you speak" ), 
						"class" => [ "quicktags" ] 
						) 
				) 
	);

	$fields[ "hobbies" ] = array(
		"id" => "hobbies", 
		"title" => __( "Interests/Hobbies:", "abbey-author-profile" ), 
		"section" => "bio_section", 
		"args" => array(
			"type" => "text", 
			"attributes" => [ "class" => [ "quicktags", "large-box" ], "placeholder" => __( "Tell us things you love doing" ) ]
		)
	);


	$repeaters[ "experience" ] = array(
		"id" => "experience", 
		"repeaters" =>  array(
			array(
				"company" => array(
					"id" => "company",
					"title" => __( "Organisation/Institution name:", "abbey-author-profile" ), 
					"args" => array( "type" => "text" )
				), 
				"year" => array(
					"id" => "year", 
					"title" => __( "Started on:", "abbey-author-profile" ), 
					"args" => array( "type" => "date" )
				)
			)
		)
	);




