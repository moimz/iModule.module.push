/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 홈페이지 내 각종 알림기능과 관련된 전반적인 기능을 관리한다.
 * 
 * @file /modules/push/scripts/script.js
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 4. 1.
 */
var Push = {
	midx:0,
	latestCount:null,
	isCheckerStarted:false,
	init:function(id) {
		var $form = $("#"+id);
		if (id == "ModulePushSettingForm") {
			$form.inits(Push.updateSetting);
		}
		
		if (id == "ModulePushListForm") {
			$("button[data-action=view]",$form).on("click",function() {
				var $button = $(this);
				$.send(ENV.getProcessUrl("push","getView"),{module:$button.attr("data-module"),type:$button.attr("data-type"),idx:$button.attr("data-idx")},function(result) {
					if (result.success == true) {
						if (result.view) {
							if (ENV.IS_CONTAINER_POPUP == true) {
								if (window.opener !== undefined) {
									window.opener.location.href = result.view
								} else {
									window.open(result.view);
								}
							} else {
								location.href = result.view;
							}
						}
						
						$button.removeClass("unread");
					}
				});
			});
		}
	},
	/**
	 * 확인하지 않은 알림갯수를 가져온다.
	 */
	getCount:function() {
		$.send(ENV.getProcessUrl("push","getCount"),function(result) {
			if (result.success == true) {
				Push.midx = result.midx;
				Push.updateBadge(result.count);
				
				if (result.midx > 0 && result.interval > 0 && Push.isCheckerStarted == false) {
					Push.checker(result.interval * 1000);
				}
			}
		});
	},
	/**
	 * 알림뱃지수를 업데이트한다.
	 */
	updateBadge:function(count) {
		if (Push.latestCount != null && Push.latestCount < count) {
			// @todo playSound
		}
		
		Push.latestCount = count;
		
		if (count > 0) {
			$("*[data-module=push][data-role=count]").html(count);
		} else {
			$("*[data-module=push][data-role=count]").empty();
		}
	},
	/**
	 * 알림설정을 저장한다.
	 */
	updateSetting:function($form) {
		$form.send(ENV.getProcessUrl("push","saveSetting"),function(result) {
			if (result.success == true) {
				iModule.modal.alert(iModule.getText("text/info"),iModule.getText("action/saved"),function() {
					if (ENV.IS_CONTAINER_POPUP == true) self.close();
				});
			}
		});
	},
	/**
	 * 새로운 알림갯수를 가져온다.
	 */
	checker:function(interval) {
		setTimeout(Push.getCount,interval);
	},
	/**
	 * 최근 알림메세지를 가져온다.
	 */
	getRecently:function(count,callback) {
		$.send(ENV.getProcessUrl("push","getRecently"),{count:count},function(result) {
			callback(result);
			Push.updateBadge(result.count);
			return false;
		});
	},
	/**
	 * 알림메세지를 확인한다.
	 */
	view:function(module,type,idx,callback) {
		$.send(ENV.getProcessUrl("push","getView"),{module:module,type:type,idx:idx},function(result) {
			if (result.success == true) {
				if (result.view) location.href = result.view;
				else if (typeof callback == "function") callback(result);
			}
		});
	},
	/**
	 * 알림설정창을 불러온다.
	 */
	settingPopup:function() {
		iModule.openPopup(ENV.getModuleUrl("push","@setting"),460,600,1,"setting");
	},
	/**
	 * 모든 알림보기 창을 불러온다.
	 */
	listPopup:function() {
		iModule.openPopup(ENV.getModuleUrl("push","@list"),460,600,1,"list");
	},
	readAll:function() {
		$.send(ENV.getProcessUrl("push","readAll"),function(result) {
			if (result.success == true) {
				if (typeof callback == "function") callback();
				
				$("button.unread",$("*[data-module=push]")).removeClass("unread");
				Push.getCount();
			}
		});
	}
};

$(document).ready(function() {
	Push.getCount(); 
});