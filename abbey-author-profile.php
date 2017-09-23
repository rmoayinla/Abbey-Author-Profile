<?php
/**
 * Plugin Name: Abbey Author Profile
 * Description: Customize author contact information and Avatar
 * Author: Rabiu Mustapha
 * Version: 0.1
 * Text Domain: abbey-author-profile
 *
 *
 * A wordpress plugin for creating a profile page for site authors 
 * extensive use of wp user metas to add additional user info e.g. contact address, phone no, state, l.g.a etc
 * create an admin page of editing user profile info and a frontend page for displaying the user profile 
 *
 *
*/

class Abbey_Author_Profile {
	
	/**
	 * User meta information for the current user 
	 *@var: array 
	 */
	private $options = array(); 

	/**
	 * Collection of fields for the admin user profile page 
	 * @var: array 
	 */
	private $fields = array();

	/**
	 * Collection of section for fields of admin user profile page 
	 *@var: array
	 */
	private $sections = array();

	/**
	 * JSON data needed for loading some field content 
	 *@var: array 
	 */
	private $data_json = array();

	/**
	 * A reference to the user profile admin page 
	 *@var: null 
	 */
	private $page = null;

	/**
	 * A prefix to be used when storing user meta options to the database
	 *@var: 	string 
	 */
	private $prefix = "";

	private $admin_page = "";

	/**
	 * An instance of worpress WP_User class containing data of the current logged in user 
	 *@var: null 
	 */
	private $current_user = null;

	private $message = "";

	/**
	 * Constructor method: called when the class is instantiated 
	 * Add/setup default values for some properties and add some wp action hooks 
	 * @param: Abbey_Json $data 		an instance of Abbey_Json class 
	 */
	function __construct( Abbey_Json $data ){
		

		$this->page = "abbey_author_profile";
		$this->prefix = "abbey_author_profile"; 

		$this->sections[ "main" ] = array( 
			"id" => "main_section", 
			"title" => __( "Contact Infos", "abbey-author-profile" ), 
			"callback" => array( $this, "author_main_section" )
		);

		$this->fields[ "address" ] = array(
			"id" => "address",
			"title" => __( "Enter your local address", "abbey-author-profile" ), 
			"callback" => "author_profile_fields", 
			"section" => "main_section", 
			"args" => array( "key" => "address", 
			"callback" => "sanitize_text", "type" => "text" )
		);

		//populate the $data_json property with get_data from the passed $data class //
		$this->data_json = $data->get_data();
		
		//hook to admin_init to process form information, here the form is validated and updated //`
		add_action('admin_init', array( $this, 'process_form' ) );

		
	}

	/**
	 * Setup the Admin page for the user profile
	 * Hooks to wp admin_menu to add a User profile menu and admin_enqueue to enqueue styles for the menu page
	 */
	function setup_admin_page(){
		add_action( 'admin_menu', array( $this, 'extra_profile_page' ), 20 );
		add_action('admin_enqueue_scripts', array( $this, "load_scripts" ) );
	}

	
	/**
	 * Add sections to the $sections property 
	 * this method is used to add different sections to the user profile page
	 * sections are not yet added to the page here, they are only added to the class $section property 
	 */
	function add_section ( $sections ){
		
		//bail if is not an array that was passed, or its empty //
		if( !is_array( $sections ) ) return;
		if( empty( $sections )  ) return; 

		foreach( $sections as $key => $section ){
			$this->sections[ $key ] = $section; 
		}
		
	}

	/**
	 * Add fields to the $fields property
	 * this fields will be added to sections where users can enter their info on the user profile admin page
	 * field can be an input field, select, multi-select, quicktag, checkbox etc 
	 */
	function add_field( $fields, $single = false ){
		
		//bail if is not an array that was passed or it was an empty array //
		if( !is_array( $fields ) ) return; 
		if ( empty( $fields ) ) return;

		if( $single ){
			$key = is_int( $key ) ? $fields[ "id" ] : $key;
			$this->fields[ $key ] = $fields;
			return;
		}
		/**
		 * Loop through the fields and add them to the $fields property
		 * fields are stored as array, with field id as index
		 */
		foreach( $fields as $key => $field ){
			$key = is_int( $key ) ? $field[ "id" ] : $key;
			$this->fields[ $key ] = $field; 
		}
	}

