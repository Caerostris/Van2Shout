<?php if (!defined('APPLICATION')) exit();
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

$Session = GDN::Session();
echo $this->Form->Open();
echo $this->Form->Errors();
?>

<h1>
	<p style="font-size:18px"><?php echo T('Van2Shout'); ?></p>
	<p><a style="margin-left:0px;" target="_blank" href="http://kenoschwalb.com/page/contact">Contact</a> | <a style="margin-left:0px;" target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R9SQDYVYKKVCC">Buy me a coffe</a></p><br />
</h1>

<div style="margin-left:20px;">
	<br /><b>Use firebase for a faster chat!</b><br />
	<div id="firebase">
		Firebase is a service which provides hyper-fast and flexible databases.<br />
		Van2Shout is able to switch its backend from vanillas MySQL database to firebase. Using firebase will make the shoutbox incredibly fast!<br />
		Firebase is free while they are still in beta! Sign up <a href='http://firebase.com'>here</a>!<br />
		<b>After you created your firebase, make sure to add the <a id="fbruleslnk2" href="javascript:showRules();">Van2Shout security rules</a>!</b><br />
		Database URL:&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->Form->Input('Plugin.Van2Shout.FBUrl'); ?><br />
		Firebase secret:&nbsp;&nbsp;<?php echo $this->Form->Input('Plugin.Van2Shout.FBSecret'); ?><br />
		(Leave both fields blank to switch back to MySQL)<br /><br />
		<input type="submit" class="Button" style="margin-left:0px;" value="Save" /> <a id="fbruleslnk" href="javascript:showRules();">Show me Van2Shout's Firebase rules!</a>
		<div id="fbrules" style="display:none;">Go to <?php $url = C('Plugin.Van2Shout.FBUrl', ''); if($url != '') { echo "<a href='".$url."' target='blank'>your firebase</a>"; } else { echo "your firebase"; } ?> and paste the following code at the "Security" tab:<pre><?php echo file_get_contents(PATH_ROOT.DS.'plugins'.DS.'Van2Shout'.DS.'firebase'.DS.'rules.firebase'); ?></pre></div>
	</div>
</div>

<table class="AltColumns">
	<thead align="left">
		<tr>
			<th width="20%">Option</th><th>Value</th>
		</tr>
	</thead>
	<tbody>
		<tr><td>Disable timestamp</td><td><?php echo $this->Form->CheckBox('Plugin.Van2Shout.Timestamp', ''); ?></td></tr>
		<tr><td>Colour of the timestamp (Default: grey)</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.TimeColour'); ?></td></tr>
		<tr><td>Update interval (Default: 5000)</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.Interval'); ?></td></tr>
		<tr><td>Set AssetTarget to 'Content' instead of 'Panel'</td><td><?php echo $this->Form->CheckBox('Plugin.Van2Shout.ContentAsset', ''); ?> <div id="assettext">In order to make this work, you need to modify a file. Click <a href="javascript:document.getElementById(id).innerHTML = 'In order to make this work, you have to add the following text to the file <code>application/vanilla/views/discussions/helper_functions.php</code><br />right below line 112:<br /><code>$Sender->FireEvent(\'BeforeDiscussionTabsDiv\');</code><br /><a href=\'javascript:document.getElementById(id).innerHTML = defaulttext;\'>hide</a>';">here</a> for more information</div></td></tr>
	</tbody>
</table>

<script type="text/javascript">
	var defaulttext = document.getElementById('assettext').innerHTML;
	var id = 'assettext';

	function showRules()
	{
		document.getElementById('fbruleslnk').href = 'javascript:hideRules();';
		document.getElementById('fbruleslnk2').href = 'javascript:hideRules();';
		document.getElementById('fbruleslnk').innerHTML = 'Hide Van2Shout\'s Firebase rules!';
		document.getElementById('fbrules').style.display = 'inherit';
	}

	function hideRules()
	{
		document.getElementById('fbruleslnk').href = 'javascript:showRules();';
		document.getElementById('fbruleslnk2').href = 'javascript:showRules();';
		document.getElementById('fbruleslnk').innerHTML = 'Show me Van2Shout\'s Firebase rules!';
		document.getElementById('fbrules').style.display = 'none';
	}
</script>

<table class="AltColumns">
	<thead align="left">
		<tr>
			<th width="20%">Role</th><th>Colours <a href="javascript:gdn.informMessage('Any HTML compatible colour (e.g. a hex colour)');">?</a></th></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$RoleModel = new RoleModel();
			$Roles = $RoleModel->Get();
			$RoleNames = array();
			array_push($RoleNames, "Default");

			while($name = $Roles->Value('Name', NULL))
			{
				array_push($RoleNames, $name);
			}
			foreach($RoleNames as $grp)
			{
				echo "<tr><td>" . $grp . "</td><td>" . $this->Form->Input('Plugins.Van2Shout.'.$grp) . "</td></tr>";
			}
		?>
	</tbody>
</table><br />
<input type="submit" class="Button" value="Save" />
<?php
echo $this->Form->Close();
?>
