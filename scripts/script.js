/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 홈페이지 내 각종 알림기능과 관련된 전반적인 기능을 관리한다.
 * 
 * @file /modules/push/scripts/script.js
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 3. 2.
 */
var Push = {
	midx:0,
	latestCount:null,
	isCheckerStarted:false,
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
	readAll:function(e) {
		$.ajax({
			type:"POST",
			url:ENV.getProcessUrl("push","readAll"),
			data:{},
			dataType:"json",
			success:function(result) {
				if (result.success == true) {
					$("*[data-push=true]").addClass("readed").removeClass("unread");
					$("*[data-push-badge=true]").html("0");
				}
			}
		});
		
		if (e) e.stopPropagation();
	}
};

$(document).ready(function() {
	Push.getCount(); 
});