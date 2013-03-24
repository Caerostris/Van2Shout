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

if(C('Plugin.Van2Shout.FBUrl', '') != '' && C('Plugin.Van2Shout.FBSecret', '') != '')
{
	define('USE_FIREBASE', true);
}
else
{
	define('USE_FIREBASE', false);
}

if(C('Plugin.Van2Shout.ContentAsset', true))
{
	define('VAN2SHOUT_ASSETTARGET', 'Content');
}
else
{
	define('VAN2SHOUT_ASSETTARGET', 'Panel');
}

// Define the plugin:
$PluginInfo['Van2Shout'] = array(
	'Name' => 'Van2Shout',
	'Description' => 'A simple shoutbox for vanilla2 with support for different groups and private messages',
	'Version' => '1.1',
	'Author' => "Caerostris",
	'AuthorEmail' => 'caerostris@gmail.com',
	'AuthorUrl' => 'http://caerostris.com',
	'SettingsPermission' => array('Plugins.Van2Shout.View', 'Plugins.Van2Shout.Post', 'Plugins.Van2Shout.Delete', 'Plugins.Van2Shout.Colour'),
	'RegisterPermissions' => array('Plugins.Van2Shout.View', 'Plugins.Van2Shout.Post', 'Plugins.Van2Shout.Delete', 'Plugins.Van2Shout.Colour'),
);

class Van2ShoutPlugin extends Gdn_Plugin {
	public function PluginController_Van2ShoutData_Create($Sender) {
		//Check if user is allowed to view
		$Session = GDN::Session();
			if(!$Session->CheckPermission('Plugins.Van2Shout.View')) {
			return;
		}


		//Displays the posts of the shoutbox

		include_once(dirname(__FILE__).DS.'controllers'.DS.'class.van2shoutdata.php');
		$Van2ShoutData = new Van2ShoutData($Sender);
		echo $Van2ShoutData->ToString();
	}

	public function Base_GetAppSettingsMenuItems_Handler($Sender) {
		$Menu = $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Add-ons', T('Van2Shout'), 'settings/van2shout', 'Garden.Settings.Manage');
	}

