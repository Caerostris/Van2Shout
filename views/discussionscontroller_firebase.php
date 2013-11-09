<?php
		include_once(PATH_ROOT.DS.plugins.DS.'Van2Shout'.DS.'firebase'.DS.'v2s.php');
		$Session = GDN::Session();
		$uname = $Session->User->Name;


		$metadata = Gdn::UserMetaModel()->GetUserMeta($Session->UserID, "Plugin.Van2Shout.Colour", "");
	        $usercolour = C('Plugins.Van2Shout.'.$metadata['Plugin.Van2Shout.Colour']);

		echo "var AUTH_TOKEN = '".fb_get_token()."';\n";
		echo "var usercolour = '".$usercolour."';\n";
	?>
	var messageCounter = 0;
	var oldestID = 0;
	var maxMessages = <?php echo C('Plugin.Van2Shout.MsgCount', '50') ?>;
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
		messageCounter++;
		if(messageCounter >= maxMessages)
		{
			$("#shout" + oldestID).remove();
			oldestID++;
		}

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
		var sn_name = snapshot.name();
		var timetext = '';
		var time = moment.unix(msg.time).calendar();
		<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";\n"; } ?>
		$("#shoutboxcontent").append("<li id='shout" + messageCounter + "' name='" + sn_name + "'>" + DeleteBttn(sn_name) + timetext + " <strong><a href='" + gdn.url('profile/' + msg.uname) + "' target='blank'>" + msg.uname + "</a>: " + msg.content + "</strong></li>");
		$("#shoutboxcontent").append("<style type='text/css'>#shout" + messageCounter + " a { color: " + msg.colour + "; } #shout" + messageCounter + " a:hover { text-decoration: underline; }</style>");
		if(scrolldown == true)
		{
			obj.scrollTop = obj.scrollHeight;
		}

	});

	firebase.child('broadcast').on('child_removed', function(snapshot) {
		$("[name='" + snapshot.name() + "']").remove();
	});

	firebase.child('private').child(loggedInUname.toLowerCase()).on('child_added', function(snapshot) {
		messageCounter++;
		if(messageCounter >= maxMessages)
		{
			$("#shout" + oldestID).remove();
			oldestID++;
		}

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
			pmtext = "PM from <a href='" + gdn.url('profile/' + msg.uname) + "' target='blank'>" + msg.uname + "</a>: ";
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

        if (!msg) {
            return;
        }
		if(msg == '/help')
		{
			v2s_help();
			$("#shoutboxinput").val('');
			return;
		}

		if(msg.indexOf('/w ') == 0)
		{
			var substr = msg.substr(3, msg.length);
			var uname = substr.substr(0, substr.indexOf(' ')).toLowerCase();
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
		firebase.push({uname: uname, colour: usercolour, content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
	}

	function firebase_push_pm(firebase, uname, to, content, callback)
	{
		firebase.child(to).push({uname: uname, to: to, content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
		firebase.child(uname).push({uname: uname, to: to, content: content, time: Math.round((new Date()).getTime() / 1000)}, callback);
	}

	function firebase_delete(firebase, name)
	{
		firebase.child(name).remove();
	}

	//return html code of delete button
	function DeleteBttn(name)
	{
		var str = "";
		if(gdn.definition('Van2ShoutDelete') == "true")
		{
			str = "<img src='<?php echo Gdn::Request()->Domain()."/".Gdn::Request()->WebRoot(); ?>/plugins/Van2Shout/img/del.png' onClick='firebase_delete(firebase.child(\"broadcast\"), \"" + name + "\");' /> ";
		} else {
			str = "";
		}
		return str;
	}
