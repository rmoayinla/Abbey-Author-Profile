<?php
/*
* Plugin Name: Abbey Author Profile
* Description: Customize author contact information and Avatar
* Author: Rabiu Mustapha
* Version: 0.1
* Text Domain: abbey-author-profile

*/

class Abbey_Author_Profile {
	private $options = array(); 
	private $fields = array();
	private $sections = array();
	private $data_json = array();


	private $page = "";
	private $prefix = "";
	private $admin_page = "";
	private $current_user = "";
	private $message = "";

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

		
		$this->data_json = $data->get_data();
		
		add_action('admin_init', array( $this, 'process_form' ) );

		
	}

	function setup_admin_page(){
		add_action( 'admin_menu', array( $this, 'extra_profile_page' ) );
		add_action('admin_enqueue_scripts', array( $this, "load_scripts" ) );
	}

	

	function add_section ( $sections ){
		if( empty( $sections ) || !is_array( $sections ) )
			return; 

		if( count( $sections ) > 0 ){
			foreach( $sections as $key => $section ){
				$this->sections[ $key ] = $section; 
			}
		}
		
	}
	function add_field( $fields ){
		if( empty( $fields ) || !is_array( $fields ) )
			return; 

		if( count( $fields ) > 0 ){
			foreach( $fields as $key => $field ){
				$key = is_int( $key ) ? $field[ "id" ] : $key;
				$this->fields[ $key ] = $field; 
			}
		}
	}

	function add_repeater( $repeaters ){
		if( empty( $repeaters ) || !is_array( $repeaters ) )
			return;
		foreach( $repeaters as $id => $repeater ){
			$id = is_int( $id ) ? $repeater[ "id" ] : $id;
			if( empty( $repeater[ "section" ] ) || empty( $this->sections[ $repeater[ "section" ] ] ) ){
				$repeater[ "section" ] = !empty( $repeater[ "section" ] ) ? $repeater[ "section" ] : $repeater[ "id" ];
				$repeater[ "title" ] = !empty( $repeater[ "title" ] ) ? $repeater[ "title" ] : ucwords( $repeater[ "id" ] );
				$section[ $repeater[ "id" ] ] =  array( "id" => $repeater[ "section" ]."_section", "title" => $repeater[ "title" ] );
				$this->add_section( $section );
			}

			if( !empty( $repeater[ "repeaters" ] ) && is_array( $repeater[ "repeaters" ] ) ){
				$fields = $repeater[ "repeaters" ];
				foreach( $fields as $no => $repeater_field ){
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
						

						$field[ "args" ] = wp_parse_args( $field[ "args" ], $args[ "args" ] );
						$field = wp_parse_args( $field, $args );

						$this->fields[ $field["id"] ] = $field;
						
						
					}
				}
			}
			
		}
	}

	public function init(){
		add_action('admin_init', array( $this, 'extra_profile_init' ) );
	}

	

	function extra_profile_page(){
		$this->admin_page = add_menu_page( 'Abbey Author Profile', 
											'Abbey Author Profile', 
											'manage_options', 
											$this->page, 
											array( $this, "profile_page" )
										);
	}

	function profile_page(){ 
		$this->current_user = wp_get_current_user();

		if( empty( $this->options ) )
			$this->options = get_user_meta( $this->current_user->ID, $this->prefix."_options", true );

		 ?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Abbey Author Profile: <?php  echo $this->current_user->display_name; ?></h2>
			<form action='' method='post' id="profile-form">
				<?php settings_fields( $this->page ); ?>
				<?php do_settings_sections( $this->page );?>

				<input type="hidden" name="<?php echo $this->prefix."_options[user_id]"; ?>" 
				value="<?php echo $this->current_user->ID; ?>" /> 
				
				<?php submit_button();
				print_r( $this->options );
				print_r( $this->message );
				?>
			</form>	
		</div>	<?php
		

	}

	function extra_profile_init(){
		register_setting( $this->page, $this->prefix."_options" );

		if( count ( $this->sections ) > 0 ){
			foreach( $this->sections as $section ){
				if( empty( $section[ "callback" ] ) )  
					$section[ "callback" ] = array( $this, "author_main_section" );
				
				add_settings_section( 
					$this->prefix."_".ltrim( $section["id"], "_" ),
					$section["title"], 
					$section["callback"], 
					$this->page
				);
			}
		} //end if //

		if( count( $this->fields ) > 0 ){
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
		if( !isset( $_POST[ "action" ] ) || !check_admin_referer( $this->page."-options" ) )
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
