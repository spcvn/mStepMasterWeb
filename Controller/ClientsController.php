<?php

/**
 * Created by PhpStorm.
 * User: Edward
 * Date: 1/9/17
 * Time: 5:06 PM
 *
 * @property TblMstepClientRequest $TblMstepClientRequest
 */

App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
App::uses('CakeEmail', 'Network/Email');

class ClientsController extends AppController {
    var $uses = [
            'ClientProfile',
            'Clients',
            'TblMstepClientRequest'
    ];

	public function index(){
	    $this->ClientProfile->bindModel(array(
            'hasMany' => array(
                    'Clients' => array(
                            'className' => 'Clients',
                            'foreignKey' => 'id'
                    )
	            )
            )
        );
	    $order[] = "ClientProfile.created_date DESC";
	    $clients = $this->ClientProfile->findAll(null, null, $order);
	    
	    $old_config_debug = Configure::read('debug');
	    Configure::write('debug', 0);
// 	    foreach($clients as &$client){
//             if($conn = mysqli_connect($client['Clients'][0]['db_host'], $client['Clients'][0]['db_user'], $client['Clients'][0]['db_password'], $client['Clients'][0]['db_name'])){
//                 $client['Clients'][0]['db_status'] = "OK";
//             }else{
//                 $client['Clients'][0]['db_status'] = "Fail";
//             }
// 	    }
	    Configure::write('debug', $old_config_debug);
	    $this->set(compact('clients'));
	}
	
	public function detail($id = null){
	    if (!is_numeric($id)) {
	        throw new NotFoundException();
	    }
	    $this->ClientProfile->bindModel(array(
	            'hasMany' => array(
	                    'Clients' => array(
	                            'className' => 'Clients',
	                            'foreignKey' => 'id'
	                    )
	            )
           )
        );
	    
	    $client = $this->ClientProfile->findById($id);
	    $this->set(compact('client'));
	}
	
	function updateStatus(){
	    if($this->request->is("post")){
	        $post = $_POST;
	        if(isset($post['client_id']) && isset($post['status'])){
	            $this->Clients->updateAll(array("del_flg" =>$post['status']), array("id"=>$post['client_id']));
	            $res['status'] = "YES";
	            Output::__output($res);
	        }else{
	            $res['status'] = "NO";
	            Output::__output($res);
	        }
	    }
	}
	
	public function add($client_id = null){
	    $client = [];
	    $request = [];
	    $is_request = false;
	    
	    if(isset($this->request->query['request_id'])){
	        $request_id=$this->request->query['request_id'];
	        
	        $this->TblMstepClientRequest->unbindFully();
	        $request = $this->TblMstepClientRequest->findById($request_id);
	        if(!is_numeric($request_id) || !$request){
	            throw new NotFoundException();
	        }else{
	            $is_request = true;
	        }
	    }else{
    	    if(!empty($client_id)){
    	        $this->ClientProfile->bindModel(array(
    	                'hasMany' => array(
    	                        'Clients' => array(
    	                                'className' => 'Clients',
    	                                'foreignKey' => 'id'
    	                        )
    	                )
    	           )
                );
    	        $client = $this->ClientProfile->findById($client_id);
    	        
    	        if(!$client || !is_numeric($client_id)){
    	            throw new NotFoundException();
    	        }
    	    }
	    }
	    $this->set(compact('is_request','client','request'));
	}
	
