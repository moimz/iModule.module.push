<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 알림 기본템플릿 - 알림설정
 * 
 * @file /modules/push/templets/default/setting.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 4. 1.
 */
if (defined('__IM__') == false) exit;

$IM->loadWebFont('XEIcon');

$date = null;
?>
<div data-role="toolbar">
	<button type="button" onclick="Push.readAll();"><i class="xi xi-check-boxout"></i><span>모두 읽음상태로 변경</span></a>
</div>

<ul data-role="list">
	<?php foreach ($lists as $item) { ?>
	<li>
		<button type="button" data-action="view" data-module="<?php echo $item->module; ?>" data-type="<?php echo $item->type; ?>" data-idx="<?php echo $item->idx; ?>"<?php echo $item->is_readed == false ? ' class="unread"' : ''; ?>>
			<i class="icon" style="background-image: url(<?php echo $item->icon; ?>);"></i>
			<div class="text"><?php echo $item->message; ?><time data-time="<?php echo $item->reg_date; ?>" data-moment="fromNow"></time></div>
		</button>
	</li>
	<?php } ?>
</ul>

<div class="pagination">
	<?php echo $pagination; ?>
</div>