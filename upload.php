<?php
// Set the path (relative or absolute) to the directory to save image files
define('UPLOAD_PATH', '../upload/files');
// Set the URL (relative or absolute) to the directory defined above
define('UPLOAD_URI', '../upload/files');

/**
 * Function for handling asynchronous uploading of files to server.
 *
 * JSON-encoded string echoed by this function has the following format:
 * <ul>
 * <li>In case of error: <code>{ "error" : { "message" : "<error message>", "format" : "json" } }</code></li>
 * <li>In case of success:
 * <pre>
 * {
 *  "upload" : [
 *    {
 *      "file" : {
 *        "size" : file size in bytes,
 *        "name" : "file name as provided by client"
 *      },
 *      "links" : {
 *        "original" : "link to the file saved on server"
 *      },
 *      "info" : "description of file as provided by client"
 *    },
 *    { info about other uploaded files }
 *  ],
 *  "done" : 1
 * }
 * </pre>
 * </li>
 * </ul>
 *
 * @param $_GET['func'] string = uploadFiles
 * @param $_FILES['attachment'] array
 * Array of files.
 * @param $_POST['attachment-info'] array
 * Array of descriptions of files.
 * @param $_POST['upload-form-name'] string
 * Name of form. Needed for correct return of result.
 * @return
 * Echoes javascript string that is expected to be shown in a hidden iframe. It will call parent.uploadComplete(formName, data) function
 * where the first argument is name of the form that initiated upload ($_POST['upload-form-name']) and the second argument is JSON-encoded
 * string containing information about the uploaded files.
*/
function uploadFiles() {
	$upload_allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'txt', 'svg', 'pdf', 'doc', 'xls', 'html', 'htm', 'zip', 'rar');

	if (!is_dir(UPLOAD_PATH)) {
		uploadError('Upload directory ' . UPLOAD_PATH . ' must exist on the server');
	}
	if (!is_writable(UPLOAD_PATH)) {
		uploadError('Upload directory ' . UPLOAD_PATH . ' must have write permissions on the server');
	}

	$status = array();
	$files = $_FILES['attachment'];
	for ($i = 0; $i < count($files['name']); $i++) {
		if (!in_array($files['error'][$i], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
			uploadError('Upload failed');
		}
		if ($files['error'][$i] == UPLOAD_ERR_NO_FILE) {
			continue;
		}
		$max_upload_size = iniMaxUploadSize();
		$ext = strtolower(substr(strrchr($files['name'][$i], '.'), 1));
		if (!in_array($ext, $upload_allowed_extensions)) {
			uploadError('Invalid file ' . $files['name'][$i] . ', must be a valid file less than ' . bytesToReadable($max_upload_size));
		}
		$filename = uniqueId() . '.' . $ext;
		$path = UPLOAD_PATH . '/' . $filename;
		if (!move_uploaded_file($files['tmp_name'][$i], $path)) {
			uploadError('Server error, failed to move file');
		}
		$result['file']['size'] = $files['size'][$i];
		$result['file']['name'] = $files['name'][$i];
		$result['links']['original'] = uploadFileUri($filename);
		$result['info'] = $_POST['attachment-info'][$i];
		$status['upload'][] = $result;
	}
	$status['done'] = 1;
	// $status['files'] = print_r($_FILES, true);
	// $status['post'] = print_r($_POST, true);

	uploadOutput($status);
}

function uploadError($msg) {
	$status = array();
	$status['error']['message'] = $msg;
	$status['error']['format'] = 'json';
	uploadOutput($status);
}

function uploadOutput($status) {
	echo "<script language='javascript' type='text/javascript'>try { parent.uploadComplete('" . $_POST['upload-form-name'] . "', '"
		. json_encode($status) . "'); } catch (e) { alert(e.message); }</script>";
	exit;
}

function uploadFileUri($filename, $override_uri = '') {
	$prefix = strlen($override_uri) > 0 ? $override_uri : UPLOAD_URI;
	return $prefix . '/' . $filename;
}

function iniMaxUploadSize() {
	$post_size = ini_get('post_max_size');
	$upload_size = ini_get('upload_max_filesize');
	if (!$post_size) $post_size = '8M';
	if (!$upload_size) $upload_size = '2M';
	return min(iniBytesFromString($post_size), iniBytesFromString($upload_size));
}

function iniBytesFromString($val) {
	$val = trim($val);
	$last = strtolower($val[strlen($val) - 1]);
	switch ($last) {
		case 'g': // The 'G' modifier is available since PHP 5.1.0
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}
	return $val;
}

function bytesToReadable($bytes) {
	if ($bytes <= 0) {
		return '0 Byte';
	}
	$convention = 1000; // [1000 -> 10^x | 1024 -> 2^x]
	$s = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB');
	$e = floor(log($bytes, $convention));
	return round($bytes / pow($convention, $e), 2) . ' ' . $s[$e];
}

function uniqueId($length = 40) {
	return md5(randomString($length) . microtime());
}

function randomString($length) {
	$pool = "ABCDEFGHIJKMNPQRSTUVWXYZ23456789abcdefghijklmnopqrstuvwxyz";
	$sid = "";
	for ($index = 0; $index < $length; $index++) {
		$sid .= substr($pool, rand() % (strlen($pool)), 1);
	}
	return $sid;
}

switch ($_GET['func']) {
	case 'uploadFiles':
		uploadFiles();
		break;
	default:
		break;
}
?>
