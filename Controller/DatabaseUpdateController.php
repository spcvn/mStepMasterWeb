<?php

/**
 * Author: Hung Nguyen
 * Date: 1/9/17
 * Time: 5:06 PM
 *
 * @property 
 */

App::uses('CakeEmail', 'Network/Email');

class DatabaseUpdateController extends AppController {
    var $uses = [
            'ClientProfile',
            'Clients',
            'TblMstepClientRequest'
    ];
    var $name = 'DatabaseUpdate';

	public function index(){
		
		$this->page_title=__('DatabaseUpdate');
	}
	
	public function applySQL(){
	    if($this->request->is("post")){
	        $sql = $_POST['sql_query'];
	        
// 	        require_once 'SQL/Parser.php';
// 	        $parser = new SQL_Parser();
// 	        $struct = $parser->parse($sql);
// 	        if(!is_array($struct)){
// 	            $res['status'] = "NO";
// 	            $res['message'] = __("SQL is wrong");
// 	            Output::__output($res);
// 	        }else{
    	        $old_config_debug = Configure::read('debug');
    	        Configure::write('debug', 0);
    	        
    	        $clients = $this->Clients->findAll(array("del_flg" => 0));
    	        foreach ($clients as $client) {
    	            if($conn = @mysqli_connect($client['Clients']['db_host'], $client['Clients']['db_user'], $client['Clients']['db_password'], $client['Clients']['db_name'], $client['Clients']['db_port'])){
        	            @mysqli_multi_query($conn, $sql);
        	            @mysqli_close($conn);
    	            }
    	            @ini_set('max_execution_time', @ini_get("max_execution_time") + 10);
    	        }
    	        
    	        Configure::write('debug', $old_config_debug);
    	        
    	        // write log
    	        $str_log = "\n";
    	        $str_log .= "Author: ".$this->Auth->user('first_name').$this->Auth->user('last_name');
    	        $str_log .= "\n";
    	        $str_log .= "Update type: alter table query";
    	        $str_log .= "\n";
    	        $str_log .= "SQL statement: ".$sql;
    	        $str_log .= "\n";
    	        CakeLog::write('sql', $str_log);
    	        // End write log
    	        
    	        $res['status'] = "YES";
    	        $res['message'] = __("SQL is applied");
    	        Output::__output($res);
	        //}
	    }
	}
}