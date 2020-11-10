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
 * @modified 2020. 1. 28.
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
						width:140,
						editable:false,
						displayField:"title",
						valueField:"module",
						value:"",
						matchFieldWidth:false,
						listConfig:{
							minWidth:180
						},
						listeners:{
							change:function(form,value) {
								Ext.getCmp("ModulePushModuleCodeList").getStore().getProxy().setExtraParam("module",value);
								Ext.getCmp("ModulePushModuleCodeList").getStore().reload();
								Ext.getCmp("ModulePushList").getStore().getProxy().setExtraParam("module",value);
							}
						}
					}),
					new Ext.form.ComboBox({
						id:"ModulePushModuleCodeList",
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								url:ENV.getProcessUrl("push","@getCodes"),
								extraParams:{is_all:"true"},
								reader:{type:"json"}
							},
							autoLoad:true,
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							fields:["idx","code",{name:"sort",type:"int"}],
							listeners:{
								load:function(store) {
									var index = store.findExact("code",Ext.getCmp("ModulePushModuleCodeList").getValue());
									if (store.findExact("code",Ext.getCmp("ModulePushModuleCodeList").getValue()) === -1) {
										Ext.getCmp("ModulePushModuleCodeList").setValue("");
									} else {
										Ext.getCmp("ModulePushModuleCodeList").setValue(store.getAt(index).get("code"));
										Ext.getCmp("ModulePushList").getStore().loadPage(1);
									}
								}
							}
						}),
						width:200,
						editable:false,
						displayField:"title",
						valueField:"code",
						matchFieldWidth:false,
						listConfig:{
							minWidth:200
						},
						value:"",
						listeners:{
							change:function(form,value) {
								Ext.getCmp("ModulePushList").getStore().getProxy().setExtraParam("code",value);
								Ext.getCmp("ModulePushList").getStore().loadPage(1);
							}
						}
					}),
					Admin.searchField("ModulePushKeyword",160,"수신자",function(keyword) {
						Ext.getCmp("ModulePushList").getStore().getProxy().setExtraParam("keyword",keyword);
						Ext.getCmp("ModulePushList").getStore().loadPage(1);
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
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
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
						$.send(ENV.getProcessUrl("push","@getView"),{module:record.data.module,type:record.data.type,idx:record.data.idx},function(result) {
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
			}),
			new Ext.grid.Panel({
				id:"ModulePushTypeList",
				title:Push.getText("admin/type/title"),
				iconCls:"xi xi-postbox",
				border:false,
				tbar:[
					Admin.searchField("ModulePushTypeKeyword",200,"알림명",function(keyword) {
						Ext.getCmp("ModulePushTypeList").getStore().clearFilter();
						
						if (keyword.length > 0) {
							Ext.getCmp("ModulePushTypeList").getStore().filter(function(record) {
								var filter = (record.data.title != null && record.data.title.toString().indexOf(keyword) > -1);
								return filter;
							});
						}
					})
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:ENV.getProcessUrl("push","@getPushTypes"),
						reader:{type:"json"}
					},
					remoteSort:true,
					sorters:[{property:"reg_date",direction:"DESC"}],
					groupField:"group",
					autoLoad:true,
					pageSize:50,
					fields:[""],
					listeners:{
						load:function(store,records,success,e) {
							if (success == false) {
								if (e.getError()) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							}
						}
					}
				}),
				columns:[{
					text:Push.getText("admin/type/columns/title"),
					width:250,
					dataIndex:"title"
				},{
					text:Push.getText("admin/type/columns/web"),
					width:80,
					dataIndex:"settings",
					align:"center",
					renderer:function(value,p) {
						var setting = value.web;
						
						if (setting == true) {
							p.style = "color:blue;";
							return Push.getText("admin/type/settings/active");
						} else if (setting == false) {
							p.style = "color:gray;";
							return Push.getText("admin/type/settings/deactive");
						} else {
							p.style = "color:red;";
							return Push.getText("admin/type/settings/disable");
						}
					}
				},{
					text:Push.getText("admin/type/columns/email"),
					width:80,
					dataIndex:"settings",
					align:"center",
					renderer:function(value,p) {
						var setting = value.email;
						
						if (setting == true) {
							p.style = "color:blue;";
							return Push.getText("admin/type/settings/active");
						} else if (setting == false) {
							p.style = "color:gray;";
							return Push.getText("admin/type/settings/deactive");
						} else {
							p.style = "color:red;";
							return Push.getText("admin/type/settings/disable");
						}
					}
				},{
					text:Push.getText("admin/type/columns/mobile"),
					width:80,
					dataIndex:"settings",
					align:"center",
					renderer:function(value,p) {
						var setting = value.mobile;
						
						if (setting == true) {
							p.style = "color:blue;";
							return Push.getText("admin/type/settings/active");
						} else if (setting == false) {
							p.style = "color:gray;";
							return Push.getText("admin/type/settings/deactive");
						} else {
							p.style = "color:red;";
							return Push.getText("admin/type/settings/disable");
						}
					}
				},{
					text:Push.getText("admin/type/columns/latest"),
					width:140,
					dataIndex:"latest",
					align:"center",
					renderer:function(value) {
						if (value == null) return "-";
						return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
					}
				},{
					text:Push.getText("admin/type/columns/latest_message"),
					minWidth:200,
					flex:1,
					dataIndex:"latest_message",
					renderer:function(value,p,record) {
						if (value == null) return "";
						
						var sHTML = "";
						if (value.icon) {
							sHTML+= '<i style="display:inline-block; width:26px; height:26px; vertical-align:middle; background:url('+value.icon+') no-repeat 50% 50%; background-size:cover; border-radius:50%; border:1px solid #ccc; box-sizing:border-box; margin:-4px 5px -3px -5px;"></i>';
						} else {
							sHTML+= '<i style="display:inline-block; width:26px; height:26px; vertical-align:middle; border-radius:50%; border:1px solid #ccc; box-sizing:border-box; margin:-4px 5px -3px -5px; line-height:24px; color:#666; text-align:center;"><i class="mi mi-push"></i></i>';
						}
						
						sHTML+= value.message;
						
						return sHTML;
					}
				}],
				features:[{
					ftype:"grouping",
					groupHeaderTpl:"{name}",
					hideGroupedHeader:false,
					enableGroupingMenu:false
				}],
				bbar:[
					new Ext.Button({
						iconCls:"x-tbar-loading",
						handler:function() {
							Ext.getCmp("ModulePushTypeList").getStore().reload();
						}
					})
				]
			})
		]
	})
); });
</script>