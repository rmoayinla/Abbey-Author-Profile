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
	 * Json data container datas that will be used to populate some fields dynamically 
	 *@var: array
	 */
	private $data_json = array();
	
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
		$field = $value = $name = $id = "";
		$value = "";
		$name = $args[ "name" ]; 
		$id = $args[ "id" ];
		$field = "<fieldset>";
		$args[ "attributes" ][ "data-name" ] = $name;
		$attributes = $class = ""; 
		
		
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
			$choices = !empty( $args[ "choices" ]) ? $args[ "choices" ] : array();

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

	/**
	 * Populate value of fields that already have some existing data 
	 * fields that have values previously stored are prefilled so that they can be updated
	 *@return: 	mixed 		depending on if the value is found 
	 */
	private function get_field_value( $section_key, $key = "", $options ){
		if( !array_key_exists( $section_key, $options ) ){
			if( in_array( $section_key, $options ) )
		}
			$section = $options[ $section_key ];
			if( is_array( $section ) )
				return !empty( $section[ $key ] ) ? $section[ $key ] : var_export( $section );
		}
		return "";
	

	
}
