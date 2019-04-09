<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 알림메시지 확인 URL 을 가져온다.
 * 
 * @file /modules/push/process/getView.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 3. 2.
 */
if (defined('__IM__') == false) exit;

$module = Param('module');
$type = Param('type');
$idx = Param('idx');
$midx = $this->IM->getModule('member')->getLogged();

if ($midx == 0) {
	$results->success = false;
	$results->message = $this->getErrorText('REQUIRED_LOGIN');
	return;
}

$view = $this->getPushView($module,$type,$idx);

$results->success = true;
$results->view = $view;
?>