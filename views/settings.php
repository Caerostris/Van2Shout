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
	<p><a style="margin-left:0px;" target="_blank" href="http://kenoschwalb.com/page/contact">Contact</a> | <a style="margin-left:0px;" target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R9SQDYVYKKVCC">Buy me a coffe</a></p>
</h1>

<table class="AltColumns">
	<thead align="left">
		<tr>
			<th width="20%">Option</th><th>Value</th>
		</tr>
	</thead>
	<tbody>
		<tr><td>Disable timestamp</td><td><?php echo $this->Form->CheckBox('Plugin.Van2Shout.Timestamp', ''); ?></td></tr>
		<tr><td>Colour of the timestamp</td><td><?php echo $this->Form->Input('Plugin.Van2Shout.TimeColour'); ?></td></tr>
	</tbody>
</table>

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
