<?php

/**
 * Author: Hung Nguyen
 * Date: 1/9/17
 * Time: 5:06 PM
 *
 * @property 
 */

App::uses('CakeEmail', 'Network/Email');

class DatabaseStructureController extends AppController {
    var $uses = [
            'ClientProfile',
            'Clients',
            'TblMstepClientRequest'
    ];
    var $name = 'DatabaseStructure';

	public function index(){
		
		$this->page_title=__('DatabaseStructure');
	}
	
	public function updateStruct(){
	    if($this->request->is("post")){
	        $file = $_FILES;
	        $path = SQL;
	        $fileName = 'database_structure.sql';
	        $fileNameTmp = $fileName.'.'.date("Ymdhis");
	        
	        if ($file['file']['error'] > 0) {
	            $res['status'] = "NO";
	            $res['message'] = __("Fail to upload file");
	            Output::__output($res);
	        }
	        else {
	            if(@file_exists($path.$fileName)){
	                @unlink($path.$fileName);
	            }
	            @copy($file['file']['tmp_name'], $path.$fileNameTmp);
	            @move_uploaded_file($file['file']['tmp_name'], $path.$fileName);
	            
	            // write log
	            $str_log = "\n";
	            $str_log .= "Author: ".$this->Auth->user('first_name').$this->Auth->user('last_name');
	            $str_log .= "\n";
	            $str_log .= "Update type: database structure";
	            $str_log .= "\n";
	            $str_log .= "File upload: ".$path.$fileNameTmp;
	            $str_log .= "\n";
	            CakeLog::write('sql', $str_log);
	            // End write log
	            
	            $res['status'] = "YES";
	            $res['message'] = __("Database structure is updated");
	            Output::__output($res);
	        }
	    }
	}
}