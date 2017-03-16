<?php

/**
 * Created by PhpStorm.
 * User: Edward
 * Date: 2/15/17
 * Time: 11:00 AM
 *
 * @property TblMstepClientRequest $TblMstepClientRequest
 */

App::uses('CakeEmail', 'Network/Email');

class ClientRequestController extends AppController {
	var $name = "ClientRequest";
	var $uses = array('TblMstepClientRequest');
	var $components=array('Email');
	
	public function beforeFilter(){
		parent::beforeFilter();
		
		$this->page_title=__('Client Request', true);
	}
	
	public function index(){
		$this->paginate=[
			'conditions'=>[
				'TblMstepClientRequest.del_flg'=>0
			],
			'order'=>'TblMstepClientRequest.id DESC'
		];
		
		$request=$this->paginate('TblMstepClientRequest');
//		v($request);
		$this->set(compact('request'));
	}
	
	public function add(){
		$this->render('edit');
	}
	
	public function edit($id){
		if(!$id or !is_numeric($id)) { return false; }
		
		$request=$this->TblMstepClientRequest->getRequestById($id);
		
		$this->set(compact('request'));
	}
	
	public function detail($id){
		if(!$id or !is_numeric($id)) { return false; }
		
		$request=$this->TblMstepClientRequest->getRequestById($id);
		
		$this->set(compact('request'));
	}
	
	/**
	 * Only for add and edit
	 * @return bool
	 *
	 */
	public function save_process(){
		if(!$this->request->is('ajax')){return false;}
		if(!$this->data) {return false;}
		$data=$this->data;
		$request_id=0;
		
		if(!empty($data['id'])){
			$data['modified_date']=date('Y-m-d H:i:s');
			$this->TblMstepClientRequest->id=$data['id'];
			$request_id=$data['id'];
			unset($data['id']);
		} else {
			$data['requester']=$this->Auth->user('id');
			$data['created_date']=date('Y-m-d H:i:s');
			$this->TblMstepClientRequest->create();
		}
		
		$edit=$request_id>0;
		
		// alway set status is Requested
		$data['status']='requested';
		
		$this->TblMstepClientRequest->set($data);
		
		if(!$this->TblMstepClientRequest->save()) {
			$res['message']=__('Cannot save Client request, please try again',true);
			Output::__outputNo($res);
		}
		
		// get class insert id
		if(!$edit) {
			$request_id = $this->TblMstepClientRequest->getLastInsertID();
		}
		// send email
		$Email=new CakeEmail('updateRequestStatus');
		
		if($edit){
			$Email->subject(SUBJECT_UPDATE_REQUEST);
		} else {
			$Email->subject(SUBJECT_NEW_REQUEST);
		}
		
		$Email->viewVars(array(
			'url'=>Router::url(array('controller'=>'ClientRequest','action'=>'detail',$request_id),true)
		));
		$Email->to($this->Auth->user('email'));
		$Email->template('update_request');
		$Email->send();
		
		// save success
		$res['message']=__('Your request has been saved success',true);
		Output::__outputYes($res);
		return false;
	}
	
	/**
	 * Update status
	 *
	 * @return bool
	 */
	public function update_status() {
		if (!$this->request->is('ajax')) {
			return false;
		}
		if (!isset($this->data['id']) or (int)$this->data['id']<=0) {
			return false;
		}
		
		// update request status
		$data=$this->data;
		
		// get reason to update
		$reason='';
		if(isset($data['reason'])) {
			$reason = $data['reason'];
			unset($data['reason']);
		}
		
		// get request id
		$request_id=$data['id'];
		unset($data['id']);
		
		// set time and editor information
		$data['modified_date']=date('Y-m-d H:i:s');
		
		// update request
		$this->TblMstepClientRequest->id=$request_id;
		$this->TblMstepClientRequest->set($data);
		
		if(!$this->TblMstepClientRequest->save($data)) {
			$res['message']=__('Cannot save Client Request, please try again',true);
			Output::__outputNo($res);
		}
		$request_data=$this->TblMstepClientRequest->getRequestByID($request_id);
		
		// Send email
		$Email=new CakeEmail('updateRequestStatus');
		if($data['status']=='returned') {
			$Email->subject(SUBJECT_RETURN_REQUEST);
		} else if($data['status']=='rejected'){
			$Email->subject(SUBJECT_REJECT_REQUEST);
		}
		if(!empty($request_data['Requester']['email'])) {
			$Email->to($request_data['Requester']['email']);
		}
		$Email->viewVars(array(
			'reason'=>$reason,
			'status'=>$data['status'],
			'url'=>Router::url(array('controller'=>'ClientRequest','action'=>'detail',$request_id),true)
		));
		$Email->template('update_request_status');
		$Email->send();
		
		// save success
		$res['message']=__('Status has beed updated success to ',true).$data['status'];
		Output::__outputYes($res);
	}
	
	/**
	 * Change delete status to yes
	 *
	 * @return bool
	 */
	public function delete() {
		if (!$this->request->is('ajax')) {
			return false;
		}
		if (!isset($this->data['id']) or (int)$this->data['id']<=0) {
			return false;
		}
		
		// find client request
		$this->TblMstepClientRequest->id=$this->data['id'];
		$this->TblMstepClientRequest->set(array(
			'del_flg'=>1,
			'modified_date'=>date('Y-m-d H:i:s')
		));
		
		// delete client request
		if(!$this->TblMstepClientRequest->save()){
			$res['message']=__('Cannot delete this Client request, please try again', true);
			Output::__outputNo($res);
		}
		
		// delete success
		$res['message']=__('Request has been deleted success',true);
		Output::__outputYes($res);
	}
}