var messageCounter = 0;
var oldestID = 0;
var firebase;

jQuery(document).ready(function($) {
	firebase = new Firebase(gdn.definition('Van2ShoutFirebaseUrl'));
	firebase.auth(gdn.definition('Van2ShoutFirebaseToken'), function(err) {
		if(err) {
			console.log("Login to firebase failed, trying to generate new auth token!");
			$.get(gdn.url('plugin/Van2ShoutFirebase?newtoken=1'), function(data) {
				console.log("This is the new token: " + data);
			});
		} else {
			console.log("Login to firebase succeeded!");
		}
	});

	// show new messages
	firebase.child('broadcast').on('child_added', function(snapshot) {
		messageCounter++;
		if(messageCounter >= parseInt(gdn.definition('Van2ShoutMessageCount'))) {
			$("#shout" + oldestID).remove();
			oldestID++;
		}

		var obj = document.getElementById("van2shoutscroll");
		//the slider currently is at the bottom ==> make it stay there after adding new posts
		if(obj.scrollTop == (obj.scrollHeight - obj.offsetHeight)) {
			var scrolldown = true;
		} else {
			var scrolldown = false;
		}

		var msg = snapshot.val();
		var sn_name = snapshot.name();
		var timetext = '';
		var time = moment.unix(msg.time).calendar();
		if(gdn.definition('Van2ShoutTimestamp') == 'true')
			timetext = "<font color='" + gdn.definition('Van2ShoutTimeColor') + "'>[" + time + "]</font>";

		$("#shoutboxcontent").append("<li id='shout" + messageCounter + "' name='brod_" + sn_name + "'>" + DeleteBttn('brod_' + sn_name) + timetext + " <strong><a href='" + gdn.url('profile/' + msg.uname) + "' target='blank'>" + msg.uname + "</a></strong>: " + htmlEntities(msg.content) + "</li>");
		$("#shoutboxcontent").append("<style type='text/css'>#shout" + messageCounter + " a { color: " + msg.colour + "; } #shout" + messageCounter + " a:hover { text-decoration: underline; }</style>");
		if(scrolldown == true) {
			obj.scrollTop = obj.scrollHeight;
		}

		$("#shout" + messageCounter).emoticonize();
	});

	// remove messages locally which have been removed from the database
	firebase.child('broadcast').on('child_removed', function(snapshot) {
		$("[name='brod_" + snapshot.name() + "']").remove();
	});
});

function firebase_push(firebase, uname, content, callback) {
	firebase.push({uname: uname, colour: gdn.definition('Van2ShoutUserColor'), content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
}

function firebase_push_pm(firebase, uname, to, content, callback) {
	firebase.child(to).push({uname: uname, to: to, content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
	firebase.child(uname).push({uname: uname, to: to, content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
}

function firebase_delete(firebase, name) {
	firebase.child(name).remove();
}

function htmlEntities(str) {
	return String(str).replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;');
}

function SubmitMessage() {
	var msg = $("#shoutboxinput").val();

	if (!msg)
		return;

	if(msg == '/help') {
		v2s_help();
		$("#shoutboxinput").val('');
		return;
	}

	if(msg.indexOf('/w ') == 0) {
		alert("Private messages not supported.");
	} else {
		firebase_push(firebase.child('broadcast'), gdn.definition('UserName'), msg, function(err) {
			if(err != null)
				alert("Couldn't send message");

			// reset the loading icon
			$("#van2shoutsubmit").show();
			$("#shoutboxloading").hide();
		});

		// show the loading icon
		$("#van2shoutsubmit").hide();
		$("#shoutboxloading").show();
	}

	$("#shoutboxinput").val("");
}

function DeletePost(id) {
	var name = id.substr(5, id.length - 5);
	if(id.substr(0, 5) == 'brod_') {
		firebase_delete(firebase.child('broadcast'), name);
	} else if(id.substr(0, 5) == 'priv_') {
		firebase_delete(firebase.child('private').child(gdn.definition('UserName').toLowerCase()), name);
	}
}

