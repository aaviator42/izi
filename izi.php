<?php
/*
izi - v4.7  |  2022-06-20
by @aaviator42
*/

//BCRYPT hash of password generated using password_hash()
//Default is '12345678' - PLEASE CHANGE THIS!!!
const IZI_PASSWORD_HASH = '$2y$10$o43k01OfwVJaYZ8/rKo4se09BWnlREHBc64rtA9ROLouy.2BBjPlq';

//Folder in which to store uploaded files, include trailing slash
const IZI_UPLOAD_DIR = "files/";

//Public URL to this folder, include HTTP(S) and trailing slash
const IZI_UPLOAD_DIR_URL = "https://example.com/izi/files/";

//Impose file size limit?
const IZI_FILE_SIZE_LIMIT = false;
const IZI_MAX_FILE_SIZE = 1024*1000*100; //1024*x = x kilobytes

//Impose file extension allowances?
//If enabled, only files with these extensions can be uploaded
const IZI_FILE_ALLOWANCES = false;
const IZI_VALID_FORMATS = array("jpeg", "txt", "jpg", "pdf", "png", "gif", "bmp");

//Impose file extension exclusions?
//If enabled, files with these extensions can NOT be uploaded
const IZI_FILE_EXCLUSIONS = true;
const IZI_INVALID_FORMATS = array("php", "phar", "phtml", "sh", "exe", "js");

//Enforce HTTPS?
const IZI_FORCE_HTTPS = true; 

//CONFIG ENDS HERE	
//---------------------

iniSettings();
enforceHTTPS();
session_start();

if(isset($_GET["m"])){
	if(!isLoggedIn()){
		//user is trying to access a page, but isn't logged in
		//we redirect to the login page
		redirect();	
	}
} else {
	//no page specified
	if(isLoggedIn()){
		//user logged in, take to upload page
		redirect("uploadFiles");
	} else {
		//user not logged in, print login form
		if(isset($_POST["password"])){
			//password submitted
			if(password_verify($_POST["password"], IZI_PASSWORD_HASH)){
				//password correct!
				$_SESSION["active"] = true;
				redirect("uploadFiles");
			} else {
				//password incorrect!
				$_SESSION["loginFail"] = true;
			}
		}
		printHeader("Login");
		printLoginForm();
		printFooter();
	}
}

//navigation
switch($_GET["m"]){
	case "logout":
		logout();
		break;
	case "myFiles":
		printHeader("My Files");
		printFileList();
		printFooter();
		break;
	case "uploadFiles":
		printHeader("Upload Files");
		if(isset($_POST["submit"])){
			processFileUpload();
		}
		printUploadForm();
		printFooter();
		break;
	case "deleteFiles":
		printHeader("Delete Files");
		deleteFiles();
		printFooter();
		break;
	case "viewConfig":
		printHeader("Current Configuration");
		printConfig();
		printFooter();
	default:
		//invalid page
		redirect(); //go home
		break;
}
		
exit(0);


function printHeader($page){
	echo <<<ENDEND
	
<!DOCTYPE html>
<!-- izi v4.7 by @aaviator42 -->
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>$page | izi</title>
	<style>
	body {
		font-family: Verdana, sans-serif !important;
		padding: 2rem;
		max-width: 50rem;
		margin: auto;
		font-size: 1rem !important;
		background-color: #e8ddc3;
	}
	code, pre {
		font-family: monospace;
		background-color: #d9cba9;
		white-space: pre-wrap;
	}
	table {
		width: 100%;
		border: 0.01rem solid;
		margin-left: auto;
		margin-right: auto;
		border-collapse: collapse;
		display: block;
		overflow-x: auto;
		white-space: nowrap;
	}
	table tbody {
		display: table;
		width: 99.9999%;
	}
	td {
		border: 0.01rem solid;
		vertical-align: text-top;
		padding: 1rem;
	}
	th {
		border: 0.01rem solid;
	}
	a, a:visited {
		color: green;
	}
	</style>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<meta name="robots" content="noindex, nofollow, noarchive">

</head>
<body>
	<script>
		function copyText(p1){
			navigator.clipboard.writeText(p1);
		}
	</script>
	<h2><u><a href="?">izi</a></u></h2>
	<h4><i>&gt; $page</i></h4>

ENDEND;
	
}

