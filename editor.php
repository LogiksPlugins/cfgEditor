<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include "cfgschema.php";

_js(array("dialogs","jquery.multiselect"));
_css(array("styletags","jquery.multiselect","formfields"));

function loadCfgFile($file,$schemas=null, $cfgParams=null) {
	$webPath=getWebPath(__FILE__);
	$rootPath=getRootPath(__FILE__);

	if(!isset($_REQUEST["forsite"])) $_REQUEST["forsite"]=SITENAME;

	$xPath=$file;
	$bPath=trim(APPS_FOLDER.$_REQUEST["forsite"]."/config/");
	$xPath=substr($xPath,0,strlen($xPath)-4);
	if(strpos($xPath,$bPath)===0) {
		$l1=strlen($bPath);
		$xPath=substr($xPath,$l1);
	} elseif(strpos($xPath,APPS_FOLDER.$_REQUEST["forsite"]."/")===0) {
		$l1=strlen(APPS_FOLDER.$_REQUEST["forsite"]."/");
		$xPath=substr($xPath,$l1);
	} else {
		$l1=basename($xPath);
	}

	$title=basename($file);
	$title=substr($title,0,strlen($title)-4);
	$title=ucwords($title);

	$cfgSchema=new CFGSchema();
	if($schemas!=null) {
		if(is_array($schemas)) {
			foreach($schemas as $a=>$b) {
				$f=$cfgSchema->searchSchema($file, $b);
				if(strlen($f)>0) {
					$cfgSchema->loadSchema($f);
				}
			}
		} else {
			$f=$cfgSchema->searchSchema($file, $schemas);
			if(strlen($f)>0) {
				$cfgSchema->loadSchema($f);
			}
		}
	}
	if($cfgParams!=null) {
		$dispArr=loadCFGData($file);
		$arr=array();
		$foundArr=array();
		foreach($cfgParams as $a=>$x) {
			if(is_array($x)) {
				$arr[$a]=array();
				foreach($x as $m=>$b) {
					if(isset($dispArr[0][$b])) {
						$d1=$dispArr[0][$b];
						if(isset($dispArr[1][$d1][$b])) {
							$d2=$dispArr[1][$d1][$b];
							$arr[$a][$b]=$d2;
							array_push($foundArr,$b);
						}
					}
				}
			} else {
				$b=$x;
				if(isset($dispArr[0][$b])) {
					$d1=$dispArr[0][$b];
					if(isset($dispArr[1][$d1][$b])) {
						$d2=$dispArr[1][$d1][$b];
						$arr[$b]=$d2;
						array_push($foundArr,$b);
					}
				}
			}
		}
		if(isset($arr['Others']) && count($arr['Others'])==0) {
			foreach($dispArr[0] as $a=>$x) {
				if(!in_array($a,$foundArr)) {
					$d2=$dispArr[1][$x][$a];
					$arr["Others"][$a]=$d2;
				}
			}
		}
		$dispArr=$arr;
	} else {
		$dispArr=loadCFGData($file);
		$dispArr=$dispArr[1];

		if($cfgSchema->isSetupDefined("CFG_GROUPS")) {
			$arr=array();
			$arrGroups=array();
			$arr1=$cfgSchema->getSetup("CFG_GROUPS");

			foreach($arr1 as $a=>$b) {
				$arr[$a]=array();
				if(is_array($b)) {
					foreach($b as $x) {
						$arrGroups[$x]=$a;
					}
				}
			}
			//if(!isset($arr["Others"])) $arr["Others"]=array();
			foreach($dispArr as $a=>$b) {
				foreach($b as $m=>$n) {
					if(isset($arrGroups[$m])) {
						$arr[$arrGroups[$m]][$m]=$n;
					} elseif(isset($arr["Others"])) {
						$arr["Others"][$m]=$n;
					}
				}
			}
			$dispArr=$arr;
		} else {
			$arr=array();
			foreach($dispArr as $a=>$b) {
				foreach($b as $m=>$n) {
					$arr[$m]=$n;
				}
			}
			$dispArr=$arr;
		}
	}
	if(isset($dispArr["Others"]) && count($dispArr["Others"])<=0) {
		unset($dispArr["Others"]);
	}
	$_SESSION["CFG_DATA"]=$dispArr;

$adminCfg=parseListFile("admincfgs");
$cfgName=str_replace(".cfg","",basename($file));
if(!in_array($cfgName,$adminCfg)) {
$extraToolBar="||
			<button title='Add New Field.' onclick='addNewField()'><div class='addicon'> Add</div> </button>
			||";
} else $extraToolBar="";
?>
<link href='<?=$webPath?>style.css' rel='stylesheet' type='text/css' media='all' />
<script src='<?=$webPath?>script.js' type='text/javascript' language='javascript'></script>
<div style='width:100%;height:100%;overflow:auto;overflow:hidden;'>
	<div id=toolbar class="toolbar ui-widget-header">
		<div class='left' style='margin-left:5px;'>
			<button title='Save Change In Configurations' onclick="submitForm('#cfg_workspace')"><div class='saveicon'> Save</div> </button>
			<button title='Cancel Change In Configurations' onclick="resetForm('#cfg_workspace')"><div class='reseticon'> Cancel</div> </button>
			<?=$extraToolBar?>
			<button title='Toggle Actual Tag.' onclick="toggleTags()"><div class='tagicon'> Tags</div> </button>
			<button title='Show Help Contents.' onclick="showHelp()"><div class='helpicon'> Help</div> </button>
		</div>
		<div class='right' style='margin-right:5px;'>
			<h2>Editing :: <b><?=$title?></b></h2>
		</div>
	</div>
	<div id=cfg_workspace class="cfg_workspace">
		<table class='settingstable' border=0 cellpadding=0 cellspacing=0 style='width:100%;'>
			<?php
				foreach($dispArr as $t=>$x) {
					if(is_array($x)) {
						echo "<tr class='subheader'><td colspan=2 class='ui-state-active'>$t</td></tr>";
						foreach($x as $a=>$b) {
							$t=$a;
							$b=str_replace("\"","'",$b);
							$o="";
							$tips="";
							$popup="";
							if($b=="true" || $b=="false") {
								$o="<select id=$a name=$a value='$b'>";
								if(strtolower($b)=="true") $o.="<option value='true' selected>True</option>"; else $o.="<option value='true'>True</option>";
								if(strtolower($b)=="false") $o.="<option value='false' selected>False</option>"; else $o.="<option value='false'>False</option>";
								$o.="</select>";
							} elseif($b=="on" || $b=="off") {
								$o="<select id=$a name=$a value='$b'>";
								if(strtolower($b)=="true") $o.="<option value='on' selected>On</option>"; else $o.="<option value='on'>On</option>";
								if(strtolower($b)=="false") $o.="<option value='off' selected>Off</option>"; else $o.="<option value='off'>Off</option>";
								$o.="</select>";
							} elseif($cfgSchema->isSetupDefined($a)) {
								$oA=$cfgSchema->getHTML($a,$b);
								$o=$oA["html"];
								$tips=$oA["tips"];
								$popup=$oA["popup"];
							} else {
								$o="<input id=$a name=$a type=text value=\"$b\" />";
							}
							printRow(array($t,$o,$tips,$popup),3);
						}
					} else {
						$b=$x;
						$a=$t;
						$t=$a;
						$b=str_replace("\"","'",$b);
						$o="";
						$tips="";
						$popup="";
						if($b=="true" || $b=="false") {
							$o="<select id=$a name=$a value='$b'>";
							if(strtolower($b)=="true") $o.="<option value='true' selected>True</option>"; else $o.="<option value='true'>True</option>";
							if(strtolower($b)=="false") $o.="<option value='false' selected>False</option>"; else $o.="<option value='false'>False</option>";
							$o.="</select>";
						} elseif($b=="on" || $b=="off") {
							$o="<select id=$a name=$a value='$b'>";
							if(strtolower($b)=="true") $o.="<option value='on' selected>On</option>"; else $o.="<option value='on'>On</option>";
							if(strtolower($b)=="false") $o.="<option value='off' selected>Off</option>"; else $o.="<option value='off'>Off</option>";
							$o.="</select>";
						} elseif($cfgSchema->isSetupDefined($a)) {
							$oA=$cfgSchema->getHTML($a,$b);
							$o=$oA["html"];
							$tips=$oA["tips"];
							$popup=$oA["popup"];
						} else {
							$o="<input id=$a name=$a type=text value=\"$b\" />";
						}
						printRow(array($t,$o,$tips,$popup));
					}
				}
			?>
		</table>
	</div>
</div>
<div id=msgdiv class='ui-state-highlight ui-corner-all'>Message Displayed Here</div>
<div style='display:none'>
	<div id='helpInfo' class='helpInfo' title='Help !' style='width:100%;text-align:justify;font-size:15px;font-family:verdana;'>
		<b>Configurations</b>, helps you manage the Overall Configurations that are used by the installation and the appSites. Here you
		practically configure may features.
	</div>
</div>
<script>
cfgFile="<?=$xPath?>";
<?php
if(isset($_GET['forsite'])) {
	echo 'submitLink="'.SiteLocation.'services/?scmd=cfgedit&site='.SITENAME.'&forsite='.$_REQUEST['forsite'].'&action=save&cfgfile="+cfgFile;';
} else {
	echo 'submitLink="'.SiteLocation.'services/?scmd=cfgedit&site='.SITENAME.'&action=save&cfgfile="+cfgFile;';
}
?>
</script>
<? }
function loadCFGData($file) {
	if(!file_exists($file)) {
		exit("Required Config File Missing");
	} else {
		$data=file_get_contents($file);
		$out=array();
		$mst=array();
		$data=explode("\n",$data);
		$mode="DEFINE";
		foreach($data as $a=>$s) {
			if(substr($s,0,2)=="//") continue;
			if(substr($s,0,1)=="#") continue;
			if(strlen($s)>0) {
				$n1=strpos($s, "=");
				if($n1>0) {
					$name=substr($s,0,$n1);
					$value=substr($s,$n1+1);
					$out[$mode][$name]=$value;
					$mst[$name]=$mode;
				} else {
					if($s=="[DEFINE]") $mode="DEFINE";
					elseif($s=="[SESSION]") $mode="SESSION";
					elseif($s=="[CONFIG]") $mode="CONFIG";
					elseif($s=="[DBCONFIG]") $mode="DBCONFIG";
					elseif($s=="[PHPINI]") $mode="PHPINI";
					elseif($s=="[ENV]") $mode="ENV";
					elseif($s=="[COOKIE]") $mode="COOKIE";
					else $mode="DEFINE";
				}
			}
		}
		return array($mst,$out);
	}
}
function printRow($arr) {
	$t=toTitle($arr[0]);
	if(isset($arr[1])) $o=$arr[1]; else $o="";
	if(isset($arr[2])) $tips=$arr[2]; else $tips="";
	if(isset($arr[3])) $popup=$arr[3]; else $popup="";
	if(strlen($popup)>0) {
		if(strpos(".".$popup,"url#")==1) {
			$popup=substr($popup,4,strlen($popup)-4);
			$popup="<div class='linkicon' title='$tips' onclick=\"popupLink(this,'Help On : $t')\"><div class='popupdata' style='display:none'>$popup</div></div>";
		} elseif(strpos(".".$popup,"js#")==1) {
			$popup=substr($popup,3,strlen($popup)-3);
			$popup="<div class='btnicon' title='$tips' onclick=\"openJS(this,'Help On : $t')\"><div class='popupdata' style='display:none'>$popup</div></div>";
		} else {
			$popup="<div class='popupicon' title='$tips' onclick=\"popupInfo(this,'Help On : $t')\"><div class='popupdata' style='display:none'>$popup</div></div>";
		}
	}
	echo "<tr class='datarow'>";
	echo "<td class='title' title='{$arr[0]}'>$t :</td>";
	echo "<td class='value'>$o</td>";
	echo "<td class='tips'>$popup $tips</td>";
	echo "</tr>";
}
unset($_SESSION["CFG_DATA"]);
?>
