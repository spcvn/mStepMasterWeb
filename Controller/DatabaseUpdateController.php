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
		'TblMstepClientRequest',
		'TblMstepWorkerPrice',
		'TblMstepSiteWorker',
		'TblMstepSiteSchedule',
		'TblMstepWorker',
	];
	var $name = 'DatabaseUpdate';
	
	public function index() {
		
		$this->page_title = __('DatabaseUpdate');
	}
	
	public function applySQL() {
		if ($this->request->is("post")) {
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
				if ($conn = @mysqli_connect($client['Clients']['db_host'], $client['Clients']['db_user'], $client['Clients']['db_password'], $client['Clients']['db_name'], $client['Clients']['db_port'])) {
					@mysqli_multi_query($conn, $sql);
					@mysqli_close($conn);
				}
				@ini_set('max_execution_time', @ini_get("max_execution_time") + 10);
			}
			
			Configure::write('debug', $old_config_debug);
			
			// write log
			$str_log = "\n";
			$str_log .= "Author: " . $this->Auth->user('first_name') . $this->Auth->user('last_name');
			$str_log .= "\n";
			$str_log .= "Update type: alter table query";
			$str_log .= "\n";
			$str_log .= "SQL statement: " . $sql;
			$str_log .= "\n";
			CakeLog::write('sql', $str_log);
			// End write log
			
			$res['status'] = "YES";
			$res['message'] = __("SQL is applied");
			Output::__output($res);
			//}
		}
	}
	
	/**
	 * Update worker price of all Client with data that from Site Worker table (tbl_mstep_site_worker)
	 *
	 * @author Edward <ducnguyen1504@gmail.com>
	 * @date   2017-03-23
	 */
	public function update_worker_price() {
		// get Client connection config
		
		$clients = $this->Clients->find('all');
		foreach ($clients as $key => $client) {
			$client_detail = $client['Clients'];
			// define new config
			$new_config = array(
				"datasource" => "Database/Mysql",
				'driver' => 'mysql',
				'persistent' => false,
				'host' => $client_detail['db_host'],
				'login' => $client_detail['db_user'],
				'password' => $client_detail['db_password'],
				'database' => $client_detail['db_name'],
				'encoding' => 'utf8'
			);
			// effect new connection
			$config_name = $new_config['database'];
			ClassRegistry::init('ConnectionManager');
			ConnectionManager::create($config_name, $new_config);
			
			$this->TblMstepWorkerPrice->setDataSource($config_name);
			$this->TblMstepSiteWorker->setDataSource($config_name);
			$this->TblMstepSiteSchedule->setDataSource($config_name);
			$this->TblMstepWorker->setDataSource($config_name);
			// get Site Worker
			
			$site_worker = $this->TblMstepSiteWorker->find('all', array(
				'order' => 'TblMstepSiteSchedule.start_date ASC'
			));
			// data of TblMstepWorkerPrice
			$dbWorkerPrice=array();
			// using to detect price of worker does not same
			$existing_price=array();
			// id of TblMstepWorkerPrice, start from 1
			$id=1;
			
			foreach ($site_worker as $sw) {
				$new_key = $sw['TblMstepSiteWorker']['worker_id'] . $sw['TblMstepSiteWorker']['price'];
				
				// detect same price of worker. Just using older content
				
				if (in_array($new_key, $existing_price)) {
					continue;
				}
				
				// record worker have a price
				$existing_price[] = $new_key;
				
				// update data of TblMstepWorkerPrice
				$dbWorkerPrice[]['TblMstepWorkerPrice'] = array(
					'id' => $id++,
					'worker_id' => $sw['TblMstepSiteWorker']['worker_id'],
					'date' => date("Y-m-d", strtotime($sw['TblMstepSiteSchedule']['start_date'])),
					'price' => $sw['TblMstepSiteWorker']['price'],
					'update_date' => date('Y-m-d H:i:s'),
					'del_flg' => 0,
				);
			}
			
			// get price from worker
			$workers = $this->TblMstepWorker->find('all', array(
				'fields' => 'TblMstepWorker.id,TblMstepWorker.price,TblMstepWorker.modified'
			));
			
			foreach ($workers as $worker) {
				$new_key = $worker['TblMstepWorker']['id'] . $worker['TblMstepWorker']['price'];
				
				// detect same price of worker. Just using older content
				if (in_array($new_key, $existing_price)) {
					continue;
				}
				
				// record worker have a price
				$existing_price[] = $new_key;
				
				// update data of TblMstepWorkerPrice
				$dbWorkerPrice[]['TblMstepWorkerPrice'] = array(
					'id' => $id++,
					'worker_id' => $worker['TblMstepWorker']['id'],
					'date' => date("Y-m-d", strtotime($worker['TblMstepWorker']['modified'])),
					'price' => $worker['TblMstepWorker']['price'],
					'update_date' => date('Y-m-d H:i:s'),
					'del_flg' => 0,
				);
			}
			
			// Just update if $dbWorkerPrice contain data
			if (sizeof($dbWorkerPrice) > 0) {
				var_dump($this->TblMstepWorkerPrice->saveAll($dbWorkerPrice));
			}
			//var_dump($dbWorkerPrice);
			//exit;
		}
		
		exit;
	}
}