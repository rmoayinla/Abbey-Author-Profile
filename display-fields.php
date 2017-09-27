<?php
/**
 * Display fields where user can update their profile meta information 
 *
 *
 * these fields are used by WP Settings API in displaying the Abbey Author Profile page
 * fields are generated according to the input type i.e. text, date, search, multi-select, dropdown etc
 * supports repeater fields 
 * stored values of each field are populated, styling and JS classes and attributes are added 
 * the fields are in HTML5 format 
 *
 *@package: Abbey Author Profile wp plugin 
 *@author: Rabiu Mustapha 
 *
 */

class Abbey_Profile_Field {

	/**
	 * Data container storing the options that will be used to populate field values 
	 *@var: array 
	 */
	private $options = array();

	/**
	 * Json data container,  datas that will be used to populate some fields dynamically 
	 *@var: array
	 */
	private $data_json = array();

	/**
	 * ID attribute of this field 
	 *@var: string 
	 */
	private $field_id = '';

	/**
	 * Type attribute of this field
	 *@var: string 
	 *@default: text 
	 */
	private $field_type = 'text';

	/**
	 * Class attribute of this field 
	 *@var: array
	 */
	private $field_class = [];

	/**
	 * Additional attributes of this field excluding id, name, class and type 
	 *@var: string 
	 */
	private $field_attributes = '';

	/**
	 * Name attribute of this field 
	 *@var: string 
	 */
	private $field_name = '';

	/**
	 * Current field value that have been saved for this user 
	 *@var: string 
	 */
	private $field_value = '';

	/**
	 * Full HTML5 markup of the field including attributes, class, id and type 
	 *@var: string 
	 */
	private $field_html = '';
	
	/**
	 * Constructor 
	 *populate properties i.e. both data containers 
	 */
	function __construct( $options, $json ){
		if( is_array( $options ) && count( $options ) > 0 )
			$this->options = $options;
		$this->data_json = $json;
	}

	/**
	 * Display the current field i.e. select, input, textarea 
	 *@param: $args 	array 		the field arguments from Settings API 
	 */
	public function display_field( $args ){
				
		
		$this->field_attributes( $args );

		
		if( in_array( $args[ "type" ], [ "text", "number", "date" ] ) ){
		 $this->field_html .= $this->input_field( $args );
		}
		

		elseif( $args[ "type" ] === "select" ){
			$this->field_html .= $this->select_field( $args );
		}
		
		elseif( in_array( $args[ "type" ], [ "radio", "checkbox" ] ) ){
			$this->field_html .= $this->radio_check_field( $args );
		}
		
		elseif( $args[ "type" ] === "textarea" ){
			$this->field_html .= $this->textarea_field( $args );
		}
		
		$this->field_html .= "</fieldset>";

		echo $this->field_html;

	}

	private function field_attributes( $args ){
		//set the id attribute of the field //
		if( !empty( $args[ "id" ] ) ) $this->field_id = $args[ "id" ];
		
		//set the name attribute //
		if( !empty( $args[ "name" ] ) ) $this->field_name = $args[ "name" ];
		
		//set the data-name attributes, used in JS //
		$args[ "attributes" ][ "data-name" ] = $this->field_name;
		
		//add profile-$key to class attributes for fields //
		if( !empty( $args[ "key" ] ) ) $this->field_class[] = "profile-".esc_attr( $args[ "key" ] );

		//add type-field class attributes for fields and set the field_type property //
		if( !empty( $args[ "type" ] ) ){
			$this->field_type = $args[ "type" ];
			$this->field_class[] = esc_attr( $args[ "type" ] )."-field";
		}

		//if we are not in a repeater, get the value attribute from our options  //
		if( !$this->is_repeater( $args ) ){
			$this->field_value = $this->get_field_value( $args[ "section_key" ], $args[ "key" ], $this->options );
		}
		//we are on a repeater field //
		elseif( !empty( $args[ "repeater_key" ] ) ) {
			if( !empty ( $this->options[ "repeater" ][ $args[ "repeater_key" ] ] ) ){
				$repeater = $this->options[ "repeater" ][ $args[ "repeater_key" ] ];
				if( is_array( $repeater ) && array_key_exists( $args[ "repeater_no" ], $repeater ) )
					$this->field_value = $this->get_field_value( $args[ "repeater_no" ], $args[ "key" ], $repeater );
			}

			$args[ "attributes" ][ "data-repeater" ] = $args[ "repeater_no" ];
			//add a repeater class to our field class attributes //
			$this->field_class[] = "repeater-group";
		}

		//no attributes, we are done just bail //
		if( empty( $args[ "attributes" ] ) ) return;

		/**
		 * IF we have classes set in our $args['attributes'], loop and add them to our field class
		 * then remove the class index from $args['attributes']
		 */
		if( !empty( $args[ "attributes" ][ "class" ] ) ){
			foreach ( $args[ "attributes" ][ "class" ] as $class ){
				$this->field_class[] = $class;	
			}
			unset( $args[ "attributes" ][ "class" ] );
		}	

		//loop and add our remaining attributes e.g. col, row, data-name etc //
		foreach( $args[ "attributes" ] as $attribute => $attr_value ){
			//flatten the attributes if we have an array //
			if( is_array( $attr_value ) ) $attr_value = implode( $attr_value, " " );
				
			$this->field_attributes .= sprintf('%1$s="%2$s"', $attribute, esc_attr( $attr_value ) );
		}
		

	}