	/**
	 * Handles adding sections and fields for repeaters 
	 * a single or multiple  section are added depending on the section in the $repeaters param 
	 * multiple fields are added and field infos are generated, these fields are added to $fields property 
	 *@param: 	array 	$repeaters 		multi-dimensional array of repeater sections and fields 
	 */
	function add_repeater( $repeaters ){
		
		//bail if the $repeaters is not an array or empty //
		if ( !is_array( $repeaters ) ) return;
		if( empty( $repeaters ) ) return;  

		/**
		 * Loop through the repeaters and start adding the section to $sections property 
		 * and field to $fields property 
		 */
		foreach( $repeaters as $id => $repeater ){
			//get the $id of each repeater index //
			$id = is_int( $id ) ? $repeater[ "id" ] : $id;
			
			/**
			 * If the repeater['section'] index is empty or its not yet added to our sections property 
			 * Get the section and add it to our sections property 
			 */
			if( empty( $repeater[ "section" ] ) || empty( $this->sections[ $repeater[ "section" ] ] ) ){
				
				//the repeater section id //
				$repeater[ "section" ] = !empty( $repeater[ "section" ] ) ? $repeater[ "section" ] : $repeater[ "id" ];
				
				//repeater section title //
				$repeater[ "title" ] = !empty( $repeater[ "title" ] ) ? $repeater[ "title" ] : ucwords( $repeater[ "id" ] );

				// generate a section containing the basic info to add a section to the $sections container //
				$section[ $repeater[ "id" ] ] =  array( "id" => $repeater[ "section" ]."_section", "title" => $repeater[ "title" ] );
				
				$this->add_section( $section ); //add the section of this repeater to our $sections container //
			}

			/**
			 * Add fields for the repeaters, fields are gotten from the $repeaters['repeaters'] index 
			 * the requered indexes for the fields are gotten and added to the $fields container 
			 */
			if( !empty( $repeater[ "repeaters" ] ) && is_array( $repeater[ "repeaters" ] ) ){
				
				//clone the repeaters index to a var //
				$fields = $repeater[ "repeaters" ];
				/**
				 * Since we  got a repeater field, the fields are going to be multiple fields 
				 * therefore fields are stored in a mutli-dimensional array 
				 * to add the main fields, we have to loop through the repeaters index
				 * and then we loop through each repeater field in the index to add to the $field container 
				 */
				foreach( $fields as $no => $repeater_field ){

					//skip if the current repeater field is not an array 
					if( !is_array( $repeater_field ) && empty( $repeater_field ) ) continue;

					/**
					 * Start the second loop and generate the required info for a field 
					 * then adds each field to the $fields container 
					 */
					foreach( $repeater_field as $key => $field ){
						$args[ "id" ] =  $this->prefix."_".ltrim( $field[ "id" ], "_" );
						$args[ "section" ] = $repeater[ "section" ]."_section";
						$args[ "callback" ]	= "author_profile_fields";
						$args[ "args" ][ "key" ] = $field[ "id" ];
						$args[ "args" ][ "section_key" ] = $repeater[ "section" ]."_section";
						$args[ "args" ][ "repeater_key" ] = $repeater[ "id" ];
						$args[ "args" ][ "repeater_no" ] = $no;
						$args[ "args" ]["type"] = "text";
						$args["args"][ "name" ] = $this->prefix."_options[repeater][".
										$repeater[ "id" ]."][".
										$no."][".
										$field["id"]."]";
						
						//merge and replace $field['args'] infos i.e field type, name, repeater no etc //
						$field[ "args" ] = wp_parse_args( $field[ "args" ], $args[ "args" ] );

						//merge and replace $field array with $args i.e. id, section, callback etc //
						$field = wp_parse_args( $field, $args );

						//add the field to the $field container //
						$this->fields[ $field["id"] ] = $field;
						
					} //end foreach second loop //
				} //end foreach first loop //
			} //end if $repeater['repeaters'] check //
			
		}//end foreach $repeaters loop //
	}

