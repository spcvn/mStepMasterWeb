<?php
/*
 * Copyright 2015 SPCVN Co., Ltd.
 * All right reserved.
 */

/**
 * @Author: Nguyen Chat Hien
 * @Date:   2016-09-13 14:36:01
 * @Last Modified by:   Nguyen Hoai Duc
 * @Last Modified time: 2016-11-17 15:40:44
 */

App::uses('AppModel', 'Model');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class TblMstepMasterUser extends AppModel {

	var $name = "TblMstepMasterUser";
	var $useTable = "tbl_mstep_master_users";
	var $primaryKey = "id";

	// Update 2016.11.04 Hung Nguyen
	// Add virtual field fullname
	/*var $virtualFields = array(
		'full_name' => 'CONCAT(TblMstepMasterUser.first_name, " ",TblMstepMasterUser.last_name)',
	);*/

	// Update 2016.11.04 Hung Nguyen end

	/**
	 * { function_description }
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function parentNode() {
		if (!$this->id && empty($this->data)) {
			return null;
		}
		if (isset($this->data['TblMstepMasterUser']['group_id'])) {
			$groupId = $this->data['TblMstepMasterUser']['group_id'];
		} else {
			$groupId = $this->field('group_id');
		}
		if (!$groupId) {
			return null;
		} else {
			return array('Group' => array('id' => $groupId));
		}
	}

	// Update 2016.11.10 Hung Nguyen add function for hash password
	public function beforeSave($options = array()) {
		if (!empty($this->data[$this->alias]['login_pass'])) {
			$passwordHasher = new SimplePasswordHasher(array('hashType' => 'sha1'));
			$this->data[$this->alias]['login_pass'] = $passwordHasher->hash(
				$this->data[$this->alias]['login_pass']
			);
		}
		parent::beforeSave($options);
	}

}
