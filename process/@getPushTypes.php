<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 알림분류를 가져온다.
 * 
 * @file /modules/push/process/@getPushTypes.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2020. 1. 28.
 */
if (defined('__IM__') == false) exit;

$lists = array();
$modules = $this->getModule()->getModules();
foreach ($modules as $module) {
	$mModule = $this->IM->getModule($module->module);
	if (method_exists($mModule,'syncPush') == true && is_array($mModule->syncPush('list',null)) === true) {
		$pushes = $mModule->syncPush('list',true);
		foreach ($pushes as $key=>$value) {
			$push = new stdClass();
			$push->module = $module->module;
			$push->code = $key;
			$push->key = $module->module.'@'.$key;
			$push->group = $value->group;
			$push->title = $value->title;
			$push->settings = $this->getDefaultSetting($module->module,$key);
			
			$latest = $this->db()->select($this->table->push)->where('module',$module->module)->where('code',$key)->orderBy('reg_date','desc')->getOne();
			$push->latest = $latest != null ? $latest->reg_date : null;
			$push->latest_message = $latest != null ? $this->getPushMessage($latest->module,$latest->code,$latest->contents) : null;
			
			$lists[] = $push;
		}
	}
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>