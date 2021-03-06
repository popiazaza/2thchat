var nzchatobj = jQuery.noConflict();

var nzsid = getcookie('sid', true);
var nztime1 = new Date().getTime();
var nztime2 = 0;
var nztouid = 0;
var nzquota = 0;
var nzlastid = 0;
var nzonol = false;
var nzcommandz = '';
var formhash = '';
var nzChatPopupContent = '';
var nzscroll = true;
var nzChatRoom = 0;
var nzChatList = 0;
var nzInterval;

function nzolover() {
	nzonol = true;
}

function nzolout() {
	nzonol = false;
}

function nzalert(text) {
	nzchatobj('#nzalertbox').text(text);
	nzchatobj('#nzalertbox').slideDown(200);
	setTimeout(function () {
		nzchatobj('#nzalertbox').slideUp(200);
	}, 2000);
}

nzchatobj.ajaxSetup({
	timeout: 2000,
	error: function (jqXHR, textStatus, errorThrown) {
		nzalert('ขาดเชื่อมต่อกับเซิฟเวอร์ กำลังลองใหม่...');
		nzResetInterval();
	}
});

nzchatobj(function () {
	nzchatobj("#nzchatmessage").keydown(function (event) {
		if (event.keyCode == '13') {
			nzSend();
		}
		if (event.keyCode == '27') {
			nzTouid(0);
		}
	});
	nzchatobj('#nzchatmessage').bind('paste', function (e) {
		if (e.originalEvent.clipboardData.files.length !== 1) {
			return;
		}
		nzchatobj('#nzimguploadl').text('กำลังอัปโหลด...');
		nzchatobj('#nzimgupload').prop('disabled', true);
		var nzFormData = new FormData();
		nzFormData.append("pictures", e.originalEvent.clipboardData.files[0]);
		nzchatobj('#nzimgupload').val('');
		nzchatobj.ajax({
			url: 'plugin.php?id=th_chat:img',
			type: 'POST',
			data: nzFormData,
			cache: false,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: function (data, textStatus, jqXHR) {
				if (typeof data.error === 'undefined') {
					seditor_insertunit('nzchat', '[img]' + data.url + '[/img]', '');
					nzchatobj("#nzchatmessage").focus();
				} else {
					nzalert(data.error);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				nzalert('เกิดข้อผิดพลาด: ' + textStatus);
			},
			complete: function (jqXHR, textStatus, errorThrown) {
				nzchatobj('#nzimguploadl').text('อัปโหลดไฟล์ภาพ');
				nzchatobj('#nzimgupload').prop('disabled', false);
			}
		});
	});
	nzchatobj('#nzimgup').click(function () {
		nzchatobj('#nzimgupload').click();
	});
	nzchatobj('#nzimgupload').change(function () {
		if (nzchatobj('#nzimgupload').val()) {
			nzchatobj('#nzimguploadl').text('กำลังอัปโหลด...');
			nzchatobj('#nzimgupload').prop('disabled', true);
			var nzFormData = new FormData();
			nzFormData.append("pictures", nzchatobj('#nzimgupload').prop('files')[0]);
			nzchatobj('#nzimgupload').val('');
			nzchatobj.ajax({
				url: 'plugin.php?id=th_chat:img',
				type: 'POST',
				data: nzFormData,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false,
				success: function (data, textStatus, jqXHR) {
					if (typeof data.error === 'undefined') {
						seditor_insertunit('nzchat', '[img]' + data.url + '[/img]', '');
						nzchatobj("#nzchatmessage").focus();
					} else {
						nzalert(data.error);
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					nzalert('เกิดข้อผิดพลาด: ' + textStatus);
				},
				complete: function (jqXHR, textStatus, errorThrown) {
					nzchatobj('#nzimguploadl').text('อัปโหลดไฟล์ภาพ');
					nzchatobj('#nzimgupload').prop('disabled', false);
				}
			});
		}
	});
	nzchatobj('.nzchat_general').click(function () {
		nzChatList = 0;
		nzTouid(0);
		//nzchatobj("#nzchatolcontent").html('<div style="text-align: center;margin-top: 140px;"><img src = "source/plugin/th_chat/images/loading.svg"></div>');
		nzLoadTextInit();
		nzchatobj('.nzchat_room').removeClass('nzactive');
		nzchatobj(this).addClass('nzactive');
	});
	nzchatobj('.nzchat_whisper').click(function () {
		nzChatList = 1;
		//nzchatobj("#nzchatolcontent").html('<div style="text-align: center;margin-top: 140px;"><img src = "source/plugin/th_chat/images/loading.svg"></div>');
		nzLoadTextInit();
		nzchatobj('.nzchat_room').removeClass('nzactive');
		nzchatobj(this).addClass('nzactive');
	});
	if (nzsetting.autoconnect == 1) {
		nzLoadTextInit();
	}
	const button = nzchatobj('#nzemoji');
	const picker = new EmojiButton();
	picker.on('emoji', emoji => {
		nzchatobj('#nzchatmessage').val(nzchatobj('#nzchatmessage').val() + emoji);
	});
	nzchatobj('#nzemoji').click(function () {
		picker.togglePicker(this);
	});
	nzchatobj('#nznewmessage').click(function () {
		nzScrollChat(true);
	});
	nzchatobj("#nzchatmessage").keydown(function (event) {
		if (event.keyCode == '13') {
			nzSend();
		}
	});
	nzchatobj('#nzchatcontent').scroll(function () {
		var objDiv = document.getElementById("nzchatcontent");
		if (objDiv.scrollHeight - objDiv.scrollTop == nzsetting.chatheight) {
			nzchatobj("#nznewmessage").hide();
			objDiv.scrollTop = objDiv.scrollHeight;
		}
	});
});

function nzNotice() {
	nzcommandz = 'notice';
	nzchatobj(".nzquoteboxi").html('<div><span class="nzquoteboxh">แก้ไขประกาศ</span>: ' + nzchatobj('#nzchatnotice').html() + '</div><div class="nzcancel" onclick="nzTouid(0)" title="ยกเลิก"></div>');
	nzchatobj(".nzquoteboxo").show();
	nzchatobj("#nzchatcontent").css('height', nzsetting.chatheight - nzchatobj(".nzquoteboxo").height());
	nzScrollChat(true);
	nzchatobj('#nzchatmessage').val(nzchatobj('#nzchatnotice').text());
	nzchatobj('#nzchatmessage').focus();
}

function nzChatPopup(con) {
	nzChatPopupContent = nzchatobj(con).next(".nzchatpopuph").html();
	nzchatobj('#th_chat_popup_box').html(nzChatPopupContent);
	showWindow('th_chat_popup', 'plugin.php?id=th_chat:popup');
}

function nzSend() {
	var data = nzchatobj.trim(nzchatobj("#nzchatmessage").val());
	if (data === '') {
		return false;
	}
	nztime1 = new Date().getTime();
	if (nztime1 > nztime2) {
		nzchatobj("#nzchatmessage").val('');
		nztime2 = nztime1 + nzsetting.delay;
		nzchatobj.post("plugin.php?id=th_chat:post" + formhash, {
			text: data,
			lastid: nzlastid,
			touid: nztouid,
			quota: nzquota,
			command: nzcommandz,
			room: nzChatRoom
		}, function (data) {
			if (nzquota > 0 || nzcommandz == 'notice' || nzcommandz.substr(0, 4) == 'edit') {
				nzTouid(0);
			}
			data = JSON.parse(data);
			if (data.type == 1) {
				nzalert(data.error);
				if (data.script) {
					eval(data.script);
				}
			} else {
				if (nztouid == nzChatRoom) {
					var listmess = sortObject(data);
					nzReadyForScroll();
					nzchatobj.each(listmess, function (k, v) {
						k = parseInt(k);
						if (k > nzlastid) {
							nzlastid = k;
							nzchatobj("#afterme").before(v);
							nzScrollChat();
						}
					});
					nzchatobj('.nzinnercontent img').one('load', function () {
						nzScrollChat();
					});
					if (nzsetting.iscleardata == 1) {
						var nzchatrr = nzchatobj(".nzchatrow");
						if (nzchatrr.size() > nzsetting.chatrowmax) {
							nzchatrr.first().remove();
						}
					}
				} else {
					nzChangeChatRoom(nztouid);
				}
			}
		});
	} else {
		nzalert('ส่งข้อความบ่อยไป');
	}
}

function nzCommand(command, xid) {
	if (command == '') {
		nzalert('คำสั่งผิดพลาด');
	} else {
		if (command == 'del') {
			var show = 'ลบข้อความ';
			var showid = ' ' + nzchatobj("#nzchatcontent" + xid).text();
		} else if (command == 'edit') {
			nzquota = 0;
			nzcommandz = 'edit ' + xid;
			nzchatobj(".nzquoteboxi").html('<div><div class="nzquoteboxh">แก้ไขข้อความ</div>' + nzchatobj("#nzrows_" + xid + " .nzinnercontent")[0].outerHTML + '</div><div class="nzcancel" onclick="nzTouid(0)" title="ยกเลิก"></div>');
			nzchatobj(".nzquoteboxi .nzcq").remove();
			nzchatobj(".nzquoteboxi .nzblockquote").remove();
			nzchatobj(".nzquoteboxi .nztag").remove();
			nzchatobj(".nzquoteboxi .nztag2").remove();
			nzchatobj(".nzquoteboxi .nztag3").remove();
			nzchatobj(".nzquoteboxo").show();
			nzchatobj("#nzchatcontent").css('height', nzsetting.chatheight - nzchatobj(".nzquoteboxo").height());
			nzScrollChat(true);
			nzchatobj("#nzchatmessage").val(nzchatobj(".nzquoteboxi .nzinnercontent").text());
			nzchatobj("#nzchatmessage").focus();
			return;
		} else if (command == 'ban') {
			var show = 'แบน';
			var showid = ' ' + nzchatobj("#nzolpro_" + xid).text() + '(UID: ' + xid + ')';
		} else if (command == 'unban') {
			var show = 'ปลดแบน';
			var showid = ' ' + nzchatobj("#nzolpro_" + xid).text() + '(UID: ' + xid + ')';
		} else if (command == 'clear') {
			var show = 'ล้างห้องแชท';
			var showid = '';
		}
		if (confirm('คุณต้องการที่จะ' + show + showid + ' ?') == true) {
			nzchatobj("#nzchatmessage").val("!" + command + " " + xid);
			nzSend();
		}
	}
}

function nzLoadTextInit() {
	nzchatobj.post("plugin.php?id=th_chat:newinit", {
		room: nzChatRoom,
		list: nzChatList
	}, function (data) {
		data = JSON.parse(data);
		nzlastid = data.lastid;
		nzchatobj("#nzchatcontent").html('<div class="nzcallrow">' + data.datahtml + '<div id="afterme"></div></div>');
		nzScrollChat(true);
		nzchatobj('.nzinnercontent img').one('load', function () {
			nzScrollChat();
		});
		if (!nzonol) {
			nzchatobj("#nzchatolcontent").html(data.datachatonline);
		}
		nzchatobj("#nzoltotal").html(data.chat_online_total);
		if (data.chat_unread && data.chat_unread > 0) {
			nzchatobj("#nzunread").html(data.chat_unread);
			nzchatobj("#nzunread").show();
		} else {
			nzchatobj("#nzunread").hide();
		}
		nzchatobj("#nzchatnotice").html(data.welcometext);
		nzResetInterval();
	});
}

function nzResetInterval() {
	clearInterval(nzInterval);
	nzInterval = setInterval(nzLoadText, nzsetting.reload);
}

function nzChangeChatRoom(id) {
	if (nzChatRoom !== id) {
		nzChatRoom = id;
		//nzchatobj("#nzchatolcontent").html('<div style="text-align: center;margin-top: 140px;"><img src = "source/plugin/th_chat/images/loading.svg"></div>');
		nzLoadTextInit();
	}
}

function nzScrollChat(force = false) {
	var objDiv = document.getElementById("nzchatcontent");
	if (force) {
		nzscroll = true;
		nzchatobj("#nznewmessage").hide();
	}
	if (nzscroll) {
		objDiv.scrollTop = objDiv.scrollHeight;
	} else {
		nzchatobj("#nznewmessage").show();
	}
}

function nzReadyForScroll() {
	var objDiv = document.getElementById("nzchatcontent");
	if (nzchatobj(".nzquoteboxo:visible")) {
		nzscroll = true;
	} else {
		if (objDiv.scrollHeight - objDiv.scrollTop == nzsetting.chatheight) {
			nzscroll = true;
		} else {
			nzscroll = false;
		}
	}
}

function nzLoadText() {
	nzchatobj.post("plugin.php?id=th_chat:new", {
		room: nzChatRoom,
		list: nzChatList,
		lastid: nzlastid
	}, function (data) {
		data = JSON.parse(data);
		var listmess = sortObject(data.chat_row);
		nzReadyForScroll();
		nzchatobj.each(listmess, function (k, v) {
			k = parseInt(k);
			if (k > nzlastid) {
				nzlastid = k;
				nzchatobj("#afterme").before(v);
				nzScrollChat();
			}
		});
		nzchatobj('.nzinnercontent img').one('load', function () {
			nzScrollChat();
		});
		if (nzsetting.iscleardata == 1) {
			var nzchatrr = nzchatobj(".nzchatrow");
			if (nzchatrr.size() > nzsetting.chatrowmax) {
				nzchatrr.first().remove();
			}
		}
		if (data.chat_online) {
			if (!nzonol) {
				nzchatobj("#nzchatolcontent").html(data.chat_online);
			}
			nzchatobj("#nzoltotal").html(data.chat_online_total);
		}
		if (data.chat_unread && data.chat_unread > 0) {
			nzchatobj("#nzunread").html(data.chat_unread);
			nzchatobj("#nzunread").show();
		} else {
			nzchatobj("#nzunread").hide();
		}
		nzResetInterval();
	});
}

function nzQuota(i) {
	nzTouid(0);
	if (nzchatobj("#nzrows_" + i + " .nzuserat2")[0]) {
		nzchatobj(".nzquoteboxi").html('<div class="nzinnercontent"><div class="nzblockquote">' + nzchatobj("#nzrows_" + i + " .nzuserat2")[0].outerHTML + ': ' + nzchatobj("#nzchatcontent" + i).html() + '</div></div><div class="nzcancel" onclick="nzTouid(0)" title="ยกเลิก"></div>');
	} else {
		nzchatobj(".nzquoteboxi").html('<div class="nzinnercontent"><div class="nzblockquote">' + nzchatobj("#nzchatcontent" + i).html() + '</div></div><div class="nzcancel" onclick="nzTouid(0)" title="ยกเลิก"></div>');
	}
	nzchatobj(".nzquoteboxi .nzcq").remove();
	nzchatobj(".nzquoteboxi .nzuserat2").toggleClass('nzuserat2 nzuserat');
	nzchatobj(".nzquoteboxo").show();
	nzchatobj("#nzchatcontent").css('height', nzsetting.chatheight - nzchatobj(".nzquoteboxo").height());
	nzScrollChat(true);
	nzquota = i;
	nzchatobj("#nzchatmessage").focus();
}

function nzAt(i) {
	seditor_insertunit('nzchat', '@' + i + ' ', '');
	nzchatobj("#nzchatmessage").focus();
}

function nzTouid(i) {
	if (i > 0) {
		nzquota = 0;
		nzcommandz = '';
		nzchatobj(".nzquoteboxi").html('<div style="margin: 0 auto;"><span class="nzquoteboxh">แชทส่วนตัวกับ</span> <img src="uc_server/avatar.php?uid=' + i + '&size=small" class="nzchatavatar" width="32" height="32" onerror="this.src=\'uc_server/images/noavatar_small.gif\';" align="absmiddle"> ' + nzchatobj(".nzat_" + i).last()[0].outerHTML + '</div><div class="nzcancel" onclick="nzTouid(0)" title="ยกเลิก"></div>');
		nzchatobj(".nzquoteboxi .nzcq").remove();
		nzchatobj(".nzquoteboxi .nzinnercontent").remove();
		nzchatobj(".nzquoteboxo").show();
		nzchatobj("#nzchatcontent").css('height', nzsetting.chatheight - nzchatobj(".nzquoteboxo").height());
		nztouid = i;
		if (nzChatRoom !== i) {
			nzChangeChatRoom(i);
		}
	} else {
		nzchatobj("#nzchatcontent").css('height', nzsetting.chatheight);
		if (nzcommandz.substr(0, 4) == 'edit') {
			if (nzchatobj(".nzquoteboxi .nzinnercontent").text() == nzchatobj('#nzchatmessage').val()) {
				nzchatobj('#nzchatmessage').val('');
			}
		} else if (nzcommandz == 'notice') {
			if (nzchatobj('#nzchatmessage').val() == nzchatobj('#nzchatnotice').text()) {
				nzchatobj('#nzchatmessage').val('');
			}
		}
		nzchatobj(".nzquoteboxi").html('');
		nzchatobj(".nzquoteboxo").hide();
		nztouid = 0;
		nzquota = 0;
		nzcommandz = '';
		if (nzChatRoom > 0) {
			nzChangeChatRoom(0);
		}
	}
}

function nzReload() {
	nzalert('กำลังรีโหลด...');
	nzChatList = 0;
	nzChatRoom = 0;
	nzTouid(0);
	nzchatobj('.nzchat_room').removeClass('nzactive');
	nzchatobj('.nzchat_general').addClass('nzactive');
	nzLoadTextInit();
}

function nzClean() {
	nzchatobj(".nzchatrow").fadeOut('slow');
}

function nzCheckImg(i) {
	var maxheight = 240;
	var maxwidth = 500;
	var w = parseInt(i.width);
	var h = parseInt(i.height);
	if (w > maxwidth) {
		i.style.cursor = "pointer";
		i.onclick = function () {
			var iw = window.open(this.src, 'ImageViewer', 'resizable=1');
			iw.focus();
		};
		h = (maxwidth / w) * h;
		w = maxwidth;
		i.height = h;
		i.width = w;
	}
	if (h > maxheight) {
		i.style.cursor = "pointer";
		i.onclick = function () {
			var iw = window.open(this.src, 'ImageViewer', 'resizable=1');
			iw.focus();
		};
		i.width = (maxheight / h) * w;
		i.height = maxheight;
	}
}

function sortObject(a) {
	var b = {},
		c, d = [];
	for (c in a) {
		if (a.hasOwnProperty(c)) {
			d.push(c);
		}
	}
	d.sort();
	for (c = 0; c < d.length; c++) {
		b[d[c]] = a[d[c]]
	}
	return b;
}