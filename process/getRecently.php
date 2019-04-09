<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 확인하지 않은 알림 갯수를 가져온다.
 * 
 * @file /modules/push/process/getRecently.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 3. 2.
 */
if (defined('__IM__') == false) exit;

$count = Request('count') ? Request('count') : 20;
$midx = $this->IM->getModule('member')->getLogged();

if ($midx == 0) {
	$results->success = false;
	$results->message = $this->getErrorText('REQUIRED_LOGIN');
	$results->count = 0;
	return;
}

$lists = $this->db()->select($this->table->push)->where('midx',$midx)->orderBy('reg_date','desc')->limit($count)->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	$message = $this->getPushMessage($lists[$i]->module,$lists[$i]->code,$lists[$i]->contents);
	$lists[$i]->message = $message->message;
	$lists[$i]->icon = $message->icon;
	$lists[$i]->is_checked = $lists[$i]->is_checked == 'TRUE';
	$lists[$i]->is_readed = $lists[$i]->is_readed == 'TRUE';
	
	$this->checkPush($lists[$i]->module,$lists[$i]->type,$lists[$i]->idx,$lists[$i]->code);
}

if (count($lists) == 0) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	$results->count = 0;
	return;
}

$results->success = true;
$results->lists = $lists;
$results->count = $this->getPushCount('UNCHECKED');
?>