	public function SettingsController_Van2Shout_Create($Sender) {
		$Sender->Permission('Garden.Plugins.Manage');
		$Sender->AddSideMenu();
		$Sender->Title('Van2Shout Settings');

		$ConfigurationModule = new ConfigurationModule($Sender);
		$ConfigurationModule->RenderAll = True;
		$Schema = array();
		$RoleModel = new RoleModel();
		$Roles = $RoleModel->Get();

		$Schema['Plugin.Van2Shout.FBUrl'] = array('LabelCode' => 'FBUrl', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.FBUrl', ''));
		$Schema['Plugin.Van2Shout.FBSecret'] = array('LabelCode' => 'FBSecret', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.FBSecret', ''));
		$Schema['Plugin.Van2Shout.ContentAsset'] = array('LabelCode' => 'ContentAsset', 'Control' => 'Checkbox', 'Default' => C('Plugin.Van2Shout.ContentAsset', true));
		$Schema['Plugin.Van2Shout.Timestamp'] = array('LabelCode' => 'Timestamp', 'Control' => 'Checkbox', 'Default' => C('Plugin.Van2Shout.Timestamp', false));
		$Schema['Plugin.Van2Shout.SendText'] = array('LabelCode' => 'SendText', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.SendText', 'Send'));
		$Schema['Plugin.Van2Shout.TimeColour'] = array('LabelCode' => 'TimeColour', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.TimeColour', 'grey'));
		$Schema['Plugin.Van2Shout.Interval'] = array('LabelCode' => 'Interval', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.Interval', '5000'));
		$Schema['Plugin.Van2Shout.MsgCount'] = array('LabelCode' => 'MsgCount', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.MsgCount', '50'));

		while($role = $Roles->Value('Name', NULL))
		{
			$Schema['Plugins.Van2Shout.'.$role] = array('LabelCode' => $role, 'Control' => 'Input', 'Default' => C('Plugins.Van2Shout.'.$role, ''));
		}

		$ConfigurationModule->Schema($Schema);
		$ConfigurationModule->Initialize();
		$Sender->ConfigurationModule = $ConfigurationModule;
		$Sender->Render(dirname(__FILE__) . DS . 'views' . DS . 'settings.php');
	}

	public function Base_Render_Before($Sender) {
		if(VAN2SHOUT_ASSETTARGET != 'Panel')
			return;

		$this->includev2s($Sender);
	}

	public function DiscussionsController_Render_Before($Sender) {
		if(VAN2SHOUT_ASSETTARGET != 'Content')
			return;

		$this->includev2s($Sender);
	}

	public function ProfileController_AfterAddSideMenu_Handler($Sender) {
		$Session = GDN::Session();
		$SideMenu = $Sender->EventArguments['SideMenu'];
		$ViewingUserID = $Session->UserID;

		if($Sender->User->UserID == $ViewingUserID && $Session->CheckPermission('Plugins.Van2Shout.Colour'))
		{
			$SideMenu->AddLink('Options', T('Van2Shout Settings'), '/profile/van2shout', FALSE, array('class' => 'Popup'));
		}
	}

	public function ProfileController_Van2Shout_Create($Sender) {

		$Session = GDN::Session();
		$UserModel = new UserModel();

		if(!$Session->CheckPermission('Plugins.Van2Shout.Colour'))
			return;

		//Get the data
		$UserMetaData = $this->GetUserMeta($Session->UserID, '%');
		$ConfigArray = array('Plugin.Van2Shout.UserColour' => NULL);

		if($Sender->Form->AuthenticatedPostBack() === FALSE)
		{
			$ConfigArray = array_merge($ConfigArray, $UserMetaData);
			$Sender->Form->SetData($ConfigArray);
		}
		else
		{
			$Values = $Sender->Form->FormValues();
			$FrmValues = array_intersect_key($Values, $ConfigArray);

			$UserRoles = $UserModel->GetRoles($Session->UserID);

			foreach($FrmValues as $MetaKey)
			{
				foreach($UserRoles as $UserRole)
				{
					if($UserRole["Name"] == $MetaKey)
					{
						$this->SetUserMeta($Session->UserID, "Colour", $UserRole["Name"]);
						$Sender->StatusMessage = T("Your changes have been saved.");
					}
				}
			}
		}

		$Sender->Render($this->GetView('usersettings.php'));
	}

	private function includev2s($Sender)
	{
		$Session = GDN::Session();
		if($Session->CheckPermission('Plugins.Van2Shout.View'))
		{
			//Include firebase script?
			if(USE_FIREBASE)
			{
				$Sender->Head->AddString("\n<script src='https://cdn.firebase.com/v0/firebase.js'></script>");
			}

			//Display the delete icon?
			if($Session->CheckPermission('Plugins.Van2Shout.Delete'))
			{
				$Sender->AddDefinition('Van2ShoutDelete', 'true');
			}

			include_once(PATH_PLUGINS.DS.'Van2Shout'.DS.'modules'.DS.'class.van2shoutdiscussionsmodule.php');
			$Van2ShoutDiscussionsModule = new Van2ShoutDiscussionsModule($Sender);
			$Sender->AddModule($Van2ShoutDiscussionsModule);
		}
	}

	private function checkTableFormat()
	{
	}

	public function Setup() {
		$Construct = GDN::Structure();
		$Construct->Table('Shoutbox');

		if(!$Construct->TableExists())
		{
			$Construct->PrimaryKey('ID')
				->Column('UserName', 'varchar(50)')
				->Column('PM', 'varchar(50)')
				->Column('Content', 'varchar(150)')
				->Column('Timestamp', 'int(11)')
				->Set(FALSE, FALSE);
		}
	}
}
