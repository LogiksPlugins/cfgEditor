<?php
if(!defined('ROOT')) exit('No direct script access allowed');
user_admin_check(true);

if(isset($_REQUEST["cfg"])) {
	if(strlen($_REQUEST["cfg"])>0) {
		include "editor.php";
	} else {
		echo "<style>body {overflow:hidden;}</style>";
		dispErrMessage("Config Request Not Found.","404:Not Found",404);
	}
} else {
	include "manager.php";
}
function findCfgFile($f) {
	if(file_exists(ROOT."config/$f.cfg")) {
		return "config/$f.cfg";
	}
	return "";
}
function findAppCfgFile($f) {
	if(file_exists(ROOT.APPS_FOLDER.$_REQUEST["forsite"]."/$f.cfg")) {
		return APPS_FOLDER.$_REQUEST["forsite"]."/$f.cfg";
	} elseif(file_exists(ROOT.APPS_FOLDER.$_REQUEST["forsite"]."/config/$f.cfg")) {
		return APPS_FOLDER.$_REQUEST["forsite"]."/config/$f.cfg";
	}
	return "";
}
?>

