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

?>
<h2><?php echo T('Van2Shout'); ?></h2>
<?php
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<ul>
	<li>
		<?php
			$Session = GDN::Session();
			$UserModel = new UserModel();

			echo $this->Form->Label('Settings');

			$UserRoles = $UserModel->GetRoles($Session->UserID);

			$data = array("Default" => "Default");

			foreach($UserRoles as $UserRole)
			{
				$color = C('Plugin.Van2Shout.'.$UserRole["Name"], 'Default');
				if($color == '')
					$color = 'Default';

				$data[$UserRole["Name"]] = $UserRole["Name"]." (".$color.")";
			}

			$metadata = Gdn::UserMetaModel()->GetUserMeta($Session->UserID, 'Plugin.Van2Shout.Colour', 'Default');
			$currentColour = $metadata['Plugin.Van2Shout.Colour'];

			echo "Select the group that should be used for the Shoutbox colour:<br />";
			echo $this->Form->DropDown('Plugin.Van2Shout.UserColour', $data, array('Value' => $currentColour));
		?>
	</li>
</ul>
<?php echo $this->Form->Close('Save');
