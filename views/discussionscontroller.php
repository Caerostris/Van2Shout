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
		if(VAN2SHOUT_ASSETTARGET == 'Content')
		{
			$width = "90%";
		}
		else
		{
			$width = "70%";
		}
		echo "<form action='javascript:SubmitMessage();'>\n<input type='text' style='width: ".$width."' name='shoutboxinput' id='shoutboxinput' onkeydown='checkLength();' />";
		echo "<img src='".Gdn::Request()->Domain()."/".Gdn::Request()->Webroot()."/applications/dashboard/design/images/progress.gif' style='display:none;' id='shoutboxloading' />\n";
		echo "<input type='submit' value='Send' id='van2shoutsubmit' name='van2shoutsubmit' />\n</form>\n";
	}
?>
</div>
<script src="<?php echo Gdn::Request()->Domain()."/".Gdn::Request()->WebRoot(); ?>/plugins/Van2Shout/js/moment.min.js" type="text/javascript"></script>
<script type="text/javascript">
	<?php
		if(VAN2SHOUT_ASSETTARGET == 'Content')
		{
			echo "	$('#van2shout').insertBefore('#Content');";
		}
	?>

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

	<?php include(USE_FIREBASE ? dirname(__FILE__).DS.'discussionscontroller_firebase.php' : dirname(__FILE__).DS.'discussionscontroller_local.php'); ?>
</script>

<style type="text/css">
	#van2shoutscroll
	{
		<?php
		if(VAN2SHOUT_ASSETTARGET == 'Content')
		{
			echo "height:200px;\n";
		}
		else
		{
			echo "height:500px\n;";
		}
		?>
		overflow:auto;
	}
</style>
