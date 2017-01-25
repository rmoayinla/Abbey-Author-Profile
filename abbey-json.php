<?php
class Abbey_Json{
	private $data_json = array();
	private $json_file = "";
	public function __construct(){
		$this->json_file = plugin_dir_path( __FILE__ )."data.json"; 
		if( file_exists( $this->json_file ) && $json = json_decode( file_get_contents( $this->json_file ), true ) )
			$this->data_json = $json;
		
	}

	public function get_data(){
		$this->update_data();
		
		return $this->data_json;
	}

	function update_data(){
		$json = apply_filters( "abbey_author_profile_json_data", $this->data_json );
		if( !$this->is_equal( $json, $this->data_json ) )
			file_put_contents( $this->json_file, json_encode( $json, JSON_PRETTY_PRINT ) );
		
		$this->data_json = $json;
		
	}




	private function is_equal( $a, $b ){
		if( !is_array( $a ) || !is_array( $b ) )
			return false; 
		if( $a !== $b )
			return false; 
		$count_a = count( $a, COUNT_RECURSIVE );
		$count_b = count( $b, COUNT_RECURSIVE ); 
		
		if(  $count_a != $count_b )
			return false; 
		

		return true;
	}
}