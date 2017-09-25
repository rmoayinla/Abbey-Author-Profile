<?php
/**
 * A simple class providing JSON Data for the User profile form 
 */

class Abbey_Json{

	/**
	 * Container to store data from storage 
	 *@var: array 
	 */
	private $data = array();

	/**
	 * Actual storage where data is read from and written to 
	 *@var: mixed 
	 */
	private $storage = "";

	public function __construct(){
		//our storage is a simple json file //
		$this->storage = plugin_dir_path( __FILE__ )."data.json"; 

		//read data from storage and populate our data container //
		$this->read_data();
		
	}

	/**
	 * Reads from storage and populate our data container 
	 * checks if our storage is valid before reading 
	 *@uses: is_valid_storage
	 *@return: $this 		instance of this class 
	 */
	public function read_data(){
		//read the file and populate our data container //
		if( $this->is_valid_storage() ){
			if( $json = json_decode( file_get_contents( $this->storage ), true ) )
				$this->data = $json;
		}
		return $this;
	}

	/**
	 * Return our $data container
	 *@return 	array 		the class data container 
	 */
	public function get_data(){
		
		$this->update_data(); //update the $data container before returning it //
		
		return $this->data;
	}

	/**
	 * Adds data to our data storage 
	 * checks if our storage is valid before adding/writing to it 
	 *@return: $this 		instance of this class 
	 */
	public function add_data( $data ){
		if( $this->is_valid_storage() ){
			file_put_contents( $this->storage, json_encode( $data, JSON_PRETTY_PRINT ) );
		}
		return $this;
	}

	/**
	 * Updates the $data container if modified through the wp filter 
	 * since our data for this class is saved in a file, we update the file content with the most recent version 
	 * we try as much as possible not to be writing to the file everytime but only when there is a change
	 *@uses: is_equal 
	 *@return: $this 		instance of this class
	 */
	function update_data(){
		//read from storage and populate our data container //
		$this->read_data();
		
		//read from data storage and provide a filter to extend/add datas //
		$json = apply_filters( "abbey_author_profile_json_data", $this->data );

		//if the data have been modified, update the storage //
		if( !$this->is_equal( $json, $this->data ) ) $this->add_data( $json );
		
		$this->data = $json; //update our data container with the most recent version //

		return $this;
		
	}

	/**
	 * Checks if two datas are the same in count and value 
	 * the two datas being compared have to be arrays 
	 * the two arrays are passed through multiple checks to confirm if they are the same or not
	 * keys and values are also compared 
	 *@return: bool 	true|false 	
	 *@param: array 	$a 		first data to compare against 
	 * 		  array 	$b 		second data to compare against 
	 */
	private function is_equal( $a, $b ){
		
		if( !is_array( $a ) ) $a = (array)$a; 
		if( !is_array( $b ) ) $b = (array)$b; 
		
		if( $a !== $b ) return false; 
		
		$count_a = count( $a, COUNT_RECURSIVE );
		$count_b = count( $b, COUNT_RECURSIVE ); 
		
		if(  $count_a != $count_b ) return false; 

		//perform a recursive array_diff on the arrays //
		//this function is a custom function found in includes/functions.php //
		$diff = abbey_array_diff_recursive($a, $b);
		if( !empty( $diff ) ) return false;

		return true;
	}

	/**
	 * Checks if our storage is valid 
	 * the term valid depends on how this class is used e.g. DB Storage, File Storage, Cache storage 
	 *@return: bool 		true|false 		depending on the check result 
	 */
	private function is_valid_storage(){

		//not valid if our storage is empty //
		if( empty( $this->storage ) ) return false;

		//not valid if our storage is not an existing file //
		if( !file_exists( $this->storage ) ) return false;

		//failed both checks, our storage is valid //
		return true;
	}
}