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
class TblMstepSiteWorker extends AppModel {

	var $name = "TblMstepSiteWorker";
	var $useTable = "tbl_mstep_site_workers";
	var $primaryKey = "id";
	var $belongsTo=array(
		'TblMstepSiteSchedule'=>array(
			'className'=>'TblMstepSiteSchedule',
			'foreignKey'=>'schedule_id',
		)
	);
}
