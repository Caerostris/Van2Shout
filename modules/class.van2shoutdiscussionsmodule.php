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

class Van2ShoutDiscussionsModule extends Gdn_Module {
	public function __construct($Sender = '') {
		parent::__construct($Sender);
	}

	public function AssetTarget() {
		return VAN2SHOUT_ASSETTARGET;
	}

	public function ToString() {
		$Session = Gdn::Session();
		if(!$Session->CheckPermission('Plugins.Van2Shout.View')) {
			return "";
		}

		$String = '';

		ob_start();
		require(PATH_PLUGINS.DS.'Van2Shout'.DS.'views'.DS.'discussionscontroller.php');
		$String = ob_get_contents();
		@ob_end_clean();

		return $String;
	}
}
