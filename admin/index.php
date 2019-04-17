<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodules.io)
 *
 * 알림모듈 관리자패널을 구성한다.
 * 
 * @file /modules/push/admin/index.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0
 * @modified 2019. 4. 16.
 */
if (defined('__IM__') == false) exit;
?>
<script>
Ext.onReady(function () { Ext.getCmp("iModuleAdminPanel").add(
	new Ext.TabPanel({
		id:"ModulePush",
		border:false,
		tabPosition:"bottom",
		items:[
			new Ext.grid.Panel({
				id:"ModulePushList",
				title:Push.getText("admin/list/title"),
				iconCls:"mi mi-push",
				border:false,
				tbar:[
					new Ext.form.ComboBox({
						id:"ModulePushModuleList",
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								url:ENV.getProcessUrl("push","@getModules"),
								extraParams:{is_all:"true"},
								reader:{type:"json"}
							},
							autoLoad:true,
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							fields:["idx","title",{name:"sort",type:"int"}]
						}),
						width:180,
						editable:false,
						displayField:"title",
						valueField:"module",
						value:"",
						listeners:{
							change:function(form,value) {
								Ext.getCmp("ModulePushList").getStore().getProxy().setExtraParam("module",value);
								Ext.getCmp("ModulePushList").getStore().loadPage(1);
							}
						}
					})
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:ENV.getProcessUrl("push","@getPushes"),
						reader:{type:"json"}
					},
					remoteSort:true,
					sorters:[{property:"reg_date",direction:"DESC"}],
					autoLoad:true,
					pageSize:50,
					fields:[""],
					listeners:{
						load:function(store,records,success,e) {
							if (success == false) {
								if (e.getError()) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getText("error/load"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							}
						}
					}
				}),
				columns:[{
					text:Push.getText("admin/list/columns/module"),
					width:120,
					dataIndex:"module_title"
				},{
					text:Push.getText("admin/list/columns/code"),
					width:160,
					dataIndex:"code_title"
				},{
					text:Push.getText("admin/list/columns/message"),
					minWidth:200,
					flex:1,
					dataIndex:"message",
					renderer:function(value,p,record) {
						var sHTML = "";
						if (record.data.icon) {
							sHTML+= '<i style="display:inline-block; width:26px; height:26px; vertical-align:middle; background:url('+record.data.icon+') no-repeat 50% 50%; background-size:cover; border-radius:50%; border:1px solid #ccc; box-sizing:border-box; margin:-4px 5px -3px -5px;"></i>';
						} else {
							sHTML+= '<i style="display:inline-block; width:26px; height:26px; vertical-align:middle; border-radius:50%; border:1px solid #ccc; box-sizing:border-box; margin:-4px 5px -3px -5px; line-height:24px; color:#666; text-align:center;"><i class="mi mi-push"></i></i>';
						}
						
						sHTML+= value;
						
						return sHTML;
					}
				},{
					text:Push.getText("admin/list/columns/receiver"),
					width:140,
					dataIndex:"receiver",
					renderer:function(value,p,record) {
						return '<i style="display:inline-block; width:26px; height:26px; vertical-align:middle; background:url('+record.data.photo+') no-repeat 50% 50%; background-size:cover; border-radius:50%; border:1px solid #ccc; box-sizing:border-box; margin:-4px 5px -3px -5px;"></i>' + value;
					}
				},{
					text:Push.getText("admin/list/columns/reg_date"),
					width:140,
					dataIndex:"reg_date",
					renderer:function(value) {
						return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
					}
				}],
				selModel:new Ext.selection.CheckboxModel(),
				bbar:new Ext.PagingToolbar({
					store:null,
					displayInfo:false,
					items:[
						"->",
						{xtype:"tbtext",text:"항목 더블클릭 : 알림 URL로 이동 / 항목 우클릭 : 상세메뉴"}
					],
					listeners:{
						beforerender:function(tool) {
							tool.bindStore(Ext.getCmp("ModulePushList").getStore());
						}
					}
				}),
				listeners:{
					itemdblclick:function(grid,record) {
						$.send(ENV.getProcessUrl("push","getView"),{module:record.data.module,type:record.data.type,idx:record.data.idx},function(result) {
							if (result.success == true) {
								if (result.view) window.open(result.view);
								else Ext.Msg.show({title:Admin.getText("alert/error"),msg:"이동할 URL주소가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							}
						});
					},
					itemcontextmenu:function(grid,record,item,index,e) {
						var menu = new Ext.menu.Menu();
						
						menu.add('<div class="x-menu-title">'+record.data.message+'</div>');
						
						menu.add({
							iconCls:"xi xi-form",
							text:"알림대상 URL로 이동",
							handler:function() {
								Push.list.view(record.data.bid);
							}
						});
						
						menu.add({
							iconCls:"mi mi-trash",
							text:"알림삭제",
							handler:function() {
								Push.list.delete();
							}
						});
						
						e.stopEvent();
						menu.showAt(e.getXY());
					}
				}
			})
		]
	})
); });
</script>