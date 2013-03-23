<?php
		include_once(PATH_ROOT.DS.plugins.DS.'Van2Shout'.DS.'firebase'.DS.'FirebaseToken.php');
		$Session = GDN::Session();
		$uname = $Session->User->Name;

		$metadata = Gdn::UserMetaModel()->GetUserMeta($Session->UserID, "FirebaseToken", "");
		$auth_token = $metadata['FirebaseToken'];

		if($auth_token == "")
		{
			$tokenGen = new Services_FirebaseTokenGenerator(C('Plugin.Van2Shout.FBSecret', ''));
			$auth_token = $tokenGen->createToken(array("id" => $uname));
			Gdn::UserMetaModel()->SetUserMeta($Session->UserID, "FirebaseToken", $auth_token);
		}
		echo "var AUTH_TOKEN = '".$auth_token."';\n";
	?>
	var loggedInUname = "<?php echo $uname; ?>";
	var firebase = new Firebase('<?php echo C('Plugin.Van2Shout.FBUrl', ''); ?>');
	firebase.auth(AUTH_TOKEN, function(err)
	{
		if(err)
		{
			console.log("Login to firebase failed, trying to generate new auth token!");
			$.get(gdn.url('plugin/Van2ShoutData?newtoken=1'), function(data){
				console.log("This is the new token: " + data);
			});
		}
		else
		{
			console.log("Login to firebase succeeded!");
		}
	});

	firebase.child('broadcast').on('child_added', function(snapshot) {
		var obj = document.getElementById("van2shoutscroll");
		//the slider currently is at the bottom ==> make it stay there after adding new posts
		if(obj.scrollTop == (obj.scrollHeight - obj.offsetHeight)) {
			var scrolldown = true;
		}
		else
		{
			var scrolldown = false;
		}

		var msg = snapshot.val();
		var timetext = '';
		var time = moment.unix(msg.time).calendar();
		<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";\n"; } ?>
		$("#shoutboxcontent").append("<li>" + timetext + " <strong><a href='" + gdn.url('profile/' + msg.uname) + "' target='blank'>" + msg.uname + "</a>: " + msg.content + "</strong></li>");

		if(scrolldown == true)
		{
			obj.scrollTop = obj.scrollHeight;
		}

	});

	firebase.child('private').child(loggedInUname).on('child_added', function(snapshot) {
		var obj = document.getElementById("van2shoutscroll");
		//the slider currently is at the bottom ==> make it stay there after adding new posts
		if(obj.scrollTop == (obj.scrollHeight - obj.offsetHeight)) {
			var scrolldown = true;
		}
		else
		{
			var scrolldown = false;
		}

		var msg = snapshot.val();
		var pmtext = '';
		var timetext = '';
		var time = moment.unix(msg.time).calendar();

		if(msg.uname == loggedInUname)
		{
			pmtext = "PM to " + "<a href='" + gdn.url('profile/' + msg.to) + "' target='blank'>" + msg.to + "</a>: ";
		}
		else if(msg.to == loggedInUname)
		{
			pmtext = "PM from " + "<a href='" + gdn.url('profile/' + msg.uname) + "' target='blank'>" + msg.uname + "</a>: ";
		}
		else
		{
			pmtext = 'Some pm';
		}
		<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";\n"; } ?>
		$("#shoutboxcontent").append("<li>" + timetext + " <strong>" + pmtext + msg.content + "</strong></li>");

		if(scrolldown == true)
		{
			obj.scrollTop = obj.scrollHeight;
		}
	});

	function SubmitMessage()
	{
		var msg = $("#shoutboxinput").val();
		if(msg == '/help')
		{
			v2s_help();
			$("#shoutboxinput").val('');
			return;
		}

		if(msg.indexOf('/w ') == 0)
		{
			var substr = msg.substr(3, msg.length);
			var uname = substr.substr(0, substr.indexOf(' '));
			var msg = substr.substr(substr.indexOf(' '), substr.length);

			firebase_push_pm(firebase.child('private'), loggedInUname, uname, msg, function(err)
			{
				if(err != null)
				{
					alert("Couldn't send message");
				}

				$("#van2shoutsubmit").show();
				$("#shoutboxloading").hide();
			});
		}
		else
		{
			firebase_push(firebase.child('broadcast'), loggedInUname, msg, function(err)
			{
				if(err != null)
				{
					alert("Couldn't send message");
				}

				$("#van2shoutsubmit").show();
				$("#shoutboxloading").hide();
			});
		}

		$("#van2shoutsubmit").hide();
		$("#shoutboxloading").show();
		$("#shoutboxinput").val("");
	}

	function firebase_push(firebase, uname, content, callback)
	{
		firebase.push({uname: uname, content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
	}

	function firebase_push_pm(firebase, uname, to, content, callback)
	{
		firebase.child(to).push({uname: uname, to: to, content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
		firebase.child(uname).push({uname: uname, to: to, content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
	}

	//return html code of delete button
	function DeleteMsg(id)
	{
		var str = "";
		if(gdn.definition('Van2ShoutDelete') == "true")
		{
			str = "<img src='<?php echo Gdn::Request()->Domain()."/".Gdn::Request()->WebRoot(); ?>/plugins/Van2Shout/img/del.png' onClick='DeletePost(\"" + id + "\");' /> ";
		} else {
			str = "";
		}
		return str;
	}

	function DeletePost(id)
	{
		//$.get(gdn.url('plugin/Van2ShoutData&del=' + id), function(data) {});
		//setTimeout(UpdateShoutbox, 10);
		//alert("Message deleted");
	}
