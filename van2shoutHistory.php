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
$Session = Gdn::Session();
if(!$Session->CheckPermission('Plugins.Van2Shout.View')) {
	return "";
}
?>
<h4>Shoutbox</h4>
<ul>
	<?php
		$dblink = mysql_connect(C('Database.Host'),C('Database.User'),C('Database.Password'), FALSE, 128);
		mysql_select_db(C('Database.Name'));
		$messages = mysql_query("SELECT * FROM GDN_Shoutbox ORDER BY EntryID;");
		if(!$messages || !$dblink) {
			echo "<li>Some fatal error with mysql db</li>";
			echo "<br />".mysql_error($dblink);
			mysql_close($dblink);
		}
		mysql_close($dblink);

		$text = array();
		$fontopen = '';
		$fontclose = '';
		if(GDN::Config('Van2Shout.TextColor') != ""){
			$fontopen = "<font color='".GDN::Config('Van2Shout.TextColor')."'>";
			$fontclose = "</font>";
		}
		while ($msg = mysql_fetch_assoc($messages)) {
			if($msg["Username"] == " "){ continue; }
			if(!strstr($msg['data'], "/w ")){
				if($Session->CheckPermission('Plugins.Van2Shout.Delete')) {
					array_push($text, "<li><strong><a href=\"javascript:remove('".$msg['EntryID']."');\"><img src='plugins/Van2Shout/rem.png' width:'20px' height:'20px' /></a><a href='?p=profile/".urlencode($msg['Username'])."'>".$msg['Username']."</a>: ".$fontopen.wordwrap($msg['data'],30," ",true).$fontclose."</strong></li>");
				} else {
					array_push($text, "<li><strong><a href='?p=profile/".urlencode($msg['Username'])."'>".$msg['Username']."</a>: ".$fontopen.wordwrap($msg['data'],30," ",true).$fontclose."</strong></li>");
				}
			} else {
				$cut = explode("/w ", $msg['data']);
				$cut = explode(" ", $cut[1]);
				$user = $cut[0];
				$message = "";
				$i = 0;
				foreach($cut as $data){
					if($i != 0){
						$message .= $data." ";
					}
					$i++;
				}
				if($Session->User->Name == $user){
					if($Session->CheckPermission('Plugins.Van2Shout.Delete')) {
						array_push($text, "<li><strong><a href=\"javascript:remove('".$msg['EntryID']."');\"><img src='plugins/Van2Shout/rem.png' width:'20px' height:'20px' /></a>PM from <a href='?p=profile/".urlencode($msg['Username'])."'>".$msg['Username']."</a>: ".$fontopen.wordwrap($message,30," ",true).$fontclose."</strong></li>");
					} else {
						array_push($text, "<li><strong>PM from <a href='?p=profile/".urlencode($msg['Username'])."'>".$msg['Username']."</a>: ".$fontopen.wordwrap($message,30," ",true).$fontclose."</strong></li>");
					}
				} elseif($Session->User->Name == $msg['Username']){
					if($Session->CheckPermission('Plugins.Van2Shout.Delete')) {
						array_push($text, "<li><strong><a href=\"javascript:remove('".$msg['EntryID']."');\"><img src='plugins/Van2Shout/rem.png' width:'20px' height:'20px' /></a>PM to <a href='?p=profile/".urlencode($user)."'>".$user."</a>: ".$fontopen.wordwrap($message,30," ",true).$fontclose."</strong></li>");
					} else {
						array_push($text, "<li><strong>PM to <a href='?p=profile/".urlencode($user)."'>".$user."</a>: ".$fontopen.wordwrap($messaage,30," ",true).$fontclose."</strong></li>");
					}
				}
			}
		}
		for($i = count($text); $i > -1; $i--) {
			$text_last[count($text)-$i] = $text[$i];
			if($i == count($text)-$maxMessages) { break; }
                }

		foreach($text as $print) {
			echo $print;
		}
	?>
</ul>
<?php