function printFooter(){
	if(isLoggedIn()){
		echo<<<ENDEND
	
	<hr>
	<small>	<a href="?m=logout">Logout</a> | 
		<a href="?m=uploadFiles">Upload Files</a> | 
		<a href="?m=myFiles">My Files</a> | 
		<a href="?m=deleteFiles">Delete Files</a> | 
		<a href="?m=viewConfig">View Config</a> | 
		<a href="https://github.com/aaviator42/izi">Help/Source</a></small>
</body>
</html>

ENDEND;
	} else {
		echo<<<ENDEND

	<hr>
	<small><a href="https://github.com/aaviator42/izi">Help/Source</a></small>
</body>
</html>

ENDEND;
	}

	exit(0);
}

function printConfig(){
	
	echo"
	<table>
	<tr><td colspan = 2><b>izi settings</b></td></tr>
	<tr><td><code>IZI_UPLOAD_DIR</code></td><td><code>" . IZI_UPLOAD_DIR  ."</code></td></tr>
	<tr><td><code>IZI_UPLOAD_DIR_URL</code></td><td><code>" . IZI_UPLOAD_DIR_URL  ."</code></td></tr>
	<tr><td>Enforce <code>IZI_FILE_SIZE_LIMIT</code>?</td><td><code>" . (int)IZI_FILE_SIZE_LIMIT  ."</code></td></tr>
	<tr><td><code>IZI_MAX_FILE_SIZE</code> (bytes)</td><td><code>" . IZI_MAX_FILE_SIZE  ."</code></td></tr>
	<tr><td>Enforce <code>IZI_FILE_ALLOWANCES</code>?</td><td><code>" . (int)IZI_FILE_ALLOWANCES  ."</code></td></tr>
	<tr><td><code>IZI_VALID_FORMATS</code></td><td><code>" . implode(", ", IZI_VALID_FORMATS)."</code></td></tr>
	<tr><td>Enforce <code>IZI_FILE_EXCLUSIONS</code>?</td><td><code>" . (int)IZI_FILE_EXCLUSIONS  ."</code></td></tr>
	<tr><td><code>IZI_INVALID_FORMATS</code></td><td><code>" . implode(", ", IZI_INVALID_FORMATS)  ."</code></td></tr>
	<tr><td>Enforce <code>IZI_FORCE_HTTPS</code>?</td><td><code>" . (int)IZI_FORCE_HTTPS  ."</code></td></tr>
	<tr><td colspan = 2><b>php ini settings</b></td></tr>
	<tr><td><code>file_uploads</code></td><td><code>" . ini_get('file_uploads')  ."</code></td></tr>
	<tr><td><code>post_max_size</code></td><td><code>" . ini_get('post_max_size')  ."</code></td></tr>
	<tr><td><code>upload_max_filesize</code></td><td><code>" . ini_get('upload_max_filesize')  ."</code></td></tr>
	<tr><td><code>max_input_time</code></td><td><code>" . ini_get('max_input_time')  ."</code></td></tr>
	<tr><td><code>memory_limit</code></td><td><code>" . ini_get('memory_limit')  ."</code></td></tr>
	<tr><td><code>max_execution_time</code></td><td><code>" . ini_get('max_execution_time')  ."</code></td></tr>
	
	</table>";
}



function printLoginForm(){
	if(isset($_SESSION["loginFail"])){
		unset($_SESSION["loginFail"]);
		session_destroy();

		echo <<<ENDEND
		<table><tr><td>
		<form action="#" method="post">
			Password: <input type="password" name="password">
			<input type="submit" value="Login!" /><br><br>
			<b>[!]</b> Incorrect password!<br>
		</form></td></tr></table>
		
ENDEND;

	} else {
		echo <<<ENDEND
		<table><tr><td>
		<form action="#" method="post">
			Password: <input type="password" name="password">
			<input type="submit" value="Login!" />
		</form></td></tr></table>
ENDEND;
	}
}

function printUploadForm(){
	echo <<<ENDEND
	<table><tr><td>
	<form action="#" method="post" enctype="multipart/form-data">
	
ENDEND;
	if(IZI_FILE_SIZE_LIMIT){
		echo ' <input type="hidden" name="MAX_FILE_SIZE" value=' . IZI_MAX_FILE_SIZE . '/>';
	}
	
	echo <<<ENDEND
	  	<input type="file" id="file" name="files[]" multiple="multiple" onchange="javascript:updateList()" />
		<input type="submit" name="submit" value="Upload!" />
		
		<br><br><span id="listLabel"></span>
		<br><span id="fileList"></span>
	</form></td></tr></table>
	<script>
	updateList = function() {
		var input = document.getElementById('file');
		var label = document.getElementById('listLabel');
		var output = document.getElementById('fileList');
		label.innerHTML = "Selected files:";
		var children = "";
		for (var i = 0; i < input.files.length; ++i) {
			children += '<li>' + input.files.item(i).name + '</li>';
		}
		output.innerHTML = '<ul>'+children+'</ul>';
	}
	var input = document.getElementById('file');
	input.value = '';
	</script>
ENDEND;
	
}

