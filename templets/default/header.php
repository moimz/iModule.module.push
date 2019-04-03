<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 알림 기본템플릿 - 헤더
 *
 * @file /modules/push/templets/default/header.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 4. 1.
 */
if (defined('__IM__') == false) exit;
if (defined('__IM_CONTAINER__') == true) {
	$IM->addHeadResource('style',$me->getTemplet()->getDir().'/styles/container.css');
	$container = explode('/',$IM->container);
	
	if (defined('__IM_CONTAINER_POPUP__') == true) {
?>
<header>
	<div class="container">
		<h1><?php echo $me->getContextTitle(end($container)); ?></h1>
		<button type="button" onclick="self.close();"><i class="mi mi-close"></i></button>
	</div>
</header>
<?php } ?>
<div class="container context">
<?php } ?>