	jQuery(document).ready(function($) { UpdateShoutbox(); });
	setInterval('UpdateShoutbox()', <?php echo(C('Plugin.Van2Shout.Interval', 5) * 1000); ?>);

	function UpdateShoutbox()
	{
		var obj = document.getElementById("van2shoutscroll");
		//the slider currently is at the bottom ==> make it stay there after adding new posts
		if(obj.scrollTop == (obj.scrollHeight - obj.offsetHeight)) {
			var scrolldown = true;
		}
		else
		{
			var scrolldown = false;
		}

		$.get(gdn.url('plugin/Van2ShoutData?postcount=<?php echo C('Plugin.Van2Shout.MsgCount', '50'); ?>'), function(data)
		{
			var string = "";

			var array = unescape(data).split("\n");
			for(var key in array)
			{
				var unparsed = array[key];

				if(unparsed == "")
				{
					break;
				}

				var colourArray = unparsed.split("[!colour!]");

				unparsed = colourArray[1];
				//render PMs
				if(unparsed.indexOf('[!pmcontent!]') != -1)
				{
					var parsedArray = unparsed.split("[!pmcontent!]");
					var idArray = parsedArray[1].split("[!msgid!]");
					var id = idArray[1].split("[!msgtime!]");
					var time = moment.unix(id[1]).calendar();
					var timetext = '';
					<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";"; } ?>
					string = string + "<li>" + DeleteMsg(id[0]) + timetext + " <strong id='post" + id[0] + "'>PM from <a href='" + gdn.url('profile/' + parsedArray[0]) + "' target='blank' >" + parsedArray[0] + "</a></strong>: " + idArray[0] + "</li>";
				}
				else if (unparsed.indexOf('[!pmtocontent!]') != -1)
				{
					var parsedArray = unparsed.split("[!pmtocontent!]");
					var idArray = parsedArray[1].split("[!msgid!]");
					var id = idArray[1].split("[!msgtime!]");
					var time = moment.unix(id[1]).calendar();
					var timetext = '';
					<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";"; } ?>
					string = string + "<li>" + DeleteMsg(id[0]) + timetext + " <strong class='pmto'>PM to <a href='" + gdn.url('profile/' + parsedArray[0]) + "' target='blank' >" + parsedArray[0] + "</a></strong>: " + idArray[0] + "</li>";
				}
				else
					{
					var parsedArray = unparsed.split("[!content!]");
					var idArray = parsedArray[1].split("[!msgid!]");
					var id = idArray[1].split("[!msgtime!]");
					var time = moment.unix(id[1]).calendar();
					var timetext = '';
					<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";"; } ?>
					string = string + "<li>" + DeleteMsg(id[0]) + timetext + " <strong id='post" + id[0] + "'><a href='" + gdn.url('profile/' + parsedArray[0]) + "' target='blank' >" + parsedArray[0] + "</a></strong>: " + idArray[0] + "</li>";
				}

				string = string + '<style type="text/css">#post' + id[0] + ' a { color:' + colourArray[0] + '; } #post' + id[0] + ' a:hover { text-decoration:underline; }</style>';
			}


			$("#shoutboxcontent").html(string);

			if(scrolldown == true)
			{
				obj.scrollTop = obj.scrollHeight;
			}

			emojify.run();
		});
	}

	function SubmitMessage()
	{
		if($("#shoutboxinput").val() == '/help')
		{
			v2s_help();
			$("#shoutboxinput").val('');
			return;
		}
		$.get(gdn.url('plugin/Van2ShoutData?newpost=' + escape($("#shoutboxinput").val())), function(data) {
			UpdateShoutbox();
			$("#van2shoutsubmit").show();
			$("#shoutboxloading").hide();
		});
		$("#van2shoutsubmit").hide();
		$("#shoutboxloading").show();

		$("#shoutboxinput").val("");
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
		$.get(gdn.url('plugin/Van2ShoutData&del=' + id), function(data) {});
		setTimeout(UpdateShoutbox, 10);
		alert("Message deleted");
	}