	/**
	 * Is the current field a repeater field
	 * all repeater fields must have a 'repeater_no' or 'repeater_key' in their $args array 
	 *@return: 	bool 		true|false 
	 *@param: $args 		array 		field info from WP Settings API 
	 */
	private function is_repeater( $args ){
		if( !empty( $args[ "repeater_key" ] ) || !empty( $args[ "repeater_no" ] ) ) return true;
		
		return false;
	}
	/**
	 * Populate value of fields that already have some existing data 
	 * fields that have values previously stored are prefilled so that they can be updated
	 *@param: 	$section_key 	string 		the key to search for in fields container 
	 *			$key 			string 		the key to search for in the section_key in fields 
	 *			$options 		array 		user saved profile data 
	 *@return: 	mixed 		depending on if the value is found 
	 */
	private function get_field_value( $section_key, $key = "", $options ){
		
		//copy the option if the key exist in our $options data //
		if( array_key_exists( $section_key, $options ) ) $section = $options[ $section_key ];

		//ok, the key doesnt exist but we have a value, still copy it //
		elseif( $key = array_search( $section_key, $options ) ) $section = $options[ $key ];
		
		if( empty( $section ) ) return; //empty, simply bail //

		if( is_scalar( $section ) ) return $section; //not an array, bail again //
		
		if( is_array( $section ) )
			return !empty( $section[ $key ] ) ? $section[ $key ] : var_export( $section, false );
		
		return "";
	}

	/**
	 * Create an input type field
	 * the input type is text, date, search, number 
	 *@param: $args 	array 		field arguments from WP Settings API 
	 *@return: 			string 		an HTML input field
	 */
	function input_field( $args ){
		return sprintf( '<input type="%1$s" name="%2$s" value="%3$s" id="%4$s" class="%5$s" %6$s />', 
						esc_attr( $this->field_type ), 
						esc_attr( $this->field_name ), 
						$this->field_value, 
						esc_attr( $this->field_id ), 
						esc_attr( implode( $this->field_class, " " ) ),
						$this->field_attributes
					);
	}

