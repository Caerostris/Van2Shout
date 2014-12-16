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

// Define the plugin:
$PluginInfo['Van2Shout'] = array(
	'Name' => 'Van2Shout',
	'Description' => 'A simple shoutbox for vanilla2 with support for different groups and private messages',
	'Version' => '1.053',
	'Author' => 'Caerostris',
	'AuthorEmail' => 'caerostris@gmail.com',
	'AuthorUrl' => 'http://caerostris.com',
	'SettingsUrl' => '/dashboard/settings/van2shout',
	'SettingsPermission' => 'Garden.Settings.Manage',
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
		$Menu->AddLink('Site Settings', T('Van2Shout'), 'settings/van2shout', 'Garden.Settings.Manage');
	}

	public function SettingsController_Van2Shout_Create($Sender) {
		$Sender->Permission('Garden.Settings.Manage');
		$Sender->AddSideMenu();
		$Sender->Title('Van2Shout Settings');

		$ConfigurationModule = new ConfigurationModule($Sender);
		$ConfigurationModule->RenderAll = True;
		$Schema = array();
		$RoleModel = new RoleModel();
		$Roles = $RoleModel->Get();

		$Schema['Plugin.Van2Shout.Firebase.Enable'] = array('LabelCode' => 'Firebase.Eanble', 'Control' => 'Checkbox', 'Default' => C('Plugin.Van2Shout.Firebase.Enable', false));
		$Schema['Plugin.Van2Shout.Firebase.Url'] = array('LabelCode' => 'Firebase.Url', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.Firebase.Url', ''));
		$Schema['Plugin.Van2Shout.Firebase.Secret'] = array('LabelCode' => 'Firebase.Secret', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.Firebase.Secret', ''));
		$Schema['Plugin.Van2Shout.DisplayTarget.DiscussionsController'] = array('LabelCode' => 'AssetContent', 'Control' => 'Checkbox', 'Default' => C('Plugin.Van2Shout.DisplayTarget.DiscussionsController', true));
		$Schema['Plugin.Van2Shout.Timestamp'] = array('LabelCode' => 'Timestamp', 'Control' => 'Checkbox', 'Default' => C('Plugin.Van2Shout.Timestamp', true));
		$Schema['Plugin.Van2Shout.SendText'] = array('LabelCode' => 'SendText', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.SendText', 'Send'));
		$Schema['Plugin.Van2Shout.TimeColour'] = array('LabelCode' => 'TimeColour', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.TimeColour', 'grey'));
		$Schema['Plugin.Van2Shout.Interval'] = array('LabelCode' => 'Interval', 'Control' => 'Input', 'Default' => C('Plugin.Van2Shout.Interval', '5'));
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

	public function DiscussionsController_Render_Before($Sender) {
		if(!C('Plugin.Van2Shout.DisplayTarget.DiscussionsController', true))
			return;

		$this->includev2s($Sender, 'Content');
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
						include_once(PATH_ROOT.DS.plugins.DS.'Van2Shout'.DS.'firebase'.DS.'v2s.php');
						new_token();
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
			$Sender->AddJsFile('emojify.min.js', 'plugins/Van2Shout/js');

			//Include firebase script?
			if(C('Plugin.Van2Shout.Firebase.Enable', false))
			{
				$Sender->Head->AddString("\n<script src='https://cdn.firebase.com/v0/firebase.js'></script>");

				$metadata = Gdn::UserMetaModel()->GetUserMeta($Session->UserID, "Plugin.Van2Shout.Colour", "");
	        	$usercolor = C('Plugins.Van2Shout.'.$metadata['Plugin.Van2Shout.Colour'], '');
				$Sender->AddDefinition('Van2ShoutUserColor', $usercolor);

				$Sender->AddDefinition('Van2ShoutFirebaseUrl', C('Plugin.Van2Shout.Firebase.Url', ''));

				include_once(PATH_ROOT.DS.plugins.DS.'Van2Shout'.DS.'firebase'.DS.'v2s.php');
				$Sender->AddDefinition('Van2ShoutFirebaseToken', fb_get_token());
			} else {
				$Sender->AddDefinition('Van2ShoutUpdateInterval', C('Plugin.Van2Shout.Interval', 5) * 1000);
			}

			//Display the delete icon?
			if($Session->CheckPermission('Plugins.Van2Shout.Delete'))
			{
				$Sender->AddDefinition('Van2ShoutDelete', 'true');
			}
			$Sender->AddDefinition('UserName', $Session->User->Name);
			$Sender->AddDefinition('Van2ShoutTimestamp', C('Plugin.Van2Shout.Timestamp', true) ? 'true' : 'false');
			$Sender->AddDefinition('Van2ShoutTimeColor', C('Plugin.Van2Shout.TimeColour', 'grey'));
			$Sender->AddDefinition('Van2ShoutMessageCount', C('Plugin.Van2Shout.MsgCount', '50'));

			include_once(PATH_PLUGINS.DS.'Van2Shout'.DS.'modules'.DS.'class.van2shoutdiscussionsmodule.php');
			$Van2ShoutDiscussionsModule = new Van2ShoutDiscussionsModule($Sender);
			$Sender->AddModule($Van2ShoutDiscussionsModule);
		}
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
