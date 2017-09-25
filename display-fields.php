<?php
/**
 * Display fields where user can update their profile meta information 
 *
 *
 * these fields are used by WP Settings API in displaying the Abbey Author Profile page
 * fields are generated according to the input type i.e. text, date, search, multi-select, dropdown etc
 * supports repeater fields 
 * stored values of each field are populated and styling and JS classes and attributes are added 
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

	private $field_id = '';

	private $field_type = 'text';

	private $field_class = [];

	private $field_attributes = '';

	private $field_name = '';

	private $field_value = '';
	
	/**
	 * Constructor 
	 *populate properties i.e. both data containers 
	 */
	function __construct( $options, $json ){
		if( is_array( $options ) && count( $options ) > 0 )
			$this->options = $options;
		$this->data_json = $json;
	}

	public function display_field( $args ){
		$field = $value = $name = $id = $attributes = $class ="";
		$name = $args[ "name" ]; 
		$id = $args[ "id" ];
		$field = "<fieldset>";
		$args[ "attributes" ][ "data-name" ] = $name;
	
		
		
		if( empty( $args[ "repeater_key" ] ) && empty( $args[ "repeater_no" ] )  ){
			$value = $this->get_field_value( $args[ "section_key" ], $args[ "key" ], $this->options );
		}
		elseif( !empty( $args[ "repeater_key" ] ) ) {
			if( !empty ( $this->options[ "repeater" ][ $args[ "repeater_key" ] ] ) ){
				$repeater = $this->options[ "repeater" ][ $args[ "repeater_key" ] ];
				if( is_array( $repeater ) && array_key_exists( $args[ "repeater_no" ], $repeater ) )
					$value = $this->get_field_value( $args[ "repeater_no" ], $args[ "key" ], $repeater );
			}
			$args[ "attributes" ][ "data-repeater" ] = $args[ "repeater_no" ];
		}

		if( !empty( $args[ "attributes" ] ) ){
			$class= ( !empty( $args[ "attributes" ][ "class" ] ) )  ? $args[ "attributes" ][ "class" ] : array();
			$class[] = "profile-".esc_attr( $args[ "key" ] );
			if( !empty( $args[ "repeater_key" ] ) || !empty( $args[ "repeater_no" ] ) )
				$class[] = "repeater-group";

			if( !empty( $args[ "attributes" ][ "class" ] ) )
				unset( $args[ "attributes" ][ "class" ] );

			foreach( $args[ "attributes" ] as $attribute => $attr_value ){
				if( is_array( $attr_value ) )
					$attributes .= sprintf('%1$s="%2$s"', $attribute, esc_attr( implode( $attr_value, " " ) ) );
				else
					$attributes .= sprintf('%1$s="%2$s"', $attribute, esc_attr( $attr_value ) );
			}
		}

		//******************** start creating fields based on $args["type"]*********//

		//*********** if its type= text or number or date *****************//
		if( in_array( $args[ "type" ], [ "text", "number", "date" ] ) ){
		 $field .= sprintf( '<input type="%1$s" name="%2$s" value="%3$s" id="%4$s" class="%5$s" %6$s />', 
						esc_attr( $args["type"] ), 
						esc_attr( $name ), 
						$value, 
						esc_attr( $id ), 
						esc_attr( implode( $class, " " ) ),
						$attributes

					);
		}
		//******** end check for text, number and date ************** //

		elseif( $args[ "type" ] === "select" ){
			$field .= sprintf( '<select id="%1$s" name="%2$s" class="%3$s" %4$s>', 
								esc_attr( $id ), 
								esc_attr( $name ), 
								esc_attr( implode( $class, " " ) ), 
								$attributes
							 ); 
			$respond_data = array();
			$choices = !empty( $args[ "choices" ] ) ? $args[ "choices" ] : array();

			if( !empty( $args[ "attributes" ][ "data-json" ] )  ){
				
				if( !empty( $args[ "attributes" ][ "data-json" ] ) && 
					!empty( $this->data_json[ $args[ "attributes" ][ "data-json" ] ] ) 
				){
					//{ "states" : {} } //
					$choices = $this->data_json[ $args[ "attributes" ][ "data-json" ] ];
					if( !empty( $args[ "attributes" ][ "data-respond" ] ) && 
						!empty( $this->data_json[ $args[ "attributes" ][ "data-respond" ] ] ) 
					){
						$respond_data = $this->get_field_value( $args[ "section_key" ], 
															$args[ "attributes" ][ "data-respond" ], 
															$this->options 
														);
						$choices = ( !empty( $respond_data ) && !empty( $choices[ $respond_data ] ) ) ? 
								$choices[ $respond_data ] : array();
					} 
				}
				
				

					
			}
			//======= end if ( $args["attributes"]["data-json"] ) ============ //

			if( !empty( $choices ) ){
				if( !empty( $value ) && 
					!in_array( $value, $choices ) &&
					!array_key_exists( $value, $choices )  && 
					empty( $args[ "attributes" ][ "data-json" ] ) 
				){
					$choices[ $value ] = $value;
				}
				foreach ( $choices as $key => $choice ){
					if( is_array( $choice ) )
						$choice = $key;
					$option_value = is_int( $key ) ? $choice : $key; 
					$field .= sprintf( '<option value="%1$s" %2$s >%3$s</option>', 
										esc_attr( $option_value ), 
										selected( $value, $option_value, false ), 
										$choice
									 );
				}
			} //endif empty choices  //
			if( !empty( $args["others"] ) )
				$field .= sprintf( '<option value="" class="select-others" %1$s>%2$s</option>', 
									selected( $value, "", false ),
									__( "Others" ) 
								);

			$field .= "</select>";
		}
		// ************  end if type === select ****************** //
		elseif( in_array( $args[ "type" ], [ "radio", "checkbox" ] ) ){
			$choices = $args[ "choices" ];
			if( is_array( $choices ) && !empty( $choices ) )
				$class[] = $args[ "type" ];

				$field .= sprintf( '<p class="%s">', esc_attr( implode( $class, " " ) ) );
				foreach( $choices as $key => $choice ){
					$label = is_int( $key ) ? $choice : $key;
					$field .= sprintf( '<label>
										<input type="%1$s" name="%2$s" value="%3$s" id="%4$s" %5$s %6$s />
										%7$s
										</label>', 
										esc_attr( $args["type"] ), 
										esc_attr( $name ), 
										$choice, 
										esc_attr( $id ), 
										checked( $value, $choice, false ),
										$attributes,
										esc_html( $label )

					);
				}
		}
		//************* end if radio and checkbox ************** //
		elseif( $args[ "type" ] === "textarea" ){
			if( empty( $args[ "attributes" ][ "rows" ] ) )
				$attributes .= " rows='10' ";
			if( empty( $args[ "attributes" ][ "cols" ] ) )
				$attributes .= " cols='20' ";
			
			$field .= sprintf( '<textarea class="%1$s" name="%2$s" value="%3$s" id="%4$s" %5$s></textarea>', 
								esc_attr( implode( $class, " " ) ),
								esc_attr( $name ), 
								esc_attr( $value ), 
								esc_attr( $id ), 
								$attributes
								 );

		}
		
		$field .= "</fieldset>";

		echo $field; print_r( $args );;
	}

	private function create_attributes( $args ){
		//set the id attribute of the field //
		if( !empty( $args[ "id" ] ) $this->field_id = $args[ "id" ];
		
		//set the name attribute //
		if( !empty( $args[ "name" ] ) $this->field_name = $args[ "name" ];
		
		//set the data-name attributes, used in JS //
		$args[ "attributes" ][ "data-name" ] = $this->field_name;
		
		//add profile-$key to class attributes for fields //
		if( !empty( $args[ "key" ] ) ) $this->field_class[] = "profile-".esc_attr( $args[ "key" ] );

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
			if( is_array( $attr_value ) )
				$this->field_attributes .= sprintf('%1$s="%2$s"', $attribute, esc_attr( implode( $attr_value, " " ) ) );
			else
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

	function input_field(){
		 $field .= sprintf( '<input type="%1$s" name="%2$s" value="%3$s" id="%4$s" class="%5$s" %6$s />', 
						esc_attr( $args["type"] ), 
						esc_attr( $name ), 
						$value, 
						esc_attr( $id ), 
						esc_attr( implode( $class, " " ) ),
						$attributes

					);
	}

	function select_field(){

	}

	function textarea_field(){

	}
	

	
}
