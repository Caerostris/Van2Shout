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

//Override vanilla's default encoding UTF-8, with UTF-8 e.g. eblah² doesnt work (the ²)
header('Content-Type: text/html; charset=ISO-8859-15');

class Van2ShoutData extends Gdn_Module {

	public function __connstruct($Sender = '') {
		parent::__construct($Sender);
	}

	public function ToString() {

		ob_start();
		$Session = GDN::Session();
		$UserModel = new UserModel();
		$SQL = GDN::SQL();

		if(isset($_GET["postcount"]) && empty($_GET["newpost"]) && empty($_GET["del"])) {
			if(!$Session->CheckPermission('Plugins.Van2Shout.View')) {
				return;
			}

			//Display posts - format: User{,}post1[,]User{,}post2[,]... (other characters might be used while shoutboxing

			//Get data from mysql DB
			$posts = $SQL->Select('*')->From('Shoutbox')->BeginWhereGroup()->Where('PM', '')->OrWhere('PM', $Session->User->Name)->OrWhere('UserName', $Session->User->Name)->EndWhereGroup()->OrderBy('ID')->Limit($_GET["postcount"])->Get()->ResultArray();

			//Display data
			foreach($posts as $msg)
			{
				$colour = "";
				$User = $UserModel->GetByUsername($msg["UserName"]);

				if($User != null)
				{
					$metadata = Gdn::UserMetaModel()->GetUserMeta($User->UserID, "Plugin.Van2Shout.Colour", "");
					$colour = C('Plugins.Van2Shout.'.$metadata['Plugin.Van2Shout.Colour']);
					if(!$colour) { $colour = "Default"; }
					if($colour == "Default") { $colour = ""; }
				}

				if($msg["PM"] == "")
				{
					$delimeter = "[!content!]";
					echo $colour."[!colour!]".$msg["UserName"].$delimeter.$msg["Content"]."[!msgid!]".$msg["ID"]."[!msgtime!]".$msg["Timestamp"]."\n";
				}
				elseif($msg["PM"] != "" && $msg["UserName"] == $Session->User->Name && $msg["PM"] != $Session->User->Name)
				{
					$delimeter = "[!pmtocontent!]"; //User can see this PM because he sent it.
					echo $colour."[!colour!]".$msg["PM"].$delimeter.$msg["Content"]."[!msgid!]".$msg["ID"]."[!msgtime!]".$msg["Timestamp"]."\n"; //Display in the following format: receiver[!pmtocontent!]content instead of sender[delimeter]content
				}
				else
				{
					$delimeter = "[!pmcontent!]";
					echo $colour."[!colour!]".$msg["UserName"].$delimeter.$msg["Content"]."[!msgid!]".$msg["ID"]."[!msgtime!]".$msg["Timestamp"]."\n";
				}
			}
		}

		if(!empty($_GET["newpost"]) && empty($_GET["postcount"]) && empty($_GET["del"])) {
			if(!$Session->CheckPermission('Plugins.Van2Shout.Post')) {
				return;
			}

			//On some systems, the $_GET variables are mysql_real_escaped for some reason... Let's undo this!
			$searchstring = array("\\\\", "\\n", "\\r", "\\Z", "\\'", '\\"');
  			$replacestring = array("\\", "\n", "\r", "\x1a", "'", '"');
			$string = str_replace($searchstring, $replacestring, $_GET["newpost"]);

			//Check if message is to long
			if(strlen($_GET["newpost"]) > 148) { return; }

			if(stristr($_GET["newpost"], "[!pmcontent!]") || stristr($_GET["newpost"], "[!content!]") || stristr($_GET["newpost"], "[!msgid!]") || stristr($_GET["newpost"], "[!colour!]")) {
				return;
			}

			//Filter XSS and MySQL injections
			$string = htmlspecialchars($string);
			//Detect links starting with http:// or ftp://
			$string = preg_replace( '/(http|ftp)+(s)?:(\/\/)((\w|\.)+)(\/)?(\S+)?/i', '<a href="\0" target="blank">\0</a>', $string);

			//Is the shoutbox open to everyone?
			if($Session->User->Name == "") {
				$username = "Guest";
			} else {
				$username = $Session->User->Name;
			}

			$pm = "";
			//Is it a PM?
			if(substr($string, 0, 3) == "/w "){
				$cut = explode("/w ", $string);
				$cut = explode(" ", $cut[1]);

				$pm = $cut[0];
				//Okay, we got the username, now we need to reassemble message
				$string = "";
				$i = 0;
				foreach($cut as $data){
					if($i != 0) {
						$string .= $data." ";
					}
					$i++;
				}
			}

			$SQL->Insert('Shoutbox', array(
				'UserName' => $username,
				'PM' => $pm,
				'Content' => $string,
				'Timestamp' => time()
			));
		}

		if(!empty($_GET["del"]) && empty($_GET["newpost"]) && empty($_GET["postcount"])) {
			if(!$Session->CheckPermission('Plugins.Van2Shout.Delete')) {
				return;
			}

			if(!is_numeric($_GET["del"])) {
				return;
			}

			//Delete post from mysql DB
			$SQL->Delete('Shoutbox', array(
				'ID' => $_GET["del"]
			));
		}

		$String = ob_get_contents();
		@ob_end_clean();

		return $String;
	}
}