	public function saveData(){
	    if($this->request->is("post")){
	        $post = $_POST;
	        $old_config_debug = Configure::read('debug');
	        Configure::write('debug', 0);
	        
	        $datasource = $this->Clients->getDataSource();
	        $datasource->begin();
	        
	        if(!empty($post['client_id'])){
	            
	            if($conn = mysqli_connect($post['db_host'], $post['db_user'], $post['db_password'], $post['db_name'], $post['db_port'])){
	            }else{
	                $res['status'] = "DB";
	                Output::__output($res);
	            }
	            
	            $this->ClientProfile->id = $post['client_id'];
	            $this->Clients->id = $post['client_id'];
	            
	            if(!$this->Clients->save($post)){
	                $res['status'] = "NO";
	                Output::__output($res);
	            }
	            
	            if(!$this->ClientProfile->save($post)){
	                $res['status'] = "NO";
	                Output::__output($res);
	            }
	        }else{
	            $this->Clients->create();
	            $this->ClientProfile->create();
	            
	            if(!$this->Clients->save($post)){
	                $res['status'] = "NO";
	                Output::__output($res);
	            }
	            
	            $client_id = $this->Clients->getLastInsertID();
	            $post['id'] = $client_id;
	             
	            if(!$this->ClientProfile->save($post)){
	                $res['status'] = "NO";
	                Output::__output($res);
	            }
	            
	            // create database for host
				$password = $this->randomPassword();
				if($conn = mysqli_connect($post['db_host'], $post['db_user'], $post['db_password'], $post['db_name'], $post['db_port'])){
					$sql = file_get_contents(SQL.'database_structure.sql');
					// add account admin
	                $sql .= "INSERT INTO `tbl_mstep_master_users` (`id`, `worker_id`, `first_name`, `last_name`, `login_id`, `login_pass`, `email`, `area_id`, `address`, `authority`, `del_flg`, `created`, `modified`) VALUES";
	                $sql .= "(1, 0, '', '', '".CLIENT_MASTER_ACCOUNT."', '".$password['hash']."', '', 0, '', 'master', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00')";
	                
	                mysqli_multi_query($conn, $sql);
	                mysqli_close($conn);
	                
	                $res['pass_show'] = $password['show'];
	            }else{
	                $res['status'] = "DB";
	                Output::__output($res);
	            }
		
				if(!empty($post['request_id'])){
					$this->TblMstepClientRequest->id = $post['request_id'];
					$data = [];
					$data['status'] = 'created';
					$data['client_id'] = $client_id;
					$data['creator']=$this->Auth->user('id');
					$this->TblMstepClientRequest->save($data);
			
					// get request information
			
					$request_data=$this->TblMstepClientRequest->read();
					// Send email
					$Email=new CakeEmail('updateRequestStatus');
					$Email->subject(SUBJECT_CREATE_CLIENT_FROM_REQUEST);
			
					if(!empty($request_data['Requester']['email'])) {
						$Email->to($request_data['Requester']['email']);
					}
					// render
					$Email->viewVars(array(
						'domain'=>$post['short_name'].'.'.DOMAIN_NAME_MSTEP,
						'account'=>CLIENT_MASTER_ACCOUNT,
						'password'=>$password['show'],
						'request_url'=>Router::url(array('controller'=>'ClientRequest','action'=>'detail',$post['request_id']),true),
						'client_url'=>Router::url(array('controller'=>'Clients','action'=>'detail',$client_id),true)
					));
					$Email->template('created_client_from_request');
					$Email->send();
				}
	        }
	        
	        $datasource->commit();
	        
	        Configure::write('debug', $old_config_debug);
	        
	        $res['status'] = "YES";
	        Output::__output($res);
	    }
	}
	
	public function checkConnectDB(){
	    if($this->request->is("post")){
	        $post = $_POST;
	        
	        $old_config_debug = Configure::read('debug');
	        Configure::write('debug', 0);
            if($conn = mysqli_connect($post['host'], $post['user'], $post['pass'], $post['name'], $post['port'])){
                $res['status'] = "OK";
            }else{
                $res['status'] = "Fail";
            }
            Output::__output($res);
	        Configure::write('debug', $old_config_debug);
	    }
	}
	
	function randomPassword() {
	    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	    $password=[];
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < 8; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    $password['show'] = implode($pass);
	    
	    $passwordHasher = new SimplePasswordHasher(array('hashType' => 'sha1'));
	    $password['hash'] = $passwordHasher->hash($password['show']);
	    
	    return $password;
	    
	}
}