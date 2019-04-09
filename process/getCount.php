<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 확인하지 않은 알림 갯수를 가져온다.
 * 
 * @file /modules/push/process/getCount.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 2. 27.
 */
if (defined('__IM__') == false) exit;

$midx = $this->IM->getModule('member')->getLogged();

$results->success = true;
$results->midx = $midx;
$results->count = $this->getPushCount('UNCHECKED');
$results->interval = 60;
?>