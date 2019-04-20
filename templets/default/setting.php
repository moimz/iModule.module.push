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
 * @modified 2019. 4. 20.
 */
if (defined('__IM__') == false) exit;

$IM->loadWebFont('XEIcon');
$previous = null;
?>
<ul data-role="table" class="black inner">
	<li class="thead">
		<span class="type">알림종류</span>
		<span class="method">
			<div>웹</div>
<!--
			<div data-role="input">
				<label><input type="checkbox" name="is_web_all"></label>
			</div>
-->
		</span>
		<span class="method">
			<div>모바일</div>
<!--
			<div data-role="input">
				<label><input type="checkbox" name="is_web_all"></label>
			</div>
-->
		</span>
		<span class="method">
			<div>이메일</div>
<!--
			<div data-role="input">
				<label><input type="checkbox" name="is_web_all"></label>
			</div>
-->
		</span>
	</li>
	<?php foreach ($pushes as $push) { $latest = $me->getLatestMessage($push->module,$push->code); ?>
	<?php if ($previous == null || $previous != $push->module.'@'.$push->group) { $previous = $push->module.'@'.$push->group; ?>
	<li class="title">
		<span><?php echo $push->group; ?></span>
	</li>
	<?php } ?>
	<li class="tbody">
		<span class="type">
			<?php echo $push->title; ?>
			<?php if ($latest !== null) { ?>
			<div class="latest">
				<i style="background-image:url(<?php echo $latest->icon; ?>);"></i>
				<span><?php echo $latest->message; ?></span>
				<time data-moment="fromNow" data-time="<?php echo $latest->reg_date; ?>"></time>
			</div>
			<?php } ?>
		</span>
		<span class="method">
			<div data-role="input">
				<label><input type="checkbox" name="<?php echo $push->key.'@web'; ?>" value="TRUE"<?php echo $push->settings->web === true ? ' checked="checked"' : ''; ?><?php echo $push->settings->web === null ? ' disabled="disabled"' : ''; ?>></label>
			</div>
		</span>
		<span class="method">
			<div data-role="input">
				<label><input type="checkbox" name="<?php echo $push->key.'@sms'; ?>" value="TRUE"<?php echo $push->settings->sms === true ? ' checked="checked"' : ''; ?><?php echo $push->settings->sms === null ? ' disabled="disabled"' : ''; ?>></label>
			</div>
		</span>
		<span class="method">
			<div data-role="input">
				<label><input type="checkbox" name="<?php echo $push->key.'@email'; ?>" value="TRUE"<?php echo $push->settings->email === true ? ' checked="checked"' : ''; ?><?php echo $push->settings->email === null ? ' disabled="disabled"' : ''; ?>></label>
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