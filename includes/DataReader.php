<?php
/**
 * A simple interface for handling saving, reading and writing of data 
 * 
 * Implementing class can add, read and update data in a storage 
 * the type of storage is determined by the implementing class 
 *
 *@package: Abbey Author Profile wp plugin 
 *@author: Rabiu Mustapha
 *@category: includes 
 *
 */
interface DataReader{
	
	private $storage = null;

	/**
	 * Get data from the storage 
	 */
	public function get_data();

	/**
	 * Add data to the storage 
	 */
	public function add_data();

	/**
	 * Updates the storage with the most recent version of data 
	 */
	public function update_data();

}