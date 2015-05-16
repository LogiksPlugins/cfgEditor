<?php
if(!defined('ROOT')) exit('No direct script access allowed');
session_check(true);
user_admin_check(true);

if(isset($_REQUEST["forsite"])) {
	checkUserSiteAccess($_REQUEST["forsite"]);
} else {
	echo "<style>body {overflow:hidden;}</style>";
	dispErrMessage("Requested Site Not Defined.","404:Not Found",404);
	exit();
}

loadModule("page");
_js("commons");

$layout="apppage";
$params=array("toolbar"=>null,"contentarea"=>"printContent");

printPageContent($layout,$params);
function printContent() {
	$noDispArr=array();
	$defaultCfgFile=ROOT."config/lists/configs.lst";
	if(file_exists($defaultCfgFile)) {
		$cfgData=file_get_contents($defaultCfgFile);
		$noDispArr=explode("\n",$cfgData);
	}
	$rlink="?site=".SITENAME."&forsite={$_REQUEST["forsite"]}&page=configeditor&popup=true&cfg=";
?>
<style>
.tabPage {
	width:100%;height:500px;
	overflow:auto;
	padding:0px !important;margin:0px !important;
}
#pageArea .src,#pageArea .layout {
	display:none;
}
input[type=text] {
	border:1px solid #aaa;
	width:95%;
}
select#reportselector {
	font:bold 18px Georgia;
	background:white;
	width:100%;height:100%;
	overflow:auto !important;
	border:0px;
}
#logpage {
	margin:0px;padding:0px;border:0px;
}
select option {
	color:#000;
}
</style>
<div style='width:100%;height:100%;'>
	<div style='width:25%;height:100%;float:left;overflow:hidden;'>
		<select id=reportselector size=2 onchange='loadConfig();' class='ui-widget-content ui-corner-all'>
			<?php
				$f=ROOT.APPS_FOLDER.$_REQUEST["forsite"]."/config/";
				if(is_dir($f)) {
					$fs=scandir($f);
					unset($fs[0]);unset($fs[1]);
					if(count($fs)>0) echo "<optgroup label='Application Settings' class='clr_blue'>";
					foreach($fs as $lf) {
						if(is_file($f.$lf) && strtolower(strstr($lf,"."))==".cfg" && !in_array($lf,$noDispArr)) {
							$x=substr($lf,0,strlen($lf)-4);
							$t=_ling($x."_Settings");
							$t=toTitle($t);
							echo "<option value='$x'>".$t."</option>";
						}
					}
					if(count($fs)>0) echo "</optgroup>";
				}
				$f=ROOT.APPS_FOLDER.$_REQUEST["forsite"]."/config/features/";
				if(is_dir($f)) {
					$fs=scandir($f);
					unset($fs[0]);unset($fs[1]);
					if(count($fs)>0) echo "<optgroup label='Modules/Widgets Settings' class='clr_blue'>";
					foreach($fs as $lf) {
						if(is_file($f.$lf) && strtolower(strstr($lf,"."))==".cfg" && !in_array($lf,$noDispArr)) {
							$x=substr($lf,0,strlen($lf)-4);
							$t=_ling($x."_Features");
							$t=toTitle($t);
							echo "<option value='features/$x'>".$t."</option>";
						}
					}
					if(count($fs)>0) echo "</optgroup>";
				}
			?>
		</select>
	</div>
	<iframe id=logpage class='page ui-widget-content' style='width:75%;height:100%;float:right;overflow:hidden;'>
	</iframe>
</div>
<script language=javascript>
$(function() {
	$(".tabPage").css("height",($("#pgworkspace").height()-35)+"px");
	$("select").addClass("ui-state-active ui-corner-all");

	printMsg("No Settings Selected ...");
});
function loadConfig() {
	if($("#reportselector").val()==null) {
		return;
	}
	s="<div id=logpgmsg class='logpgmsg ui-widget-header'>";
	s+="<div class='ajaxloading'>Loading Log Report ...</div>";
	s+="</div>";
	printMsg();
	$("#logpage").addClass("ajaxloading");
	loadLogReport($("#reportselector").val());
}
function loadLogReport(lg) {
	if(lg==null) return;
	lnk="<?=$rlink?>"+lg;
	$("#logpage").get(0).src=lnk;
}
function printMsg(msg,icon) {
	if(icon==null && msg!=null) icon="media/images/notfound/process.png";
	$("#logpage").get(0).src="about:blank";
	if(msg!=null) {
		s="<div id=logpgmsg style='width:50%;height:40%;margin:auto;margin-top:10%;padding:20px;'>";
		s+="<h1 style='font-size:2em;' align=center><img src='"+icon+"' width=48px height=48px alt='.' style='margin-right:30px;'/><br/>"+msg+"</h1>";
		s+="</div>";
		$("#logpage").get(0).contentWindow.document.write(s);
	}
}
</script>
<?php
}
?>