function deleteFiles(){
	echo PHP_EOL . "		<table>";
	
	if(isset($_POST["delfiles"])){
		
		if(trim($_POST["delfiles"]) === "YOLO_DELETE_ALL"){
			$files = glob(IZI_UPLOAD_DIR . '*'); // get all file names
			foreach($files as $file){ // iterate files
				if(is_file($file)) {
					if(in_array($file, ["index.html", "index.php", "index.shtml"])){
						//don't delete index files
						continue;
					}
				unlink($file); // delete file
				}
			}
			echo "<tr><td>All files have been deleted.</td></tr>";
				
		} else {
		
			$files = preg_replace('#\s+#',',',trim($_POST["delfiles"]));
			$files = explode(",", $files);
			echo "<tr><td>";
			$count = 0;
			foreach($files as $filename){
				$filename = sanitize($filename);
				if(!empty($filename) && file_exists(IZI_UPLOAD_DIR . $filename)){
					unlink(IZI_UPLOAD_DIR . $filename);
					$count++;
					echo "<b>" . $filename . "</b> has been deleted.<br>";
				}
			}
			if($count == 0){
				echo "No files to delete.";
			}
			echo "</td></tr>";
		}
	}
	
	echo '<tr><td>
	<form action="#" method="post">
			<label for="delfiles">Enter file names here, separated by commas or newlines:</label><br>
			<textarea name="delfiles" id="delfiles" rows="3"></textarea><br>
			<input type="submit" value="Delete!" /><br><br>
	</form>
	
	To delete all files, type "YOLO_DELETE_ALL".
	
	</td></tr></table>';
}

function printFileList(){
	echo "	<table>";
	
	if(isset($_GET["del"])){
		//delete request made within one hour of file listing?
		//to prevent cached GET requests from deleting files
		if((time() - (int)$_GET["time"]) < 3600){ 
			$filename = sanitize($_GET["del"]);
			if(file_exists(IZI_UPLOAD_DIR . $filename)){
				unlink(IZI_UPLOAD_DIR . $filename);
				echo "<tr><td colspan='5'><b>" . $filename . "</b> has been deleted. </td></tr>" . PHP_EOL;
			}
		}
	}
	
	$files = array_diff(scandir(IZI_UPLOAD_DIR), array('.', '..'));
	$filecount = 0;
	foreach($files as $f){
		if(!is_file(IZI_UPLOAD_DIR . $f)){
			continue;
		}
		
		if(in_array($f, ["index.html", "index,php", "index.shtml"])){
			continue;
		}
		
		$filesize = round((filesize(IZI_UPLOAD_DIR . $f)/1000), 2); //KB
		$suffix = "KB";
		
		if($filesize > 1000){
			$filesize = round(($filesize/1000), 2); //MB
			$suffix = "MB";
		}
		if($filesize > 1000){
			$filesize = round(($filesize/1000), 2); //GB
			$suffix = "GB";
		}
		
		$filesize = number_format((float)$filesize, 2, '.', '');
		$filesize = $filesize . ' ' . $suffix;
		
		$URL = IZI_UPLOAD_DIR_URL . $f;
		echo "	<tr><td><a href=" . $URL . ">" . $f . "</a></td>" . PHP_EOL . 
		"	<td style='text-align: right;'>" . $filesize . "</td>" . PHP_EOL . 
		"	<td>" . date("Y-m-d H:i:s", filemtime(IZI_UPLOAD_DIR . $f)) . "</td>" . PHP_EOL .
		"	<td><a href='?m=myFiles&time=" . time() . "&del=" . $f . "'>delete</a></td>" . PHP_EOL . 
		"	<td><a href='#' onclick='copyText(\"" . $URL . "\")'>copy url</a></td></tr>" . PHP_EOL;
		$filecount++;
	}
	
	if(empty($files)){
		echo "<tr><td colspan='5'>No files to show.</td></tr>" . PHP_EOL;
	}
	echo"	<tr><td colspan='5'>Current date: " . date("Y-m-d H:i:s", time()) . "</td></tr>" . PHP_EOL;
	echo"	<tr><td colspan='5'>Number of files: " . $filecount . "</td></tr>";
	echo "</table>";
}

