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

class Van2ShoutFirebase extends Gdn_Module {

	public function __connstruct($Sender = '') {
		parent::__construct($Sender);
	}

	public function ToString() {
		ob_start();
		$Session = GDN::Session();
		$UserModel = new UserModel();

		if(!empty($_GET["newtoken"]) && empty($_GET["reset_tokens"])) {
			// client requesting a new firebase token
			include_once(PATH_ROOT.DS.plugins.DS.'Van2Shout'.DS.'firebase'.DS.'v2s.php');
			echo fb_new_token();
		} else if(!empty($_GET["reset_tokens"]) && empty($_GET["newtoken"])) {
			// admin requesting a reset of all firebase tokens
			if(!$Session->CheckPermission('Garden.Settings.Manage'))
				return;

			include_once(PATH_ROOT.DS.plugins.DS.'Van2Shout'.DS.'firebase'.DS.'v2s.php');
			fb_reset_tokens();
		}

		$String = ob_get_contents();
		@ob_end_clean();
		return $String;
	}
}
