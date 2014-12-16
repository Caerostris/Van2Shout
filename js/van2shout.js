jQuery(document).ready(function($) {
	$('#van2shout').prependTo('#Content');
	$('#van2shout').insertAfter('.Info');

	emojify.setConfig({
		emojify_tag_type: 'div',
		only_crawl_id: 'van2shout',
		img_dir: 'plugins/Van2Shout/img/emoji',
		ignore_tags: {
			'SCRIPT': 1,
			'A': 1,
			'PRE': 1,
			'CODE': 1
		}
	});
});

function checkLength() {
	if($("#shoutboxinput").val().length > 148) {
		$("#van2shoutsubmit").attr('disabled', 'disabled');
		$("#shoutboxinput").css("background-color", "red");
	} else {
		$("#van2shoutsubmit").removeAttr('disabled');
		$("#shoutboxinput").css("background-color", "white");
	}
}

function v2s_help() {
	gdn.informMessage('PM: /w {User}<br /><a href="' + gdn.url('profile/<?php echo $Session->UserName; ?>van2shout')  + '" target="_blank">Change your colour</a>');
}

//return html code of delete button
function DeleteBttn(id) {
	var str = "";
	if(gdn.definition('Van2ShoutDelete') == "true") {
		str = "<img src='" + gdn.definition('WebRoot') + "/plugins/Van2Shout/img/del.png' onClick='DeletePost(\"" + id + "\");' /> ";
	}
	return str;
}
