<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모듈목록을 가져온다.
 * 
 * @file /modules/push/process/@getModules.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 4. 16.
 */
if (defined('__IM__') == false) exit;

$modules = $this->getModule()->getModules();
$lists = array();

if (Request('is_all') == 'true') {
	$lists[] = array('module'=>'','title'=>'전체보기');
}

foreach ($modules as $module) {
	$mModule = $this->IM->getModule($module->module);
	if (method_exists($mModule,'syncPush') == true) {
		$lists[] = array('module'=>$module->module,'title'=>$mModule->getModule()->getTitle());
	}
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>