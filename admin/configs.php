<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 알림모듈 설정을 위한 설정폼을 생성한다.
 * 
 * @file /modules/push/admin/configs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2019. 4. 1.
 */
if (defined('__IM__') == false) exit;
?>
<script>
new Ext.form.Panel({
	id:"ModuleConfigForm",
	border:false,
	bodyPadding:"10 10 5 10",
	width:500,
	fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:true},
	items:[
		new Ext.form.FieldSet({
			title:Push.getText("admin/configs/form/default_setting"),
			items:[
				Admin.templetField(Push.getText("admin/configs/form/templet"),"templet","module","push",false)
			]
		})
	]
});
</script>