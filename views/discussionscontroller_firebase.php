<?php
		include_once(PATH_ROOT.DS.plugins.DS.'Van2Shout'.DS.'firebase'.DS.'FirebaseToken.php');
		$Session = GDN::Session();
		$uname = $Session->User->Name;

		$tokenGen = new Services_FirebaseTokenGenerator("BGP4TgWgRe8wqiS5Qr3y2WDwD6dwiLaKxRQ1siop");

		$auth_token = $tokenGen->createToken(array("id" => $uname));
		echo "var AUTH_TOKEN = '".$auth_token."';\n";
	?>
	var loggedInUname = "<?php echo $uname; ?>";
	var firebase = new Firebase('https://van2shout.firebaseIO.com');
	firebase.auth(AUTH_TOKEN, function(err)
	{
		if(err)
		{
			alert("Could not log in to firebase");
		}
		else
		{
			console.log("Login to firebase succeeded!");
		}
	});

	firebase.child('broadcast').on('child_added', function(snapshot) {
		var msg = snapshot.val();
		var timetext = '';
		var time = moment.unix(msg.time).calendar();
		<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";\n"; } ?>
		$("#shoutboxcontent").append("<li>" + timetext + " <strong><a href='" + gdn.url('profile/' + msg.uname) + "' target='blank'>" + msg.uname + "</a>: " + msg.content + "</strong></li>");
	});

	firebase.child('private').on('child_added', function(snapshot) {
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
			pmtext = ' Some pm';
		}
		<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";\n"; } ?>
		$("#shoutboxcontent").append("<li>" + timetext + " <strong>" + pmtext + msg.content + "</strong></li>");
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
		firebase.push({uname: uname, to: to, content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
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
