<?php

/**
 * Created by PhpStorm.
 * User: Edward
 * Date: 3/27/17
 * Time: 3:12 PM
 */
class DatabaseConnectionComponent extends Component  {
	
	var $name="DatabaseConnection";
	
	public function createDBConnection($data,&$config_name=''){
		if(empty($data)) { return false; }
		
		$config_name="db_connect_".$config_name;
		$new_config = array(
			'datasource'=>'Database/Mysql',
			'driver' => 'mysql',
			'persistent' => false,
			'host' => $data['db_host'],
			'login' => $data['db_user'],
			'password'=> $data['db_password'],
			'database'=>$data['db_name'],
			'encoding'=>'utf8',
		);
		
		ClassRegistry::init('ConnectionManager');
		if(!ConnectionManager::create($config_name, $new_config)){
			return false;
		}
		
		return $config_name;
	}
	
}