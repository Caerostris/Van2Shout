<?php if(!defined('APPLICATION')) exit();
//Copyright (c) 2010-2013 by Caerostris <caerostris@gmail.com>
//	 This file is part of Van2Shout.
//
//	 Van2Shout is free software: you can redistribute it and/or modify
//	 it under the terms of the GNU General Public License as published by
//	 the Free Software Foundation, either version 3 of the License, or
//	 (at your option) any later version.
//
//	 Van2Shout is distributed in the hope that it will be useful,
//	 but WITHOUT ANY WARRANTY; without even the implied warranty of
//	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	 GNU General Public License for more details.
//
//	 You should have received a copy of the GNU General Public License
//	 along with Van2Shout.  If not, see <http://www.gnu.org/licenses/>.

echo "<div id='van2shout' class='Box'>";
echo "<h4>Shoutbox</h4>\n";
?>

<div id="van2shoutscroll">
	<ul id="shoutboxcontent">
	</ul>
</div>

<?php
	$Session = GDN::Session();
	if($Session->CheckPermission('Plugins.Van2Shout.Post'))
	{
		echo "<form action='javascript:SubmitMessage();'>\n<input type='text' style='width:80%;' name='shoutboxinput' id='shoutboxinput' onkeydown='checkLength();' />";
		echo "<img src='".Gdn::Request()->Domain()."/".Gdn::Request()->Webroot()."/applications/dashboard/design/images/progress.gif' style='display:none;' id='shoutboxloading' />\n";
		echo "<input type='submit' value='->' id='van2shoutsubmit' name='van2shoutsubmit' />\n</form>\n";
	}
?>
</div>
<script src="<?php echo Gdn::Request()->Domain()."/".Gdn::Request()->WebRoot(); ?>/plugins/Van2Shout/js/moment.min.js" type="text/javascript"></script>
<script type="text/javascript">
	jQuery(document).ready(function($) { UpdateShoutbox(); });
	setInterval('UpdateShoutbox()', <?php echo C('Plugin.Van2Shout.Interval', 5000); ?>);

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

		$.get(gdn.url('plugin/Van2ShoutData?postcount=50'), function(data)
		{
			var timecolour = "<?php echo C('Plugin.Van2Shout.TimeColour', 'grey'); ?>";
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
					string = string + "<li>" + DeleteMsg(id[0]) + timetext + " <strong id='post" + id[0] + "'>PM from <a href='" + gdn.url('profile/' + parsedArray[0]) + "' target='blank' >" + parsedArray[0] + "</a>: " + idArray[0] + "</strong></li>";
				}
				else if (unparsed.indexOf('[!pmtocontent!]') != -1)
				{
					var parsedArray = unparsed.split("[!pmtocontent!]");
					var idArray = parsedArray[1].split("[!msgid!]");
					var id = idArray[1].split("[!msgtime!]");
					var time = moment.unix(id[1]).calendar();
					var timetext = '';
					<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";"; } ?>
					string = string + "<li>" + DeleteMsg(id[0]) + timetext + " <strong class='pmto'>PM to <a href='" + gdn.url('profile/' + parsedArray[0]) + "' target='blank' >" + parsedArray[0] + "</a>: " + idArray[0] + "</strong></li>";
				}
				else
					{
					var parsedArray = unparsed.split("[!content!]");
					var idArray = parsedArray[1].split("[!msgid!]");
					var id = idArray[1].split("[!msgtime!]");
					var time = moment.unix(id[1]).calendar();
					var timetext = '';
					<?php if(!C('Plugin.Van2Shout.Timestamp', false)) { echo "timetext = \"<font color='\" + timecolour + \"'>[\" + time + \"]</font>\";"; } ?>
					string = string + "<li>" + DeleteMsg(id[0]) + timetext + " <strong id='post" + id[0] + "'><a href='" + gdn.url('profile/' + parsedArray[0]) + "' target='blank' >" + parsedArray[0] + "</a>: " + idArray[0] + "</strong></li>";
				}

				string = string + '<style type="text/css">#post' + id[0] + ' a { color:' + colourArray[0] + '; } #post' + id[0] + ' a:hover { text-decoration:underline; }</style>';
			}


			$("#shoutboxcontent").html(string);

			if(scrolldown == true)
			{
				obj.scrollTop = obj.scrollHeight;
			}
		});
	}

	function checkLength()
	{
		if($("#shoutboxinput").val().length > 148)
		{
			$("#van2shoutsubmit").attr('disabled', 'disabled');
			$("#shoutboxinput").css("background-color", "red");
		}
		else
		{
			$("#van2shoutsubmit").removeAttr('disabled');
			$("#shoutboxinput").css("background-color", "white");
		}
	}


	function v2s_help()
	{
		gdn.informMessage('PM: /w {User}<br /><a href="' + gdn.url('profile/<?php echo $Session->UserName; ?>van2shout')  + '" target="_blank">Change your colour</a>');
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

	<?php
		if(defined('__VAN2SHOUT_INCLUDE__'))
		{
			echo "$('#van2shout').insertBefore('#Content');";
		}
	?>
</script>

<style type="text/css">
	#van2shoutscroll
	{
		<?php
		if(!defined('__VAN2SHOUT_INCLUDE__'))
		{
			echo "height:500px;\n";
		}
		else
		{
			echo "height:200px\n;";
		}
		?>
		overflow:auto;
	}
</style>
