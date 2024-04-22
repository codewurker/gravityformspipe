function onSaveOk( streamName, streamDuration, userId, cameraName, micName, recorderId, audioCodec, videoCodec, fileType, videoId, audioOnly, location ) {

	// Prepare video details.
	var videoDetails = {
		thumbnail: 'https://' + location + '/' + gravityformspipe_frontend_strings.accountHash + '/' + streamName + '.jpg',
		video:     'https://' + location + '/' + gravityformspipe_frontend_strings.accountHash + '/' + streamName + '.mp4',
	};

	// Get input field.
	var inputField = jQuery( '#hdfvr-content' ).siblings( 'input[type="hidden"]' );

	// Save video URL.
	inputField.val( JSON.stringify( videoDetails ) );

}

function onVideoUploadSuccess( filename, filetype, videoId, audioOnly, location ) {

	// Prepare video URL.
	var videoDetails = {
		thumbnail: 'https://' + location + '/' + gravityformspipe_frontend_strings.accountHash + '/' + filename + '.jpg',
		video:     'https://' + location + '/' + gravityformspipe_frontend_strings.accountHash + '/' + filename + '.mp4',
	};

	// Get input field.
	var inputField = jQuery( '#hdfvr-content' ).siblings( 'input[type="hidden"]' );

	// Save video URL.
	inputField.val( JSON.stringify( videoDetails ) );

}