	function create_repeater_fields(){
		//get the current user //
		$this->current_user = wp_get_current_user();

		//populate our options if we havent populate it yet, options are user profile metas //
		if( empty( $this->options ) )
			$this->options = get_user_meta( $this->current_user->ID, $this->prefix."_options", true );

		/**
		 * Grab the repeater index from the user profile metas
		 * All repeater fields are stored in this index 
		 * loop through this index and get the fields for the repeaters 
		 * the repeater fields we are handling here have already been stored in the db
		 * since these fields are added dynamically by Javascript, we have to do the hardowrk of adding them
		 * manually to our fields so that they can display
		 */
		if( !empty( $this->options[ "repeater" ] ) ){
			
			//loop through the repeaters //
			foreach( $this->options[ "repeater" ] as $key => $repeaters ){
				
				//the section is the $key, if its a string //
				$clone_section = $key; 

				//containers for storing the repeater index and fields //
				$clone_fields = $current_field = array();
				
				/**
				 * the repeater field has to be more than 1
				 * the first field will already exist in our fields 
				 * we use the stored info of the first field that exist to generate fields for the other fields
				 * why we do this is because the other fields were added via javascript 
				 */
				if( count( $repeaters ) > 1 ){
					//loop through the current active repeater index e.g. experience //
					foreach( $repeaters as $no => $fields ){

						$no  = (int) $no; //cast the $no to an integer //
						/** We are at the first field in the repeater, this must exist in our $fields container */
						if( $no === 0 ){
							/** 
							 * Loop through all the $fields and check if it exist in our fields container 
							 * if it those exist, clone the $field info from the $fields container
							 * this clone info will be used for creating field info for the remaining repeating options  
							 */
							foreach( $fields as $key => $field ){
								if( array_key_exists( $key, $this->fields ) ){
									$clone_fields[ $key ] = $this->fields[ $key ];
								}
							}
							continue; // continue the loop to the next index //
						}
						/** 
						 * We are at the other fields that doesnt exist yet, so we have to do the hardowork 
						 * we check if we have a clone field, then use the clone_field info to generate the
						 * field info for these ones, these are important for repeater fields to be displayed 
						 * if we dont do these, only the first field will appear while other repeater fields wont
						 */
						elseif( $no > 0 && !empty( $clone_fields ) ){

							//loop through the fields //
							foreach( $fields as $key => $field ){
								//if the key exist in $clone_fields, that means we have a template for the repeater//
								if( array_key_exists( $key, $clone_fields ) ){
									/** Generate the field name, key, repeater_no and id */
									$current_field = $clone_fields[ $key ];
									$current_field[ "args" ][ "key" ] = $current_field[ "args" ][ "key" ];
									$current_field[ "args" ][ "repeater_no" ] = $no;
									$current_field[ "args" ][ "name" ] = $this->prefix."_options[repeater][".
										$clone_section."][".
										$no."][".
										$current_field["id"]."]";
									$current_field[ "id" ] = $current_field[ "id" ]."_".$no;

									//add each repeater field to our $fields container //	
									$this->fields[ $current_field[ "id" ] ] = $current_field;
								}
							}//end foreach loop //
						}//end elseif $no > 0 //

						$this->message = $clone_fields;		
					}
				}
			}//end foreach options[repeater]//
		}//endif empty $options"repeater"//
	}

	/**
	 * Initialize the plugin admin page 
	 * before calling the init method, add sections, fields and repeaters first
	 * this method adds the sections, fields and prepare the user admin profile page 
	 */
	public function init(){
		add_action('admin_init', array( $this, 'extra_profile_init' ), 10 );
	}

	

	function extra_profile_page(){
		$this->admin_page = add_menu_page( 'Abbey Author Profile', 
											'Abbey Author Profile', 
											'manage_options', 
											$this->page, 
											array( $this, "profile_page" )
										);
	}

	/**
	 * Callback method that displays the markup for the admin profile page
	 * the fields and sections are generated here including all other hidden form fields
	 */
	function profile_page(){ 
		//get the current user info if its empty //
		if( empty( $this->current_user ) ) $this->current_user = wp_get_current_user();
		
		//populate the options if its empty //
		if( empty( $this->options ) )
			$this->options = get_user_meta( $this->current_user->ID, $this->prefix."_options", true );

		 ?>
		<!-- Markup of the profile page -->
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php esc_html_e( sprintf( ' Abbey Author Profile: %s ', $this->current_user->display_name ) ); ?></h2>
			<form action='' method='post' id="profile-form">
				<?php settings_fields( $this->page ); ?>
				<?php do_settings_sections( $this->page );?>

				<input type="hidden" name="<?php echo $this->prefix."_options[user_id]"; ?>" 
				value="<?php echo $this->current_user->ID; ?>" /> 
				
				<?php submit_button();
				print_r( $this->options );
				?>
			</form>	
		</div>	<?php
		

	}

