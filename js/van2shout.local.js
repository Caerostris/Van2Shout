jQuery(document).ready(function($) {
	UpdateShoutbox();
	setInterval('UpdateShoutbox()', parseInt(gdn.definition('Van2ShoutUpdateInterval')));
});

function UpdateShoutbox() {
	var obj = document.getElementById("van2shoutscroll");
	// the slider currently is at the bottom ==> make it stay there after adding new posts
	if(obj.scrollTop == (obj.scrollHeight - obj.offsetHeight)) {
		var scrolldown = true;
	} else {
		var scrolldown = false;
	}

	$.get(gdn.url('plugin/Van2ShoutData?postcount=' + gdn.definition('Van2ShoutMessageCount')), function(data) {
		var string = "";
		var posts = JSON.parse(data);

		for(var i = 0; i < posts.length; i++) {
			var timetext = '';
			var private_text = '';
			var user = posts[i].user;

			if(gdn.definition('Van2ShoutTimestamp') == 'true') {
				var time = moment.unix(posts[i].time).calendar();
				timetext = "<font color='" + gdn.definition('Van2ShoutTimeColor') + "'>[" + time + "]</font>";
			}

			if(posts[i].type == 'private') {
				if(posts[i].recipient == gdn.definition('UserName')) {
						private_text = 'PM from ';
				} else {
					private_text = 'PM to ';
					user = posts[i].recipient;
				}
			}

			string = string + "<li>" + DeleteBttn(posts[i].id) + timetext + " <strong id='post" + posts[i].id + "'>" + private_text + "<a href='" + gdn.url('profile/' + user) + "' target='blank' style='color: " + posts[i].color + "'>" + user + "</a></strong>: " + emoticon.emoticonize(posts[i].message, {animate: false}) + "</li>";
		}

		$("#shoutboxcontent").html(string);

		if(scrolldown == true) {
			obj.scrollTop = obj.scrollHeight;
		}
	});
}

function SubmitMessage() {
	var post = {};
	post.message = $("#shoutboxinput").val();

	if(post.message == '/help') {
		v2s_help();
		$("#shoutboxinput").val('');
		return;
	}

	if(post.message.substr(0, 3) == '/w ' || post.message.substr(0, 5) == '/msg ') {
		post.message = post.message.substr(post.message.indexOf(" ")+1, post.message.length - post.message.indexOf(" ") - 1);
		post.recipient = post.message.substr(0, post.message.indexOf(" "));
		post.message = post.message.substr(post.message.indexOf(" ")+1, post.message.length - post.message.indexOf(" ") - 1);
	}

	$.post(gdn.url('plugin/Van2ShoutData'), { 'post': JSON.stringify(post) }, function(data) {
		UpdateShoutbox();
		$("#van2shoutsubmit").show();
		$("#shoutboxloading").hide();
	});

	$("#van2shoutsubmit").hide();
	$("#shoutboxloading").show();
	$("#shoutboxinput").val("");
}

function DeletePost(id) {
	$.get(gdn.url('plugin/Van2ShoutData&del=' + id), function(data) {});
	setTimeout(UpdateShoutbox, 10);
	alert("Message deleted");
}
