//Copyright (c) 2010-2011 by Caerostris <caerostris@gmail.com>
//    This file is part of Van2Shout.
//
//    Van2Shout is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Van2Shout is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Van2Shout.  If not, see <http://www.gnu.org/licenses/>.

function send() {
	var rootpath2 = gdn.definition('Van2ShoutRootpath');
	var msg = $("#shoutboxMessage").val();
	if(msg.indexOf("/help") == 0){
		gdn.inform("Type /help to get this help.<br />Type /w {username} {message} to send a private message");
		setPostText();
	} else {
		var url = rootpath2 + 'index.php?msg=' + msg
		$("#postDIV").html('<form action="javascript:send(' + "'" + rootpath2 + "'" + ');" name="shoutform"><input type="text" onKeyPress="checkLength();" name="shoutboxMessage" id="shoutboxMessage" value="" size="18px" maxlength="256"/> <img src="' + rootpath2 + 'applications/dashboard/design/images/progress.gif" /></form>');
		$.ajax({
			url: url,
			global: false,
			type: "GET",
			data: null,
			dataType: "html",
			success:function(){
				UpdateShoutbox();
				setPostText();
			}
		});
		$("#shoutboxMessage").val("");
		if(msg.indexOf("/w ") == 0){
			var slice = msg.slice(3);
			var split = slice.split(" ");
			$.ajax({
				url: rootpath2 + '?p=profile/' + escape(split[0]),
				global: false,
				type: "GET",
				data: null,
				dataType: "html",
				error:function(){
					gdn.inform("Warning: Username doesn't exist");
				},
				success:function(){
					gdn.inform("Private message sent.");
				}
			});
		}
	}
}

function UpdateShoutbox() {
	var rootpath2 = gdn.definition('Van2ShoutRootpath');
	var url = gdn.url('/plugin/van2shouttext');
	$.ajax({
		url: url,
		global: false,
		type: "GET",
		data: null,
		dataType: "html",
		success: function(Data){
			$("#shoutboxText").html(Data);
		}
	});
}
function setPostText() {
	var rootpath2 = gdn.definition('Van2ShoutRootpath');
	$("#postDIV").html('<form action="javascript:send();" name="shoutform"><input type="text" onKeyPress="checkLength();" name="shoutboxMessage" id="shoutboxMessage" value="" size="18px" maxlength="256"/> <input type="button" id="van2shoutsubmit" name="submit" onClick="javascript:send();" value="->" /></form>');
	$("#shoutboxMessage").focus();
}
$(document).ready(function() {
	setPostText();
	UpdateShoutbox();
	setInterval('UpdateShoutbox()', 5000);
});

function remove(rmID) {
	var rootpath2 = gdn.definition('Van2ShoutRootpath');
	var url = rootpath2 + 'index.php?rm=' + rmID;
	$.ajax({
		url: url,
		global: false,
		type: "GET",
		data: null,
		dataType: "html",
		error: function(){
			alert("Error while deleting message!");
		}
	});
	gdn.inform("Message removed");
	//alert("Message removed");
	UpdateShoutbox();
}
function clearall() {
	var rootpath2 = gdn.definition('Van2ShoutRootpath');
	var url = rootpath2 + 'index.php?clearall=1';
	$.ajax({
		url: url,
		global: false,
		type: "GET",
		data: null,
		dataType: "html",
		error: function(){
			alert("Error while clearing database");
		}
	});
	alert("Database cleared");
	UpdateShoutbox();
}
function checkLength() {
	if($("#shoutboxMessage").val().length > "150"){
		$("#van2shoutsubmit").attr('disabled', 'disabled');
		$("#shoutboxMessage").css("background-color", "red");
	} else {
		$("#van2shoutsubmit").removeAttr('disabled');
		$("#shoutboxMessage").css("background-color", "white");
	}
}
