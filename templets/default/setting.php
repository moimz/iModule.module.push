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
?>
<ul data-role="table" class="black inner">
	<li class="thead">
		<span class="type">알림종류</span>
		<span class="method"><i class="xi xi-monitor"></i></span>
		<span class="method"><i class="xi xi-mobile"></i></span>
		<span class="method"><i class="xi xi-envelope"></i></span>
	</li>
	<?php foreach ($pushes as $push) { ?>
	<li class="tbody">
		<span class="type"><?php echo $push->title; ?></span>
		<span class="method">
			<div data-role="input">
				<label><input type="checkbox" name="<?php echo $push->key.'@web'; ?>" value="TRUE"<?php echo $push->settings->web == true ? ' checked="checked"' : ''; ?>></label>
			</div>
		</span>
		<span class="method">
			<div data-role="input">
				<label><input type="checkbox" name="<?php echo $push->key.'@sms'; ?>" value="TRUE"<?php echo $push->settings->sms == true ? ' checked="checked"' : ''; ?>></label>
			</div>
		</span>
		<span class="method">
			<div data-role="input">
				<label><input type="checkbox" name="<?php echo $push->key.'@email'; ?>" value="TRUE"<?php echo $push->settings->email == true ? ' checked="checked"' : ''; ?>></label>
			</div>
		</span>
	</li>
	<?php } ?>
</ul>

<div data-role="button">
	<button type="submit">저장</button>
	<?php if (defined('__IM_CONTAINER_POPUP__') == true) { ?>
	<button type="button" onclick="self.close();">취소</button>
	<?php } ?>
</div>