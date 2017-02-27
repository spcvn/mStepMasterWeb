<?php

require_once "tsv.php";
require_once "output.php";
require_once "SetModel.php";

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * @property 	RequestHandlerComponent $RequestHandler
 *
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package     app.Controller
 * @link        http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

	var $ext = ".html";
	var $client_id;
	var $check_authentication;

	public $components = array(
		'Session',
		'Auth' => array(
			'loginRedirect' => array('controller' => 'clients', 'action' => 'index'),
			'logoutRedirect' => array('controller' => 'users', 'action' => 'login'),
			'authenticate' => array(
				'Form' => array(
					'fields' => array('username' => 'login_id',
						'password' => 'login_pass'),
				),
				'all'=>['userModel'=>'TblMstepMasterUser'],
			),
			'authorize' => array('Controller'),
		),
		'Acl',
		'Cookie',
		'RequestHandler'
	);

	function __getLogPath(){

			$controller=$this->params["controller"];
			$action    =$this->params["action"];
			$log_path=LOGS."post".DS."{$controller}_{$action}.txt";
			return $log_path;
	}

	function beforeFilter() {
		$this->__setLanguage();
		
		parent::beforeFilter();
		
		if($this->data){
			
			$post=$this->data;
			$log_path=$this->__getLogPath();
			@file_put_contents($log_path,serialize($post));
		}
		
		if(!defined("UNIQUE_KEY")) define("UNIQUE_KEY",'sample_unique_key');
		
		$this->check_authentication = $this->Auth->user("authority") === "master";
		$this->set('role', $this->check_authentication);
		//Update 2017.02.20 Hung Nguyen start
		// add name for show on header
		$this->set("login_name",($this->Auth->user("first_name").$this->Auth->user("last_name")));
		//Update 2017.02.20 Hung Nguyen end
		
		$this->page_title='';
		$this->page_desc='';
		
		$is_mobile=$this->RequestHandler->isMobile();
		$authority=$this->Auth->user('authority');
		
		$this->allows=array(
			'spc'=>array(
				'Clients'=>array(
					'index'=>true,
					'add'=>(!$is_mobile?true:false),
					'updateStatus'=>(!$is_mobile?true:false),
					'saveData'=>(!$is_mobile?true:false),
					'randomPassword'=>(!$is_mobile?true:false),
					'detail'=>true,
					'checkConnectDB'=>true,
				),
				'ClientRequest'=>array(
					'index'=>(!$is_mobile?true:false),
					'update_status'=>(!$is_mobile?true:false),
					'detail'=>true,
				)
			),
			'mstep'=>array(
				'Clients'=>array(
					'index'=>true,
					'detail'=>true,
					'checkConnectDB'=>true
				),
				'ClientRequest'=>array(
					'index'=>true,
					'edit'=>true,
					'add'=>true,
					'detail'=>true,
					'save_process'=>true,
					'delete'=>true,
				)
			)
		);
		
		$this->set('allows',(empty($this->allows[$authority])?'all':$this->allows[$authority]));
		$this->set('current_page', strtolower($this->params['controller'].$this->params['action']));
	}
	
	function __setLanguage(){
		$this->Session->write('Config.language', Configure::read('Config.language'));
	}

	function __output($res = array()) {

		Configure::write("debug", 0);
		Output::__output($res);
	}

	/**
	 * Determines if authorized.
	 *
	 * @author Nguyen Chat Hien
	 */
	public function isAuthorized($user) {

		// Admin can access every action
		if (isset($user['authority']) && $user['authority'] === 'master' or
			(isset($this->allows[$user['authority']][$this->name][$this->action])
				and $this->allows[$user['authority']][$this->name][$this->action])) {
			return true;
		}
		
		// Default deny
		throw new unAuthorizedException();
	}

	#
	# @author Kiyosawa
	# @date 2011/05/07 14:44:59
	function beforeRender() {
		parent::beforeRender();
		
		if($this->name=="CakeError") {
			$this->layout='error';
		}

		$this->set('page_title',(!empty($this->page_title)?$this->page_title:$this->name));
		$this->set('page_desc',$this->page_desc);
		$this->set('auth', $this->Auth->user());
	}

	function __arrangeAry($data = array(), $key) {

		$res = array();
		foreach ($data as $k => $v) {

			$__data = current($v);
			$res[$__data[$key]] = $__data;
			unset($res[$__data[$key]][$key]);
		}

		return $res;
	}

	function isPrimaryKey($data = array(), $key) {

		$res = array();
		foreach ($data as $k => $v) {

			$__data = current($v);
			$res[$__data[$key]] = $__data;
		}

		return $res;
	}

		function __multiInsert(Model $model,$inserts=array()){

				try{

						$model->multiInsert($inserts);

				}catch(Exception $e){

						$res["status"]=false;
						$res["message"]=$e->getMessage();
						return $res;
				}

				$res["status"]=true;
				return $res;
		}

		function unbindFully(){

				if(empty($this->uses)) return;
				foreach($this->uses as $k=>$model) $this->$model->recursive=-1;
		}

}

?>

