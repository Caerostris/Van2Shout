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
</h1>

<div style="margin-left:20px;">
	<br /><b>Use firebase for a faster chat!</b><br />
	Firebase is a service which provides hyper-fast and flexible databases.<br />
	The free plan should provide enough resources for all shoutboxes.<br />
	Sign up at <b><a href='https://firebase.com' target='_blank'>firebase.com</a></b> and add the <b><a href="/<?php echo (Gdn::Request()->WebRoot().'/plugins/Van2Shout/firebase/rules.html'); ?>" class="SignInPopup">Firebase security rules</a></b><br />
	<a href="javascript:reset_tokens();">Reset firebase tokens</a><br />
	<br />
</div>

<table class="AltColumns">
	<thead align="left">
		<tr>
			<th width="25%">Firebase</th><th></th>
		</tr>
	</thead>
	<tbody>
		<tr><td>Enable Firebase</td><td><?php echo $this->Form->CheckBox('Plugin.Van2Shout.Firebase.Enable', ''); ?></td></tr>
		<tr><td>Firebase URL</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.Firebase.Url'); ?></td></tr>
		<tr><td>Firebase secret</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.Firebase.Secret'); ?></td></tr>
	</tbody>
</table>

<table class="AltColumns">
	<thead align="left">
		<tr>
			<th width="25%">Option</th><th>Value</th>
		</tr>
	</thead>
	<tbody>
		<tr><td>Display on the discussions page</td><td><?php echo $this->Form->CheckBox('Plugin.Van2Shout.DisplayTarget.DiscussionsController', ''); ?></td></tr>
		<tr><td>Show timestamp</td><td><?php echo $this->Form->CheckBox('Plugin.Van2Shout.Timestamp', ''); ?></td></tr>
		<tr><td>'Send' button text</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.SendText'); ?></td></tr>
		<tr><td>Timestamp colour<br />(Default: grey)</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.TimeColour'); ?></td></tr>
		<tr><td>Update interval in seconds<br />(Default: 5)</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.Interval'); ?></td></tr>
		<tr><td>Number of messages to display<br />(Default: 50)</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.MsgCount'); ?></td></tr>
		<tr><td>Default colour (leave empty for theme default)</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.Default'); ?></td></tr>
	</tbody>
</table>

<table class="AltColumns">
	<thead align="left">
		<tr>
			<th width="20%">Role</th><th>Colour <a href="javascript:gdn.informMessage('Any HTML compatible colour (e.g. a hex colour)');">?</a></th></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$RoleModel = new RoleModel();
			$Roles = $RoleModel->Get();
			while($role = $Roles->Value('Name', NULL))
			{
				echo "<tr><td>" . $role . "</td><td>" . $this->Form->Input('Plugin.Van2Shout.'.$role) . "</td></tr>";
			}
		?>
	</tbody>
</table><br />
<input type="submit" class="Button" value="Save" />
<?php
echo $this->Form->Close();
?>

<script type="text/javascript">
	function reset_tokens() {
		$.get(gdn.url('plugin/Van2ShoutData?reset_tokens=1'), function(data) {
			gdn.informMessage("Tokens reset.");
		});
	}
</script>
