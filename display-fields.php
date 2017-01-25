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
		$attributes = ""; 
		if( !empty( $args[ "attributes" ] ) ){
			foreach( $args[ "attributes" ] as $attribute => $attr_value ){
				$attributes .= sprintf( '%1$s="%2$s" ', $attribute, esc_attr( $attr_value ) );
			}
		}

		if( $args[ "type" ] === "text" || $args[ "type" ] === "number" ){
		 $field .= sprintf( '<input type="%1$s" name="%2$s" value="%3$s" id="%4$s" %5$s />', 
						esc_attr( $args["type"] ), esc_attr( $name ), $value, esc_attr( $id ), $attributes
					);
		}

		elseif( $args[ "type" ] === "select" ){
			$field .= sprintf( '<select id="%1$s" name="%2$s" %3$s>', esc_attr( $id ), esc_attr( $name ), $attributes ); 
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
				if( !empty( $value ) && !in_array( $value, $choices ) )
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
			$field .= sprintf( '<option value="" class="select-others" %1$s>%2$s</option>', 
								selected( $value, "", false ),
								__( "Others" ) 
							);

			$field .= "</select>";
		}
		// ************  end if type === select ****************** //
		
		
		$field .= "</fieldset>";

		echo $field;
	}
}