	/**
	 * Create a select field type 
	 * select field have dropdown options where users can pick from 
	 *@param: $args 	array 	field arguments from WP Settings API 
	 *@return: 			string 	a HTML select field markup 
	 */
	function select_field( $args ){
		$field = "";
		$field .= sprintf( '<select id="%1$s" name="%2$s" class="%3$s" %4$s>', 
								esc_attr( $this->field_id ), 
								esc_attr( $this->field_name ), 
								esc_attr( implode( $this->field_class, " " ) ), 
								$this->field_attributes
						);
		//datas that should be populated dynamically from $data_json container and select options // 
		$respond_data = $choices = array();
		
		//our select choices/options //
		if( !empty( $args[ "choices" ] ) ) $choices = $args[ "choices" ];
		
		//if we have a data-json index in our $args, we populate our choices dynamically //
		if( !empty( $args[ "attributes" ][ "data-json" ] )  ){

			//now check if the data-json index has a key in our data_json property //
			if(  !empty( $this->data_json[ $args[ "attributes" ][ "data-json" ] ] ) ) 
			{
				//copy the data to choices from our data_json property //
				$choices = $this->data_json[ $args[ "attributes" ][ "data-json" ] ];

				//check if we have a data-respond attributes set and the data-respond exist in our $data_json property //
				if( !empty( $args[ "attributes" ][ "data-respond" ] ) && 
					!empty( $this->data_json[ $args[ "attributes" ][ "data-respond" ] ] ) 
				){
					//get the value of the data-respond field from our saved options //
					$respond_data = $this->get_field_value( $args[ "section_key" ], 
														$args[ "attributes" ][ "data-respond" ], 
														$this->options 
													);
					//if we got a value, check if it exists in our choices and populate accordingly //
					$choices = 	( !empty( $respond_data ) && !empty( $choices[ $respond_data ] ) ) ? 
								$choices[ $respond_data ] : 
								array();
				}//end if empty check for data-respond //

			}//end if empty( $this->data_json[ $args[ "attributes" ] ] )//
				
		}//end if data-json //
		
		//if we dont have any $choices/options for select field, add others and return field // 
		if( empty( $choices ) ){
			if( !empty( $args[ "others" ] ) )   
				$field .= sprintf( '<option value="" class="select-others" %1$s>%2$s</option>', 
									selected( $this->field_value, "", false ),
									__( "Others:", "abbey-author-profile" ) 
								);
			$field .= "</select>";
			return $field;
		}
		
		/** if for some reason the field value is not present in our choices array, add it */
		if( !empty( $this->field_value ) && 
			!in_array( $this->field_value, $choices ) &&
			!array_key_exists( $this->field_value, $choices )  && 
			empty( $args[ "attributes" ][ "data-json" ] ) 
		)
			$choices[ $this->field_value ] = $this->field_value;
		
		foreach ( $choices as $key => $choice ){
			//if the current choice is an array, dont flatten just use the $key instead //
			if( is_array( $choice ) ) $choice = $key;

			//use $keys as values except if $key is an integer //
			$option_value = is_int( $key ) ? $choice : $key; 

			$field .= sprintf( '<option value="%1$s" %2$s >%3$s</option>', 
								esc_attr( $option_value ), 
								selected( $this->field_value, $option_value, false ), 
								esc_html( $choice )
							);
		}//end foreach loop $choices //

		//add an others field if we have it in $args //
		if( !empty( $args["others"] ) )
			$field .= sprintf( '<option value="" class="select-others" %1$s>%2$s</option>', 
								selected( $this->field_value, "", false ),
								__( "Others:" ) 
							);

		$field .= "</select>";

		return $field;
			
	}

	function textarea_field( $args ){
		$field = "";
		if( empty( $args[ "attributes" ][ "rows" ] ) ) $this->field_attributes .= " rows='10' ";
		if( empty( $args[ "attributes" ][ "cols" ] ) ) $this->field_attributes .= " cols='20' ";
			
		$field .= sprintf( '<textarea class="%1$s" name="%2$s" value="%3$s" id="%4$s" %5$s></textarea>', 
							esc_attr( implode( $this->field_class, " " ) ),
							esc_attr( $this->field_name ), 
							esc_attr( $this->field_value ), 
							esc_attr( $this->field_id ), 
							$this->field_attributes
						);
		return $field;
	}

	function radio_check_field( $args ){
		$field = "";
		$choices = $args[ "choices" ];

		//bail if our $choices is not an array or empty //
		if( !is_array( $choices ) || empty( $choices ) ) return;
		
		$field .= sprintf( '<p class="%s">', esc_attr( $this->field_type ) );
		//loop through our choices //
		foreach( $choices as $key => $choice ){
			//clone our label from $key or $choice //
			$label = is_int( $key ) ? $choice : $key;

			$field .= sprintf( '<label><input type="%1$s" name="%2$s" value="%3$s" id="%4$s" %5$s %6$s /> %7$s</label>', 
								esc_attr( $this->field_type ), 
								esc_attr( $this->field_name ), 
								$choice, 
								esc_attr( $this->field_id ), 
								checked( $this->field_value, $choice, false ),
								$this->field_attributes,
								esc_html( $label )
			);
		}//end foreach loop //

		$field .= "</p>";

		return $field;
	}
	

	
}
