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
class Van2ShoutModule extends Gdn_Module {
	public function __connstruct(&$Sender = '') {
		parent::__construct($Sender);
	}
	public function AssetTarget() {
		return 'Panel';
	}
	public function ToString() {
		$String = '';
		$Session = Gdn::Session();
		ob_start();
		if(!$Session->CheckPermission('Plugins.Van2Shout.View')) {
			return "";
		}
		?>
		<div id="Van2Shout" class="Box">
			<div id="shoutboxText">
			</div>
			<?php
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
				if($Session->CheckPermission('Plugins.Van2Shout.Post')) {
					echo "<div id='postDIV'></div>";
				}
				if($Session->CheckPermission('Plugins.Van2Shout.Delete')) {
					echo "<br /><a href='javascript:clearall()'>[clear all]</a>";
				}
				if($Session->CheckPermission('Plugins.Van2Shout.ViewHistory')){
					echo "<br /><a href='".$webroot."?p=plugin/van2shouthistory'> [view history] </a>";
				}
				echo "<br /><a href='#' onClick='gdn.inform(\"Type /help to get this help.<br />Type /w {username} {message} to send a private message\");'>[help]</a>";
			?>
		</div>
		<?php
		$String = ob_get_contents();
		@ob_end_clean();
		return $String;
	}
}
