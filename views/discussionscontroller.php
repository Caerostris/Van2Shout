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

echo "<div id='van2shout' class='Box'>";
echo "<h4>".T('Shoutbox')."</h4>\n";
?>

<div id="van2shoutscroll">
	<ul id="shoutboxcontent">
	</ul>
</div>

<?php
	$Session = GDN::Session();
	if($Session->CheckPermission('Plugins.Van2Shout.Post'))
	{
		echo "<form action='javascript:SubmitMessage();'>\n<input type='text' style='width: 90%' name='shoutboxinput' id='shoutboxinput' onkeydown='checkLength();' />";
		echo "<img src='".Gdn::Request()->Domain()."/".Gdn::Request()->Webroot()."/applications/dashboard/design/images/progress.gif' style='display:none;' id='shoutboxloading' />\n";
		echo "<input type='submit' value='".T(C('Plugin.Van2Shout.SendText', 'Send'))."' id='van2shoutsubmit' name='van2shoutsubmit' class='Button' />\n</form>\n";
	}
?>