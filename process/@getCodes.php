<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모듈내 알림코드를 가져온다.
 * 
 * @file /modules/push/process/@getCodes.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 10. 2.
 */
if (defined('__IM__') == false) exit;

$module = Request('module');
$lists = array();

if (Request('is_all') == 'true') {
	$lists[] = array('code'=>'','title'=>'전체보기','sort'=>-1);
}

if ($module != null) {
	$mModule = $this->IM->getModule($module);
	if (method_exists($mModule,'syncPush') == true && is_array($mModule->syncPush('list',null)) === true) {
		$sort = 0;
		$codes = $mModule->syncPush('list',null);
		foreach ($codes as $key=>$value) {
			$lists[] = array('code'=>$key,'title'=>$value->title,'sort'=>$sort++);
		}
	}
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>