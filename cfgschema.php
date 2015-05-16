<?php
include ROOT."config/timezones.php";
include ROOT."config/datetimes.php";

class CFGSchema {
	private $cfgSetup=array();

	public function __construct() {
		$this->cfgSetup["FONT"]=array(
				"type"=>"list",
				"function"=>"getFontList",
				"tips"=>"Select Font",
			);
		$this->cfgSetup["COLOR"]=array(
				"type"=>"list",
				"field"=>"color",
			);
		$this->cfgSetup["DATE_FORMAT"]=array(
				"type"=>"list",
				"function"=>"getDateFormatList",
			);
		$this->cfgSetup["TIME_FORMAT"]=array(
				"type"=>"list",
				"function"=>"getTimeFormatList",
			);
		$this->cfgSetup["TIMEZONES"]=array(
				"type"=>"list",
				"function"=>"getTimeZones"
			);
	}
	public function loadSchema($schemaFile) {
		if(file_exists($schemaFile) && is_file($schemaFile)) {
			include $schemaFile;
			if(isset($cfgSchema)) {
				foreach($cfgSchema as $a=>$b) {
					$this->cfgSetup[strtoupper($a)]=$b;
				}
			}
		}
	}
	public function isSetupDefined($title) {
		if(isset($this->cfgSetup[strtoupper($title)])) {
			return true;
		}
		if(strpos(strtolower($title),"color")>0 && strpos(strtolower($title),"color")==strlen($title)-5) {
			return true;
		}
		if(strpos(strtolower($title),"font")>0 && strpos(strtolower($title),"font")==strlen($title)-4) {
			return true;
		}
		if(strpos(strtolower($title),"timezone")>0 && strpos(strtolower($title),"timezone")==strlen($title)-8) {
			return true;
		}
		return false;
	}
	public function getSetup($title) {
		if(isset($this->cfgSetup[strtoupper($title)])) {
			return $this->cfgSetup[strtoupper($title)];
		}
		if(strpos(strtolower($title),"color")>0 && strpos(strtolower($title),"color")==strlen($title)-5) {
			return $this->cfgSetup["COLOR"];
		}
		if(strpos(strtolower($title),"font")>0 && strpos(strtolower($title),"font")==strlen($title)-4) {
			return $this->cfgSetup["FONT"];
		}
		if(strpos(strtolower($title),"timezone")>0 && strpos(strtolower($title),"timezone")==strlen($title)-8) {
			return $this->cfgSetup["TIMEZONES"];
		}
		return null;
	}

	public function getHTML($a,$v) {
		$r=$this->getSetup($a);
		if($r==null) $r=array();

		$out="";
		$attr="";
		$class="";
		$popup="";
		if(isset($r["attrs"]) && strlen($r["attrs"])>0) $attr=$r["attrs"];
		if(isset($r["popup"]) && strlen($r["popup"])>0) $popup=$r["popup"];
		if(isset($r["src"]) && strlen($r["src"])>0) {
			$src=$r["src"];
			$attr.="src='$src'";
		}
		if(isset($r["class"]) && strlen($r["class"])>0) {
			$class=$r["class"];
			$class="CLASS='$class'";
		}

		if(isset($r["type"])) {
			if($r["type"]=="list") {
				if(isset($r["values"])) {
					$arr=$r["values"];
					$out.=$this->printList($arr,$a,$v,$attr,$class);
				} elseif(isset($r["function"])) {
					if(method_exists($this,$r["function"])) {
						$arr=array();
						if(PHP_VERSION_ID<50000) {
							$arr=call_user_func(array(&$this, $r["function"]),$a,$v);
						} else {
							$arr=call_user_func(array($this, $r["function"]),$a,$v);
						}
						$out.=$this->printList($arr,$a,$v,$attr,$class);
					} elseif(function_exists($r["function"])) {
						$arr=call_user_func($r["function"]);
						$out.=$this->printList($arr,$a,$v,$attr,$class);
					} else {
						$out.="<b style='color:maroon;'>Not Supported Yet !</b>";
					}
				} elseif(isset($r["field"])) {
					$field=$r["field"];
					$out="<input id=$a name=$a class='$field' type=text value='$v' $attr $class/>";
				}
			} elseif($r["type"]=="string") {
				$out="<input id=$a name=$a type=text value='$v' $attr $class/>";
			}
		} else {
			$out="<input id=$a name=$a type=text value='$v' $attr $class/>";
		}
		$tips="";
		if(isset($r["tips"])) {
			$tips=$r["tips"];
		}
		return array("html"=>$out,"tips"=>$tips,"popup"=>$popup);
	}

	public function printList($arr,$t,$v="",$attr="",$class="") {
		$out="";
		$attr=str_replace("readonly","disabled",$attr);
		$out="<select id=$t name=$t value='$v' $attr $class>";

		$keys=array_keys($arr);
		$values=array_values($arr);
		$keys=implode("",$keys);
		$values=implode("",$values);

		if(is_numeric($keys)) {
			$arr=array_flip($arr);
			foreach($arr as $a=>$b) {
				$arr[$a]=$a;
			}
		}
		if(is_numeric($values)) {
			foreach($arr as $a=>$b) {
				$arr[$a]=$a;
			}
		}
		if(strpos("##".$attr,"multiple")>=2) {
			$v=explode(",",$v);
		}
		if(is_array($v)) {
			foreach($arr as $b=>$a) {
				if(in_array($a,$v)) $out.="<option value='$a' selected>$b</option>";
				else $out.="<option value='$a'>$b</option>";
			}
		} else {
			foreach($arr as $b=>$a) {
				if($a==$v) $out.="<option value='$a' selected>$b</option>";
				else $out.="<option value='$a'>$b</option>";
			}
		}
		$out.="</select>";
		return $out;
	}

	//Special Functions
	public function searchSchema($file, $schemas) {
		$fx=ROOT.$file;
		if(file_exists($fx)) $file=$fx;
		$schemas=basename($schemas);
		$arr=array(
				dirname($file)."/schemas/$schemas.php",
				dirname($file)."/$schemas.php",
				ROOT.$schemas,
				ROOT."config/schemas/$schemas.php",
			);
		if(defined("APPROOT")) {
			array_push($arr,APPROOT."config/schemas/$schemas.php");
		}
		foreach($arr as $a) {
			if(file_exists($a) && is_file($a)) {
				return $a;
			}
		}
		return "";
	}

	public function getFontList($f=null) {
		$f=ROOT.FONTS_FOLDER;
		$arr=array();
		$fs=scandir($f);
		unset($fs[0]);unset($fs[1]);
		foreach($fs as $b) {
			if(is_file($f.$b)) {
				if(strpos(strtolower($b),".ttf")!=strlen($b)-4) continue;
				$t=str_replace("_"," ",$b);
				$t=substr($t,0,strlen($t)-4);
				$t=ucwords($t);
				$arr[$t]=$b;
			} elseif(is_dir($f.$b)) {
				$f1="{$f}{$b}/";
				$fs1=scandir($f1);
				unset($fs1[0]);unset($fs1[1]);
				foreach($fs1 as $x) {
					if(strpos(strtolower($x),".ttf")!=strlen($x)-4) continue;
					$t=str_replace("_"," ",$x);
					$t=substr($t,0,strlen($t)-4);
					$t=ucwords($t);
					$arr[$t]="{$b}/{$x}";
				}
			}
		}
		natsort($arr);
		return $arr;
	}
}
?>
