<?php

class FatCloud {

	public static $fonts=array();
	public static $skins=array();
	/**
	 * URL of the core FatCloud SWF file
	 * @var String
	 */
	public static $SWF='FatCloud.swf';

	private static $firstRender=false;

	public $noXML=false;
	public static $saveSkinCacheURL='';

	public static $dbHost;
	public static $dbName;
	public static $dbUser;
	public static $dbPass;
	public static $dbTable;
	public static $dbKeyField;
	public static $dbKeyValue;
	public static $dbValueField;

	private static $dbConn=null;

	/**
	 * This function is called by FatCloud.php to initialise
	 * static variables $fonts and $skins from the XML file
	 */
	public static function init() {
		$xml=new SimpleXMLElement(file_get_contents(dirname(__FILE__).'/FatCloud.xml'));
		foreach($xml->fonts->font as $i) FatCloud::$fonts[]=trim((string)$i);
		foreach($xml->skins->skin as $i) FatCloud::$skins[]=new FatCloudSkin($i);
		self::$skinCacheFile=dirname(__FILE__).'/FatCloud.sc';
		self::$saveSkinCacheURL=$GLOBALS['fatCloudAjaxURL'];
	}

	private $id;
	private $skin;

	/**
	 * Skin options. Use <b>option</b> => <b>value</b> [...]
	 * @var Array
	 */
	public $options=array();

	public function __construct($id, $skin='') {
		$this->id=$id;
		$this->skin=$skin;
	}

	public function __toString() { return $this->renderJS(); }

	private function renderJS() {
		$var=$this->id.'_fatcloud';
		$r='<script type="text/javascript">/*<!--*/';
		$r.=sprintf('fatCloud.SWF="%s";',addslashes(FatCloud::$SWF));
		$r.=sprintf('var %s=new FatCloud("%s","%s");',$var,$this->id,$this->skin);
		if(function_exists('json_encode')) {
			$r.=sprintf('%s.options=%s;',$var,json_encode($this->options));
			if(!FatCloud::$firstRender) $r.=sprintf('fatCloud.fontList=%s;',json_encode(FatCloud::$fonts));
		} else {
			foreach($this->options as $k=>$i) $r.=sprintf('%s.options.%s="%s";',$var,$k,addslashes($i));
			if(!FatCloud::$firstRender) foreach(FatCloud::$fonts as $i) $r.=sprintf('fatCloud.fontList.push("%s");',addslashes($i));
		}
		if($this->noXML) $r.=sprintf('%s.noXML=true;',$var);
		if(self::$saveSkinCacheURL) $r.=sprintf('%s.saveSkinCacheURL="%s";',$var,addslashes(self::$saveSkinCacheURL));
		if($this->skin) {
			$cache=FatCloud::getSkinCache($this->id.'_'.$this->skin);
			if(is_array($cache)) if($cache[0]) $r.=sprintf('fatCloud.skinCache.%s_%s=["%s","%s"];',
				$this->id,
				$this->skin,
				addslashes($cache[0]),
				addslashes($cache[1]));
		}
        $r.=sprintf('document.getElementById("%s").style.visibility="hidden";',$this->id);
		$r.='/*-->*/</script>';
		FatCloud::$firstRender=true;
		return $r;
	}

	private static $skinCache;

