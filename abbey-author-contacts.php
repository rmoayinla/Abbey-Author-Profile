function add_author_contacts(){
		add_filter( 'user_contactmethods', array( $this, "user_contacts" ) );
	}

function user_contacts( $contacts ){	

		$contacts["linkedin"] = __( "Linkedin", "abbey-author-profile" );
		$contacts["facebook"] = __( "Facebook", "abbey-author-profile" );
		$contacts["twitter"] = __( "Twitter", "abbey-author-profile" );
		$contacts["google-plus"] = __( "Google Plus", "abbey-author-profile" );

		return $contacts;
	}