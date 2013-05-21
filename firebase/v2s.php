<?php
include_once(PATH_ROOT.DS.plugins.DS.'Van2Shout'.DS.'firebase'.DS.'FirebaseToken.php');

function fb_get_token()
{
	$Session = GDN::Session();
	$uname = $Session->User->Name;

	$metadata = Gdn::UserMetaModel()->GetUserMeta($Session->UserID, "Plugin.Van2Shout.FirebaseToken", "");
	$auth_token = $metadata['Plugin.Van2Shout.FirebaseToken'];

	if($auth_token == "")
	{
		$auth_token = fb_new_token();
	}

	return $auth_token;
}

function fb_new_token()
{
	$Session = GDN::Session();

	$tokenGen = new Services_FirebaseTokenGenerator(C('Plugin.Van2Shout.FBSecret', ''));
	$auth_token = $tokenGen->createToken(array("id" => $Session->User->Name));
	Gdn::UserMetaModel()->SetUserMeta($Session->UserID, "Plugin.Van2Shout.FirebaseToken", $auth_token);

	return $auth_token;
}
?>
