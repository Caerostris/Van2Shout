<?php
include_once(PATH_ROOT.DS.plugins.DS.'Van2Shout'.DS.'firebase'.DS.'FirebaseToken.php');

function fb_get_token() {
	$Session = GDN::Session();
	$uname = $Session->User->Name;

	$metadata = Gdn::UserMetaModel()->GetUserMeta($Session->UserID, "Plugin.Van2Shout.FirebaseToken", "");
	$auth_token = $metadata['Plugin.Van2Shout.FirebaseToken'];

	if($auth_token == "")
		$auth_token = fb_new_token();

	return $auth_token;
}

function fb_new_token() {
	$Session = GDN::Session();

	if(!$Session->CheckPermission('Plugins.Van2Shout.View'))
		return;

	$tokenGen = new Services_FirebaseTokenGenerator(C('Plugin.Van2Shout.Firebase.Secret', ''));

	$conf = array("id" => $Session->User->Name);

	if($Session->CheckPermission('Plugins.Van2Shout.Delete'))
		$conf["delete"] = true;

	if($Session->CheckPermission('Plugins.Van2Shout.Post'))
		$conf["post"] = true;

	$metadata = Gdn::UserMetaModel()->GetUserMeta($Session->UserID, 'Plugin.Van2Shout.Colour', 'Default');
	$conf["colour"] = C('Plugin.Van2Shout.'.$metadata['Plugin.Van2Shout.Colour'], '');

	$auth_token = $tokenGen->createToken($conf);
	Gdn::UserMetaModel()->SetUserMeta($Session->UserID, "Plugin.Van2Shout.FirebaseToken", $auth_token);

	return $auth_token;
}

function fb_reset_tokens() {
	if(GDN::Session()->CheckPermission('Garden.Settings.Manage'))
		GDN::SQL()->Delete('UserMeta', array('Name' => 'Plugin.Van2Shout.FirebaseToken'));
}
?>