	/**
	 * Runs through the $sections and $fields properties and add them to the page
	 */
	function extra_profile_init(){
		//register settings, using Wp Settings API //
		register_setting( $this->page, $this->prefix."_options" );

		/** Add all sections that have been added to $sections container */
		if( count ( $this->sections ) > 0 ){
			
			foreach( $this->sections as $section ){
				
				//add a default section callback if none is specified //
				if( empty( $section[ "callback" ] ) ) $section[ "callback" ] = array( $this, "author_main_section" );
				
				//actual adding of section per WP Settings API //
				add_settings_section( 
					$this->prefix."_".ltrim( $section["id"], "_" ),
					$section["title"], 
					$section["callback"], 
					$this->page
				);
			}
		} //end if //

		/** Add all fields that have been added to $fields container */
		if( count( $this->fields ) > 0 ){
			$this->create_repeater_fields();
			foreach( $this->fields as $field ){
				$f_section = str_ireplace( "_section", "", $field[ "section" ] );
				$field[ "callback" ] = !empty( $field[ "callback" ] ) ? $field[ "callback" ] :
										"author_profile_fields";
				$args["type"] = "text";
				$args[ "id" ] =  $this->prefix."_".ltrim( $field[ "id" ], "_" );
				$args[ "key" ] = $field[ "id" ];
				$args[ "section_key" ] = $f_section;
				$args[ "callback" ] = "sanitize_text";
				$args[ "name" ] = $this->prefix."_options".
								ltrim( "[".$f_section."][".$args[ 'key' ]."]", "_" );

				$field[ "args" ] = wp_parse_args( $field[ "args" ], $args );


				add_settings_field(
					$this->prefix."_".ltrim( $field["id"], "_" ),
					$field["title"], 
					array( $this, $field["callback"] ), 
					$this->page, 
					$this->prefix."_".ltrim( $field["section"], "_" ), 
					$field["args"]
				);
			}
		}//endif//

	}

	function author_main_section(){	
		echo sprintf( '<p>%s</p>', __( "This belongs to a section", "abbey-author-profile" ) );
	 
	}

	function author_profile_fields( $args ){
		require_once( plugin_dir_path( __FILE__ )."display-fields.php" );
		$field = new Abbey_Profile_Field( $this->options, $this->data_json ); 
		$field->display_field( $args );
	}

	function load_scripts( $hook ){
		if( $hook !== $this->admin_page )
			return; 

		wp_enqueue_style( 'author-profile-css', plugin_dir_url( __FILE__ )."/author-profile.css"  );
		wp_enqueue_style( 'jquery-core-css', "//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" );
		wp_enqueue_style( 'tag-css', plugin_dir_url( __FILE__ )."libs/quicktags/jquery.tag-editor.css"  );

		wp_enqueue_script( "caret-script", plugin_dir_url( __FILE__ )."/libs/quicktags/jquery.caret.min.js", array( "jquery" ), "", true );
		wp_enqueue_script( "tag-script", plugin_dir_url( __FILE__)."/libs/quicktags/jquery.tag-editor.min.js", 
							array( "jquery"), "", true );
		
		wp_enqueue_script( "author-profile-script", plugin_dir_url( __FILE__ )."author-profile.js", 
							array( "jquery"), 1.0, true );

		wp_enqueue_script( "jquery-ui-core" );
		wp_enqueue_script( "jquery-ui-widget" );
		wp_enqueue_script( "jquery-ui-widget" );
		wp_enqueue_script( "jquery-ui-autocomplete" );
		wp_enqueue_script( "jquery-ui-position" );
		wp_enqueue_script( "jquery-ui-datepicker" );

		wp_localize_script( "author-profile-script", "abbeyAuthorProfile", 
			array(
				"data_json" => $this->data_json
			) 
		);
	}

	function process_form(){
		if( !isset( $_POST[ "action" ] )  )
			return; 
		
		require_once( plugin_dir_path( __FILE__ )."profile-options.php" );
	}

	
}

add_filter( "abbey_author_profile_json_data", function( $data ) {
		$data["state"]["Nigeria"] = array( "Lagos", "Ogun", "Osun", "Oyo", "Osun", "Edo", "Kwara" );
		$data["state"]["South Africa"] = array( "Cape Town", "Johannesburg" );
		$data["state"][ "Ghana" ] = array( "Accra", "Kumasi" );
		return $data;
});

require_once( plugin_dir_path( __FILE__ )."abbey-json.php" );
require_once( plugin_dir_path( __FILE__ )."profile-fields.php" );


$json_data = new Abbey_Json();

$abbey_author_profile = new Abbey_Author_Profile( $json_data );
$abbey_author_profile->setup_admin_page();
$abbey_author_profile->add_field( $fields );
$abbey_author_profile->add_section( $sections );
$abbey_author_profile->add_repeater( $repeaters );
$abbey_author_profile->init();
