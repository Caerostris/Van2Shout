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

class Van2ShoutData extends Gdn_Module {

	public function __connstruct($Sender = '') {
		parent::__construct($Sender);
	}

	public function ToString() {
		ob_start();
		$Session = GDN::Session();
		$UserModel = new UserModel();
		$SQL = GDN::SQL();

		if(isset($_GET["postcount"]) && empty($_POST["post"]) && empty($_GET["del"])) {
			if(!$Session->CheckPermission('Plugins.Van2Shout.View'))
				return;

			//Get data from mysql DB
			$posts = $SQL->Select('*')->From('Shoutbox')->BeginWhereGroup()->Where('PM', '')->OrWhere('PM', $Session->User->Name)->OrWhere('UserName', $Session->User->Name)->EndWhereGroup()->OrderBy('ID')->Get()->ResultArray();

			//I'd use the Limit function from vanillas SQL, but no matter where I put it, OrderBy just returns the OLDEST x messages
			$posts = array_slice($posts, $_GET["postcount"]*(-1), $_GET["postcount"], true);

			//Display data
			$json = array();
			foreach($posts as $post)
			{
				$color = "";
				$User = $UserModel->GetByUsername($post["UserName"]);

				if($User != null) {
					$metadata = Gdn::UserMetaModel()->GetUserMeta($User->UserID, 'Plugin.Van2Shout.Colour', 'Default');
					$color = C('Plugin.Van2Shout.'.$metadata['Plugin.Van2Shout.Colour'], '');
					if($color == 'Default')
						$color = '';
				}

				$pm = array(
					'type' => 'broadcast',
					'id' => $post["ID"],
					'user' => $post["UserName"],
					'time' => $post["Timestamp"],
					'color' => $color,
					'message' => $post["Content"]
				);

				// process as private message
				if($post["PM"] != "") {
					$pm['type'] = 'private';
					$pm['recipient'] = $post["PM"];
				}

				array_push($json, $pm);
			}

			echo json_encode($json, JSON_UNESCAPED_UNICODE);
		} else if(!empty($_POST["post"]) && empty($_GET["postcount"]) && empty($_GET["del"])) {
			if(!$Session->CheckPermission('Plugins.Van2Shout.Post'))
				return;

			$post = json_decode($_POST["post"], true);

			// check if message is set and within the max length
			if($post === null || empty($post['message']) || strlen($post['message']) > 148)
				return;

			// Filter XSS
			$post['message'] = htmlspecialchars($post['message'], null, 'utf-8');

			//Detect links starting with http:// or ftp://
			$post['message'] = preg_replace( '/(http|ftp)+(s)?:(\/\/)((\w|\.)+)(\/)?(\S+)?/i', '<a href="\0" target="blank">\0</a>', $post['message']);

			//in case the shoutbox is open to guests - whoever would do that...
			if($Session->User->Name == "") {
				$username = "Guest";
			} else {
				$username = $Session->User->Name;
			}

			if($post['recipient'] === null)
				$post['recipient'] = '';

			$SQL->Insert('Shoutbox', array(
				'UserName' => $username,
				'PM' => $post['recipient'],
				'Content' => $post['message'],
				'Timestamp' => time()
			));
		} else if(!empty($_GET["del"]) && empty($_POST["post"]) && empty($_GET["postcount"])) {
			if(!$Session->CheckPermission('Plugins.Van2Shout.Delete'))
				return;

			if(!is_numeric($_GET["del"]))
				return;

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
