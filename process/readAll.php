<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모든 알림을 읽음상태로 변경한다.
 * 
 * @file /modules/push/process/readAll.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 4. 10.
 */
if (defined('__IM__') == false) exit;

if ($this->IM->getModule('member')->isLogged() == false) {
	$results->success = false;
	$results->message = $this->getErrorText('REQUIRED_LOGIN');
	return;
}

$this->db()->update($this->table->push,array('is_checked'=>'TRUE','is_readed'=>'TRUE'))->where('midx',$this->IM->getModule('member')->getLogged())->execute();
$results->success = true;
?>