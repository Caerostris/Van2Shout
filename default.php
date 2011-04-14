<?php if(!defined('APPLICATION')) exit();
//Copyright (c) 2010-2011 by Caerostris <caerostris@gmail.com>
//    This file is part of Van2Shout.
//
//    Van2Shout is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Van2Shout is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Van2Shout.  If not, see <http://www.gnu.org/licenses/>.


// Define the plugin:
$PluginInfo['Van2Shout'] = array(
   'Name' => 'Van2Shout',
   'Description' => 'A simple shoutbox for vanilla2 with support for diffrent groups and private messages',
   'Version' => '0.5.6',
   'Author' => "Caerostris",
   'AuthorEmail' => 'caerostris@gmail.com',
   'AuthorUrl' => 'http://caerostris.com',
		'SettingsPermission' => array('Plugins.Van2Shout.View', 'Plugins.Van2Shout.Post', 'Plugins.Van2Shout.Delete', 'Plugins.Van2Shout.ViewHistory'),
		'RegisterPermissions' => array('Plugins.Van2Shout.View', 'Plugins.Van2Shout.Post', 'Plugins.Van2Shout.Delete', 'Plugins.Van2Shout.ViewHistory')
);
class Van2ShoutPlugin extends Gdn_Plugin {
	public function PluginController_Van2ShoutHistory_Create(&$Sender){
		$Session = Gdn::Session();
		if(!$Session->CheckPermission('Plugins.Van2Shout.ViewHistory')){
			return;
		}
		$Sender->View = dirname(__FILE__).DS.'van2shoutHistory.php';
		$Sender->Render();
	}
	public function PluginController_Van2ShoutText_Create(&$Sender) {
		$Session = Gdn::Session();
		if(!$Session->CheckPermission('Plugins.Van2Shout.View')) {
			return;
		}
		include_once(PATH_PLUGIN.DS.'Van2Shout'.DS.'class.van2Shouttext.php');
		$Van2ShoutText = new Van2ShoutText($Sender);
		echo $Van2ShoutText->ToString();
	}
	public function Base_Render_Before(&$Sender) {
		$Session = Gdn::Session();
		$dblink = mysql_connect(C('Database.Host'),C('Database.User'),C('Database.Password'), FALSE, 128);
		if(!$dblink) {
                                mysql_close($dblink);
                                return;
                }
		mysql_select_db(C('Database.Name'));
		$check = mysql_query("SELECT * FROM GDN_Shoutbox;");
		//Check if table exists, create if table doesnt exist.
		if(!$check) {
			mysql_query("CREATE TABLE GDN_Shoutbox (EntryID int(255)AUTO_INCREMENT, PRIMARY KEY(EntryID), Username varchar(256), data varchar(256));", $dblink);
			$check = mysql_query("SELECT * FROM GDN_Shoutbox;");
			if(!$check) {
				//Maybe wrong password for mysql db? dunno...
				return;
			}
		}

		if(isset($_GET["clearall"]) && $_GET["clearall"] == "1") {
			if(!$Session->CheckPermission('Plugins.Van2Shout.Delete')) {
				return;
			}
			$mysqlcmd = mysql_query("DELETE FROM GDN_Shoutbox;", $dblink);
			if(!$mysqlcmd){
				mysql_close($dblink);
				return;
			}
			$mysqlcmd = mysql_query("INSERT INTO GDN_Shoutbox (Username, data) values ('System', '".GDN::Translate('Database has been cleared')."');", $dblink);
			if(!$mysqlcmd){
				mysql_close($dblink);
				return;
			}
		}
		if(isset($_GET["msg"])) {
			$Session = Gdn::Session();
			if($_GET["msg"] == "") {
				return;
			}
			if(!$Session->CheckPermission('Plugins.Van2Shout.Post')) {
				return;
			}
			if($Session->User->Name == "") {
				$username = "Guest";
			} else {
				$username = $Session->User->Name;
			}
			if(strlen($_GET['msg']) > 150){ return; }
			$filtered = wordwrap(htmlspecialchars(str_replace("'", "\'", $_GET['msg'])),30," ",true);
			$message = preg_replace( '/(http|ftp)+(s)?:(\/\/)((\w|\.)+)(\/)?(\S+)?/i', '<a href="\0" target="blank">\0</a>', $filtered);
			$mysqlcmd = mysql_query("INSERT INTO GDN_Shoutbox (Username, data) values ('".$username."','".$message."');", $dblink);
			if(!$mysqlcmd) {
				mysql_close($dblink);
				return;
			}
		}
		if(isset($_GET["rm"])) {
			$Session = Gdn::Session();
			if(!$Session->CheckPermission('Plugins.Van2Shout.Delete')) {
				return;
			}
			$mysqlcmd = mysql_query("DELETE FROM GDN_Shoutbox WHERE EntryID = ".$_GET['rm'].";", $dblink);
			if(!mysqlcmd) {
				mysql_close($dblink);
				return;
			}
		}
		mysql_close($dblink);
		include_once(PATH_PLUGINS.DS.'Van2Shout'.DS.'class.van2shoutmodule.php');
		$Van2ShoutModule = new Van2ShoutModule($Sender);
		$Sender->AddModule($Van2ShoutModule);
		$Sender->AddJsFile("/plugins/Van2Shout/van2shout.js");
		$Sender->AddDefinition('user', $Session->User->Name);

		$webroot = Gdn::Request()->Webroot();
		if($webroot == "") {
			$webroot = "/";
		}
		if(substr($webroot,0,1) != "/" && $webroot != "/") {
			$webroot = "/".$webroot;
		}
		if(substr($webroot,0,strlen($webroot)) != "/" && $webroot != "/") {
			$webroot = $webroot."/";
		}
		$Sender->AddDefinition('Van2ShoutRootpath', $webroot);
	}
}
