<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 알림설정을 저장한다.
 * 
 * @file /modules/push/process/saveSetting.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 4. 1.
 */
if (defined('__IM__') == false) exit;

if ($this->IM->getModule('member')->isLogged() == false) {
	$results->success = false;
	$results->error = $this->getErrorText('REQUIRED_LOGIN');
	return;
}

$pushes = array();
$modules = $this->getModule()->getModules();
foreach ($modules as $module) {
	$mModule = $this->IM->getModule($module->module);
	if (method_exists($mModule,'syncPush') == true && is_array($mModule->syncPush('list',null)) === true) {
		$lists = $mModule->syncPush('list',null);
		foreach ($lists as $key=>$title) {
			$push = array();
			$push['midx'] = $this->IM->getModule('member')->getLogged();
			$push['module'] = $module->module;
			$push['code'] = $key;
			$push['web'] = Request($module->module.'@'.$key.'@web') ? 'TRUE' : 'FALSE';
			$push['sms'] = Request($module->module.'@'.$key.'@sms') ? 'TRUE' : 'FALSE';
			$push['email'] = Request($module->module.'@'.$key.'@email') ? 'TRUE' : 'FALSE';
			
			$pushes[] = $push;
		}
	}
}

foreach ($pushes as $push) {
	$this->db()->replace($this->table->setting,$push)->execute();
}

$results->success = true;
?>