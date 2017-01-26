<?php
class Abbey_Profile_Field {
	private $options = array();
	private $data_json = array();
	function __construct( $options, $json ){
		
		$this->options = $options;
		$this->data_json = $json;
	}

	public function display_field( $args ){
		$field = $value = $name = $id = "";
		$value = !empty( $this->options[ $args[ "key" ] ] ) ? esc_attr( $this->options[ $args[ "key" ] ] ) : ""; 
		$name = $args[ "name" ]; 
		$id = $args[ "id" ];
		$field = "<fieldset>";
		$args[ "attributes" ][ "data-name" ] = $name;
		$attributes = $class = ""; 
		
		if( !empty( $args[ "attributes" ] ) ){
			$class= ( !empty( $args[ "attributes" ][ "class" ] ) )  ? $args[ "attributes" ][ "class" ] : array();
			$class[] = "profile-".esc_attr( $args[ "key" ] );
			
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
			$choices = $args[ "choices" ];

			if( !empty( $args[ "attributes" ][ "data-json" ] )  )
				if( !empty( $args[ "attributes" ][ "data-json" ] ) && 
					!empty( $this->data_json[ $args[ "attributes" ][ "data-json" ] ] ) 
				){
					//{ "states" : {} } 
					$choices = $this->data_json[ $args[ "attributes" ][ "data-json" ] ];
					if( !empty( $args[ "attributes" ][ "data-respond" ] ) &&
						!empty( $this->options[ $args["attributes" ][ "data-respond" ] ] ) 
					)
						//{ "states": { "Nigeria": {} } }
						$choices = !empty( $choices[ $this->options[ $args["attributes" ][ "data-respond" ] ] ] ) ?
									$choices[ $this->options[ $args["attributes" ][ "data-respond" ] ] ] : 
									array();
				}
			//======= end if ( $args["attributes"]["data-json"] ) ============ //

			if( !empty( $choices ) ){
				if( !empty( $value ) && !in_array( $value, $choices ) && empty( $args[ "attributes" ][ "data-json" ] ) )
					$choices[ $value ] = $value;
				foreach ( $choices as $key => $choice ){
					$option_value = is_int( $key ) ? $choice : $key; 
					$field .= sprintf( '<option value="%1$s" %2$s >%3$s</option>', 
										esc_attr( $option_value ), 
										selected( $value, $option_value, false ),
										esc_attr( $choice )
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
		elseif( $args[ "type" ] === "quicktags" ){
			$class[] = "quicktags";

			$field .= sprintf( '<input type="text" name="%1$s" value="%2$s" class= "%3$s" %4$s />', 
								esc_attr( $name ), 
								esc_attr( $value ), 
								esc_attr( implode( $class, " " ) ),
								$attributes
							);
			
		}
		
		$field .= "</fieldset>";

		echo $field;
	}
}