function processFileUpload(){
	$phpFileUploadErrors = array(
		0 => 'there is no error, the file uploaded with success',
		1 => 'the uploaded file exceeds the upload_max_filesize directive in php.ini',
		2 => 'the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		3 => 'the uploaded file was only partially uploaded',
		4 => 'no file was uploaded',
		6 => 'missing a temporary folder',
		7 => 'failed to write file to disk.',
		8 => 'a PHP extension stopped the file upload.',
	); 
	
	echo "<table><tr><td>";
	if($_FILES["files"]["name"][0] === ""){
		echo "No files uploaded.<br>";
	} else {
		foreach($_FILES['files']['name'] as $f => $name){
			$newname = substr(preg_replace('/\s+/', '', pathinfo($name,  PATHINFO_FILENAME)), 0, 20);
			$newname .= '-';
			$newname .= substr(md5(rand(0, 999999)), 0, 5);
			$newname .= '.';
			$newname .= pathinfo($name, PATHINFO_EXTENSION);
			
			echo "<b>$name: </b>";
			if($_FILES['files']['error'][$f] != 0){
				echo "Unable to upload this file [" . $phpFileUploadErrors[$_FILES['files']['error'][$f]] . "].";
				continue;
			}
			if(IZI_FILE_SIZE_LIMIT){
				if($_FILES['files']['size'][$f] > IZI_MAX_FILE_SIZE){
					echo "Unable to upload this file [file too large].";
					continue;
				}
			}
			if(IZI_FILE_ALLOWANCES){
				if(!in_array(pathinfo($name, PATHINFO_EXTENSION), IZI_VALID_FORMATS)){
					echo "Unable to upload this file [file extension not permitted].";
					continue;
				}
			}
			if(IZI_FILE_EXCLUSIONS){
				if(in_array(pathinfo($name, PATHINFO_EXTENSION), IZI_INVALID_FORMATS)){
					echo "Unable to upload this file [file extension not permitted].";
					continue;
				}
			}
			move_uploaded_file($_FILES["files"]["tmp_name"][$f], IZI_UPLOAD_DIR . $newname);
			echo "Uploaded as <a href=" . IZI_UPLOAD_DIR_URL . $newname . ">" . $newname . "</a> (<a href='#' onclick='copyText(\"" . IZI_UPLOAD_DIR_URL . $newname . "\")'>copy url</a>).<br>";
		}
	}
	echo "</td></tr></table>";
}



function logout(){
	$SCRIPT_NAME = basename($_SERVER["SCRIPT_FILENAME"]);
	session_destroy();
	header('Location: ' . $SCRIPT_NAME);
	echo "You have been logged out. Please click <a href='" . $SCRIPT_NAME . "'>here</a> to continue.";
	exit(0);
}

function login(){
	if(!isset($_POST["u_password"])){
		$_SESSION["loginFail"] = 1;
		redirect("login");
	}
	if(password_verify($_POST["u_password"], IZI_PASSWORD_HASH)){
		$_SESSION["active"] = 1;
		unset($_SESSION["loginFail"]);	
		redirect("login");
	}
}

function iniSettings(){
	$cookieLifetime = 60*60*24*7; //7 days
	ini_set( 'session.use_only_cookies', 	true);	// Use only cookies for session IDs
	ini_set( 'session.use_strict_mode', 	true);	// Accept only valid session IDs
	ini_set( 'session.use_trans_sid', 		false);	// Do not attach session ID to URLs
	ini_set( 'session.cookie_httponly', 	true);	// Refuse access to session cookies from JS
	ini_set( 'session.sid_length', 			48);			// Session ID length
	ini_set( 'session.cookie_samesite', 	"strict");		// Strict samesite
	ini_set( 'session.gc_maxlifetime', 		$cookieLifetime);	// Cookie lifetime
	ini_set( 'session.cookie_lifetime', 	$cookieLifetime);	// Cookie lifetime

}

function isLoggedIn(){
	if(!isset($_SESSION["active"])){
		return 0;
	} else {
		return 1;
	}
}

function goHome(){
	$SCRIPT_NAME = basename($_SERVER["SCRIPT_FILENAME"]);
	header('Location: ' . $SCRIPT_NAME);
	exit(0);
}

function redirect($page = NULL){
	$SCRIPT_NAME = basename($_SERVER["SCRIPT_FILENAME"]);
	
	if($page === NULL){
		header('Location: ' . $SCRIPT_NAME);
	} else {
		header('Location: ' . $SCRIPT_NAME . '?m=' . $page);
	}
	exit(0);
}

function enforceHTTPS(){
	if(IZI_FORCE_HTTPS){
		if($_SERVER["HTTPS"] != "on")
		{
			header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
		}
	}
}

function sanitize($string){
	$string = rtrim($string, "/\\.");
	preg_replace('/\s+/', '', $string);
	$string = str_replace('\\', '/', $string);
	$string = '/' . $string;
	$string = substr($string, strrpos($string, '/') + 1);
	return $string;
}