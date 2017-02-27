<?php
/*
 * Copyright 2015 SPC Viet Nam Co., Ltd.
 * All right reserved.
 */

/**
 * @Author: Nguyen Hoai Duc
 * @Date:   2016-08-17 14:28:44
 * @Last Modified by:   Nguyen Hoai Duc
 * @Last Modified time: 2016-11-16 10:15:15
 */

App::uses('AuthComponent', 'Controller/Component');

/**
 * Users Controller
 *
 */
class UsersController extends AppController {
	var $name = 'Users';
	var $uses = array('TblMstepMasterUser');

	public function beforeFilter() {

		$this->Auth->loginError='';
		
		parent::beforeFilter();
	}

	/**
	 * Determines if authorized.
	 *
	 * @param      <type>   $user   The user
	 *
	 * @return     boolean  True if authorized, False otherwise.
	 */
	public function isAuthorized($user) {

		// All registered users can logout
		if ($this->action === 'logout') {
			return true;
		}

		return parent::isAuthorized($user);
	}

	public function login() {
		$this->layout='login';
		//if already logged-in, redirect
		if ($this->Session->check('Auth.User')) {
			$this->redirect($this->Auth->redirectUrl());
		}

		// if we get the post information, try to authenticate
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				// check Allow ip address
				$ip_address=$this->request->clientIp();
				if(!empty(ALLOW_IP_ADRR[$this->Auth->user('authority')])){
					$allow_address=ALLOW_IP_ADRR[$this->Auth->user('authority')];
					if(!is_array($allow_address)){
						$allow_address=preg_split('/[,;]/', $allow_address);
					}
					if(!in_array($ip_address, $allow_address)){
//						throw new ForbiddenException("You don't have permission to access from ip address: ".$ip_address);
						$this->Session->setFlash("You don't have permission to access from ip address: ".$ip_address);
						$this->logout();
						die;
					}
				}
				
				file_put_contents(LOGS . 'logged.log', 'Last logged: '.date('D M dS Y H:i:s')." from ".$ip_address);
				// allow access
				$this->redirect($this->Auth->redirectUrl());
			} else {
				$this->Session->setFlash(__('Email address or password is wrong.'));
			}
		}
		
		$last_logged=file_get_contents(LOGS . 'logged.log');
		$this->set('last_logged',$last_logged);
	}

	public function logout() {
		if ($this->Auth->logout()) {
			$this->redirect(array('controller' => 'users', 'action' => 'login'));
		}
	}
}