	private static function dbConnect() {
		if(self::$dbConn) return true;
		if(self::$dbHost) {
			if(!(self::$dbConn=mysql_connect(self::$dbHost, self::$dbUser, self::$dbPass))) return false;
			if(!mysql_select_db(self::$dbName, self::$dbConn)) {
				mysql_close(self::$dbConn);
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * Save a skin's cache to local (server) storage.
	 * @param string Which skin's cache to save to
	 * @param mixed Current skin state. Can be used to determine current cache validity.
	 * @param mixed The data to save. Can be any non-custom type.
	 * @return boolean True on success, False on failure
	 */
	public static function setSkinCache($skin, $tags, $data) {
		if(!is_array(self::$skinCache)) self::loadSkinCache();
		if(!is_array(self::$skinCache)) self::$skinCache=array();
		self::$skinCache[$skin]=array($tags,$data);
		return self::saveSkinCache();
	}

	/**
	 * Recall a skin's last cache state and data
	 * @param string Which skin's cache to fetch
	 * @return array [ state in which the skin was saved, cache data ]
	 */
	public static function getSkinCache($skin) {
		if(!is_array(self::$skinCache)) self::loadSkinCache();
		if(is_array(self::$skinCache)) if(array_key_exists($skin, self::$skinCache)) return self::$skinCache[$skin];
		return array('','');
	}

	private static $skinCacheFile;
	
	private static function loadSkinCache() {
		if(function_exists('fcLoadSkinCache')) return self::$skinCache=unserialize(fcLoadSkinCache());
		if(self::dbConnect()) {
			$rlt=mysql_query(sprintf('SELECT `%s` FROM `%s` WHERE `%s`=%s LIMIT 1',
					self::$dbValueField,
					self::$dbTable,
					self::$dbKeyField,
					is_numeric(self::$dbKeyValue)?self::$dbKeyValue:"'".mysql_real_escape_string(self::$dbKeyValue)."'"),
				self::$dbConn);
			if($row=mysql_fetch_array($rlt, MYSQL_NUM)) {
				return self::$skinCache=unserialize($row[0]);
			}
		}
		if(is_file(self::$skinCacheFile)) return self::$skinCache=unserialize(gzuncompress(file_get_contents(self::$skinCacheFile)));
		return self::$skinCache=array();
	}

	private static function saveSkinCache() {
		if(function_exists('fcSaveSkinCache')) return fcSaveSkinCache(serialize(self::$skinCache));
		if(self::dbConnect()) {
			$t=self::$dbTable;
			$kf=self::$dbKeyField;
			$vf=self::$dbValueField;
			$kv=is_numeric(self::$dbKeyValue)?self::$dbKeyValue:"'".mysql_real_escape_string(self::$dbKeyValue)."'";
			$v=mysql_real_escape_string(serialize(self::$skinCache));
			mysql_query($q="UPDATE `$t` SET `$vf`='$v' WHERE `$kf`=$kv LIMIT 1",self::$dbConn);
			if(!mysql_affected_rows(self::$dbConn))
				mysql_query("INSERT INTO `$t` (`$kf`,`$vf`) VALUES ($kv,'$v')",self::$dbConn);
			return !mysql_error(self::$dbConn);
		}
		try {
			file_put_contents(self::$skinCacheFile, gzcompress(serialize(self::$skinCache)));
		} catch(Exception $e) {
			return false;
		}
		return true;
	}

	public static function processAjax() {
		if(isset($_GET['setSkinCache'])) {
			if(get_magic_quotes_gpc()) $_POST=array_map('stripslashes', $_POST);
			self::setSkinCache($_GET['setSkinCache'], $_POST['tags'], $_POST['data']);
			exit;
		}	
	}

}

class FatCloudSkin {

	/**
	 * The friendly (full) name of this skin, used only in configuration UIs.
	 * @var String
	 */
	public $name;

	/**
	 * The class (short) name of this skin, which can be passed to FatCloud
	 * class constructors in PHP, JavaScript and ActionScript.
	 * @var String 
	 */
	public $shortName;

	/**
	 * A description of the skin which can be used in configuration UIs.
	 * @var String
	 */
	public $description;

	/**
	 * A list of this skin's options, as an array of FatCloudSkinOption objects.
	 * @var Array
	 */
	public $options=array();

	public function __construct($xml) {
		$options=null;
		foreach($xml as $k=>$i) switch(strtolower($k)) {
			case 'name':		$this->name=trim($i);			break;
			case 'shortname':	$this->shortName=trim($i);		break;
			case 'description':	$this->description=trim($i);	break;
			case 'options':		$options=$i;
		}
		if(!$this->name) $this->name=$this->shortName;
		if($options!==null) foreach($options->option as $i) $this->options[]=new FatCloudSkinOption($i);
	}

}

class FatCloudSkinOption {

	/**
	 * The friendly (full) name of this option, used only in configuration UIs.
	 * @var String
	 */
	public $name;

	/**
	 * The programatic (short) name of this option, which can be passed to
	 * FatCloud class constructors in PHP, JavaScript and ActionScript.
	 */
	public $shortName;

	/**
	 * An explanation of what this option does.
	 * @var String
	 */
	public $description;

	/**
	 * The data type stored in this option. Valid types include:
	 * <ul>
	 * <li>string</li>
	 * <li>number</li>
	 * <li>color</li>
	 * <li>font</li>
	 * <li>enum</li>
	 * </ul>
	 * @var String
	 */
	public $type;

	/**
	 * The default value for this option.
	 * @var Mixed
	 */
	public $default;

	/**
	 * If this option is of type <b>enum</b>, this array will hold a
	 * list of its possible values.
	 * @var Array
	 */
	public $enum;

	public function __construct($xml) {
		foreach($xml as $k=>$i) if(preg_match('`^(name|shortName|description|default)$`i',$k)) {
			$k=strtolower($k);
			if($k=='shortname') $k='shortName';
			$this->$k=trim($i);
		}
		$this->type=strtolower(trim($xml->attributes()->type));
		if(!$this->type) $this->type='string';
		if($xml->enum) foreach($xml->enum->value as $i) $this->enum[]=trim($i);
		else unset($this->enum);
	}

}

FatCloud::init();

if(!isset($fatCloudAjaxURL)) FatCloud::processAjax();

?>