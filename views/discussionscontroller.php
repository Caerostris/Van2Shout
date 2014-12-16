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
echo "<h4>".T('Shoutbox')."</h4>\n";
?>

<div id="van2shoutscroll">
	<ul id="shoutboxcontent">
	</ul>
</div>

<?php
	$Session = GDN::Session();
	if($Session->CheckPermission('Plugins.Van2Shout.Post'))
	{
		echo "<form action='javascript:SubmitMessage();'>\n<input type='text' style='width: 90%' name='shoutboxinput' id='shoutboxinput' onkeydown='checkLength();' />";
		echo "<img src='".Gdn::Request()->Domain()."/".Gdn::Request()->Webroot()."/applications/dashboard/design/images/progress.gif' style='display:none;' id='shoutboxloading' />\n";
		echo "<input type='submit' value='".T(C('Plugin.Van2Shout.SendText', 'Send'))."' id='van2shoutsubmit' name='van2shoutsubmit' class='Button' />\n</form>\n";
	}
?>
</div>
<script src="<?php echo Gdn::Request()->Domain()."/".Gdn::Request()->WebRoot(); ?>/plugins/Van2Shout/js/moment.min.js" type="text/javascript"></script>
<script type="text/javascript">
	$('#van2shout').prependTo('#Content');
	$('#van2shout').insertAfter('.Info');

	var timecolour = "<?php echo C('Plugin.Van2Shout.TimeColour', 'grey'); ?>";

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

	emojify.setConfig({
		emojify_tag_type: 'div',
		only_crawl_id: 'van2shout',
		img_dir: 'plugins/Van2Shout/img/emoji',
		ignore_tags: {
			'SCRIPT': 1,
			'A': 1,
			'PRE': 1,
			'CODE': 1
		}
	});

	<?php include(C('Plugin.Van2Shout.Firebase.Enable', false) ? dirname(__FILE__).DS.'discussionscontroller_firebase.php' : dirname(__FILE__).DS.'discussionscontroller_local.php'); ?>
</script>

<style type="text/css">
	#van2shoutscroll {
		height:200px;\n";
		overflow:auto;
	}

	#shoutboxcontent {
		word-wrap: break-word;
	}

	.emoji {
		width: 1.5em;
		height: 1.5em;
		display: inline-block;
		margin-bottom: -0.25em;
	}
</style>
