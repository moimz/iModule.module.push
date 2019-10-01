<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모듈목록을 가져온다.
 * 
 * @file /modules/push/process/@getPushes.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 10. 2.
 */
if (defined('__IM__') == false) exit;

$module = Request('module');
$code = Request('code');
$keyword = Request('keyword');

$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir');

$mMember = $this->IM->getModule('member');
$lists = $this->db()->select($this->table->push.' p','p.*')->join($mMember->getTable('member').' m','m.idx=p.midx','LEFT');
if ($module) $lists->where('p.module',$module);
if ($code) $lists->where('p.code',$code);
if ($keyword) $lists->where('(m.name LIKE ? or m.nickname LIKE ?)',array('%'.$keyword.'%','%'.$keyword.'%'));
$total = $lists->copy()->count();
$lists = $lists->orderBy('reg_date','desc')->limit($start,$limit)->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	$mModule = $this->IM->getModule($lists[$i]->module);
	$lists[$i]->module_title = $this->getModule()->getTitle($lists[$i]->module);
	$lists[$i]->code_title = $lists[$i]->code;
	
	if (method_exists($mModule,'syncPush') == true) {
		$code = $mModule->syncPush('title',$lists[$i]->code);
		if ($code != null) $lists[$i]->code_title = $code;
		
		$message = $this->getPushMessage($lists[$i]->module,$lists[$i]->code,$lists[$i]->contents);
		$lists[$i]->icon = $message->icon;
		$lists[$i]->message = $message->message;
	}
	
	$member = $this->IM->getModule('member')->getMember($lists[$i]->midx);
	$lists[$i]->photo = $member->photo;
	$lists[$i]->receiver = $member->name;
}

$results->success = true;
$results->lists = $lists;
$results->total = $total;
?>