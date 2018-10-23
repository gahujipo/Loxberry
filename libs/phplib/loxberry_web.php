<?php

require_once "loxberry_system.php";
// require_once "phphtmltemplate_loxberry/template040.php";

class LBWeb
{
	public static $LBWEBVERSION = "1.2.5.5";
	
	public static $lbpluginpage = "/admin/system/index.cgi";
	public static $lbsystempage = "/admin/system/index.cgi?form=system";
		
	///////////////////////////////////////////////////////////////////
	// prints the head
	///////////////////////////////////////////////////////////////////
	public static function head($pagetitle = "")
	{
		// error_log("loxberry_web: Head function called -->");
		global $template_title;
		global $htmlhead;
		
		# If a global template_title is set, use it
		if ($template_title !== "") {
			$pagetitle = $template_title;
		}
		$lang = LBSystem::lblanguage();
		
		$fulltitle = $pagetitle !== "" ? lbfriendlyname() . " " . $pagetitle : lbfriendlyname();
		$fulltitle = trim($fulltitle);
		if ($fulltitle === "") {
			$fulltitle = "LoxBerry";
		}
		// error_log("   Determined template title: $fulltitle");
		
		$templatepath = $templatepath = LBSTEMPLATEDIR . "/head.html";
		if (!file_exists($templatepath)) {
			error_log("   Could not locate head template $templatepath");
			echo "<p style=\"color:red;\">Could not find head template $templatepath</p>";
			exit(1);
		}
		// $headobj = new Template( array ( 
								// 'filename' => $templatepath,
								// 'die_on_bad_params' => 0,
								// 'case_sensitive' => 0,
								// 'debug' => 1,
								// //'global_vars' => 1,
								// ));
		// $headobj->AddParam('TEMPLATETITLE', $fulltitle);
		// $headobj->AddParam('LANG', $lang);
		// //$headobj->AddParam('templatetitle', $fulltitle);
		// //$headobj->AddParam('lang', $lang);
		// $headobj->EchoOutput();

		$headobj = new LBTemplate($templatepath);
		$headobj->param('TEMPLATETITLE', $fulltitle);
		$headobj->param('LANG', $lang);
		$headobj->param('HTMLHEAD', $htmlhead);
		LBSystem::readlanguage($headobj, "language.ini", True);
		$headobj->output();

		// error_log("<-- loxberry_web: Head function finished");
	}
	
	///////////////////////////////////////////////////////////////////
	// pagestart - Prints the page
	///////////////////////////////////////////////////////////////////
	public static function pagestart($pagetitle = "", $helpurl = "", $helptemplate = "", $page = "main1")
	{
		// error_log("loxberry_web: pagestart function called -->");

		global $template_title;
		global $helplink;
		global $navbar;
		global $lbpplugindir;
		$nopanels = 0;
		
		if ($helpurl === "nopanels") {
			//error_log("Detected nopanels-option. Sidepanels will not be rendered.");
			$nopanels = 1;
		}
		if ($template_title !== "") {
			$pagetitle = $template_title;
		}
		if ($helplink !== "") {
			$helpurl = $helplink;
		}
		
		$lang = LBSystem::lblanguage();
		$fulltitle = $pagetitle !== "" ? lbfriendlyname() . " " . $pagetitle : lbfriendlyname();
		$fulltitle = trim($fulltitle);
		if ($fulltitle === "") {
			$fulltitle = "LoxBerry";
		}
		// error_log("   Determined template title: $fulltitle");
		
		$helptext = LBWeb::gethelp($lang, $helptemplate);

		if ($nopanels) {
			$templatepath = LBSTEMPLATEDIR . "/pagestart_nopanels.html";
			if (!file_exists($templatepath)) {
				error_log("   Could not locate pagestart template $templatepath");
				echo "<p style=\"color:red;\">Could not find pagestart template $templatepath</p>";
				exit(1);
			}
		} else {
			$templatepath = LBSTEMPLATEDIR . "/pagestart.html";
			if (!file_exists($templatepath)) {
				error_log("   Could not locate pagestart template $templatepath");
				echo "<p style=\"color:red;\">Could not find pagestart template $templatepath</p>";
				exit(1);
			}
		}


		// $pageobj = new Template( array ( 
								// 'filename' => $templatepath,
								// 'die_on_bad_params' => 0,
								// //'debug' => 1,
								// 'global_vars' => 1,
								// ));
		// #$pageobj->AddParam('TEMPLATETITLE', $fulltitle);
		// #$pageobj->AddParam('LANG', $lang);
		// $pageobj->AddParam(array(
					// 'TEMPLATETITLE' => $fulltitle,
					// 'HELPLINK' => $helpurl,
					// 'HELPTEXT' => $helptext,
					// 'PAGE' => $page,
					// 'LANG' => $lang
					// ));
		// $pageobj->EchoOutput();
		
		// NavBar Start
		
		if (is_array($navbar)) {
			# navbar is defined as ARRAY
			sort($navbar, SORT_NUMERIC);
			$topnavbar = '<div data-role="navbar">' . 
				'	<ul>';
			foreach ($navbar as $key => $element) {
				if (isset($element['active'])) {
					$btnactive = ' class="ui-btn-active"';
				} else { $btnactive = NULL; 
				}
				if (isset($element['target'])) {
					$btntarget = ' target="' . $element['target'] . '"';
				} else {
					$btntarget = "";
				}
				
				// // NavBar Notify Old
				// $notify = "";
				// if (isset($element['notifyRed'])) {
					// $notify = ' <span class="notifyRedNavBar">' . $element['notifyRed'] . '</span>';
				// } elseif (isset($element['notifyBlue'])) {
					// $notify = ' <span class="notifyBlueNavBar">' . $element['notifyBlue'] . '</span>';
				// }
				
				$notify = <<<EOT
				<div class="notifyBlueNavBar" id="notifyBlueNavBar$key" style="display: none">0</div>
				<div class="notifyRedNavBar" id="notifyRedNavBar$key" style="display: none">0</div>
EOT;
				
				if (isset($element['Name'])) {
					$topnavbar .= <<<EOT
				<li>
					<div style="position:relative">$notify<a href="{$element['URL']}"{$btntarget}{$btnactive}>{$element['Name']}</a>
					</div>
				</li>
EOT;
					$topnavbar_haselements = True;
				
					// Inject Notify JS code
					$notifyname = $element['Notify_Name'];
					$notifypackage = $element['Notify_Package'];
					if (isset($notifyname) && ! isset($notifypackage) && isset($lbpplugindir)) {
						$notifypackage = $lbpplugindir;
					}
					if (isset($notifypackage)) {
						$topnavbar_notify_js .= <<<EOT

		$.post( "/admin/system/tools/ajax-notification-handler.cgi", { action: 'get_notification_count', package: '$notifypackage', name: '$notifyname' })
			.done(function(data) { 
				console.log("get_notification_count executed successfully");
				console.log("{$element['Name']}", data[0], data[1], data[2]);
				if (data[0] != 0) \$("#notifyRedNavBar{$key}").text(data[2]).fadeIn('slow');
				else \$("#notifyRedNavBar{$key}").text('0').fadeOut('slow');
				if (data[1] != 0) \$("#notifyBlueNavBar{$key}").text(data[1]).fadeIn('slow');
				else \$("#notifyBlueNavBar{$key}").text('0').fadeOut('slow');
				
			});
EOT;

					}
				}
			}
			
			$topnavbar .=  '	</ul>' .
				'</div>';	
		
		} elseif (is_string($navbar)) {
			# navbar is defined as plain STRING
			$topnavbar = $navbar;
			$topnavbar_haselements = True;
		} 
		// NavBar End
			
		
		$pageobj = new LBTemplate($templatepath);
		$pageobj->paramArray(array(
					'TEMPLATETITLE' => $fulltitle,
					'HELPLINK' => $helpurl,
					'HELPTEXT' => $helptext,
					'PAGE' => $page,
					'LANG' => $lang
					));
		LBSystem::readlanguage($pageobj, "language.ini", True);
		
		if ($topnavbar_haselements) {
			$pageobj->param ( 'TOPNAVBAR', $topnavbar);
		} else {
			$pageobj->param ( 'TOPNAVBAR', "");
		}
		if ($topnavbar_notify_js) {
			$notify_js = 
<<<EOT

<SCRIPT>
\$(function() { updatenavbar(); });
function updatenavbar() {
	console.log("updatenavbar called");
	$topnavbar_notify_js
};
</SCRIPT>
EOT;
			$pageobj->param ( 'NAVBARJS', $notify_js);
		} else {
			$pageobj->param ( 'NAVBARJS', "");
		}	
		
		
		
		
		
		$pageobj->output();
		
		// error_log("<-- loxberry_web: Pagestart function finished");
	}
	
	///////////////////////////////////////////////////////////////////
	// gethelp - Collects the help text
	// Currently, only plugin help is supported (no system help)
	///////////////////////////////////////////////////////////////////
	function gethelp($lang, $helptemplate)
	{
		global $lbpplugindir;
		global $lbptemplatedir;
		
		// error_log("gethelp -> lbpplugindir: $lbpplugindir lbptemplatedir: $lbptemplatedir");
		// error_log("   Parameters: lang: $lang helptemplate: $helptemplate");
		
		if (file_exists("$lbptemplatedir/help/$helptemplate")) { 
			$templatepath = "$lbptemplatedir/help/$helptemplate";
			$ismultilang = True;
			// error_log("gethelp: Multilang template found - using templatepath $templatepath");
		} elseif (file_exists("$lbptemplatedir/$lang/$helptemplate")) {
			$templatepath = "$lbptemplatedir/$lang/$helptemplate";
			$ismultilang = False;
			// error_log("gethelp: Legacy lang $lang template found - using templatepath $templatepath");
		} elseif (file_exists("$lbptemplatedir/en/$helptemplate")) {
			$templatepath = "$lbptemplatedir/en/$helptemplate";
			$ismultilang = False;
			// error_log("gethelp: Legacy fallback lang en template found - using templatepath $templatepath");
		} elseif (file_exists("$lbptemplatedir/de/$helptemplate")) {
			$templatepath = "$lbptemplatedir/de/$helptemplate";
			$ismultilang = False;
			// error_log("gethelp: Legacy fallback lang de template found - using templatepath $templatepath");
		} else {
			//error_log("gethelp: No help found. Returning default text.");
			if ($lang === "de") {
				$helptext = "Keine weitere Hilfe verfügbar.";
			} else {
				$helptext = "No further help available.";
			}
			return $helptext;
		}
		
		// Multilang templates
		if ($ismultilang) {
			$pos = strrpos($helptemplate, ".");
			if ($pos === false) {
				error_log("gethelp: Illegal option '$helptemplate'. This should be in the form 'helptemplate.html' without pathes.");
				return null;
			}
			$langfile = substr($helptemplate, 0, $pos) . ".ini";
			$helpobj = new LBTemplate($templatepath);
			LBSystem::readlanguage($helpobj, $langfile);
			$helptext = $helpobj->outputString();
			return $helptext;
		}
		// Legacy templates
		else {
			$helptext = file_get_contents($templatepath);
			return $helptext;
		}
	}
	
	///////////////////////////////////////////////////////////////////
	// pageend - Prints the page end
	///////////////////////////////////////////////////////////////////
	public static function pageend()
	{
		global $reboot_required_file;
		$lang = LBSystem::lblanguage();
		$templatepath = $templatepath = LBSTEMPLATEDIR . "/pageend.html";
		$pageobj = new LBTemplate($templatepath);
		$SL = LBSystem::readlanguage($pageobj, "language.ini", True);
		$pageobj->param('LANG', $lang);
		
		// Reboot required button
		if (file_exists("$reboot_required_file")) {
			$reboot_req_string='<div data-href="/admin/system/power.cgi" id="btnpower_alert" style="pointer-events: none; display:none; width:30px; height:30px; background-repeat: no-repeat; background-image: url(\'/system/images/reboot_required.svg\');"></div><script>$(document).ready( function(){ $("#btnpower").attr("title","'.$SL['POWER.MSG_REBOOT_REQUIRED_SHORT'].'");  $("#btnpower_alert").on("click", function(e){ var ele = e.target; window.location.replace(ele.getAttribute("data-href"));}); function reboot_on(){ var reboot_alert_offset = $("#btnpower").offset(); $("#btnpower_alert").css({"padding": "0px", "border": "0px", "z-index": 10000, "top": "4px" ,"left" : reboot_alert_offset.left + 4, "position":"absolute" }); $("#btnpower_alert").fadeTo( 2000 , 1.0, function() { setTimeout(function(){ reboot_off(); }, 2700); }); }; function reboot_off(){ var reboot_alert_offset = $("#btnpower").offset(); $("#btnpower_alert").css({"padding": "0px", "border": "0px", "z-index": 10000, "top": "4px" ,"left" : reboot_alert_offset.left + 4, "position":"absolute" }); $("#btnpower_alert").fadeTo( 2000 , 0.1, function() { setTimeout(function(){ reboot_on(); }, 100); });   }; reboot_on(); });</script>';
			$pageobj->param('REBOOT_REQUIRED', $reboot_req_string);
		} else {
			$pageobj->param('REBOOT_REQUIRED', null);
		}

		echo $pageobj->output();
	}
	
	///////////////////////////////////////////////////////////////////
	// foot - Prints the footer
	///////////////////////////////////////////////////////////////////
	public static function foot()
	{
		$lang = LBSystem::lblanguage();
		$templatepath = $templatepath = LBSTEMPLATEDIR . "/foot.html";
		$footobj = new LBTemplate($templatepath);
		$footobj->param('LANG', $lang);
		echo $footobj->output();
	}
	
	///////////////////////////////////////////////////////////////////
	// lbheader - Prints head and pagestart
	///////////////////////////////////////////////////////////////////
	public static function lbheader($pagetitle = "", $helpurl = "", $helptemplate = "")
	{
		LBWeb::head($pagetitle);
		LBWeb::pagestart($pagetitle, $helpurl, $helptemplate);
	}
	
	///////////////////////////////////////////////////////////////////
	// lbfooter - Prints pageend and footer
	///////////////////////////////////////////////////////////////////
	public static function lbfooter()
	{
		LBWeb::pageend();
		LBWeb::foot();
	}
	
	
	///////////////////////////////////////////////////////////////////
	// Detects the language 
	// Moved to LBSystem::lblanguage
	///////////////////////////////////////////////////////////////////
	public static function lblanguage() 
	{
		$phpself = basename(__FILE__);
		error_Log("$phpself: LBWeb::lblanguage was moved to LBSystem::lblanguage. The call was redirected, but you should update your program.");
		return LBSystem::lblanguage();
	}

	///////////////////////////////////////////////////////////////////
	// readlanguage - reads the language to an array and template
	///////////////////////////////////////////////////////////////////
	public static function readlanguage($template = NULL, $genericlangfile = "language.ini", $syslang = FALSE)
	{
		$phpself = basename(__FILE__);
		error_Log("$phpself: LBWeb::readlanguage was moved to LBSystem::readlanguage. The call was redirected, but you should update your program.");
		return LBSystem::readlanguage($template, $genericlangfile, $syslang);
	}
		
	function get_plugin_icon($iconsize = 64)
	{
		global $lbshtmldir;
		global $lbpplugindir;
		
		if 		($iconsize > 256) { $iconsize = 512; }
		elseif	($iconsize > 128) { $iconsize = 256; }
		elseif	($iconsize > 64) { $iconsize = 128; }
		else					{ $iconsize = 64; }
		$logopath = "$lbshtmldir/images/icons/$lbpplugindir/icon_$iconsize.png";
		$logopath_web = "/system/images/icons/$lbpplugindir/icon_$iconsize.png";
	
		if (file_exists($logopath)) { 
			return $logopath_web;
		}
		return undef;
	}

	public static function logfile_button_html ($p)
	{
		global $lbpplugindir;
		
		if (! isset($p['LABEL'])) {
			$SL = LBSystem::readlanguage(0, 0, True);
			$p['LABEL'] = $SL['COMMON.BUTTON_LOGFILE'];
		}
		if (isset($p['NAME']) && !isset($p['PACKAGE']) && isset($lbpplugindir)) {
			$p['PACKAGE'] = $lbpplugindir;
		}
		
		if(isset($p['DATA_MINI']) && $p['DATA_MINI'] == 0 ) {
			$datamini = "false";
		} else {
			$datamini = "true";
		}
	
		if (isset($p['DATA_ICON'])) {
			$dataicon = $p['DATA_ICON'];
		} else {
			$dataicon = "action";
		}
	
		return "<a data-role=\"button\" href=\"/admin/system/tools/logfile.cgi?logfile=${p['LOGFILE']}&package=${p['PACKAGE']}&name=${p['NAME']}&header=html&format=template\" target=\"_blank\" data-inline=\"true\" data-mini=\"${datamini}\" data-icon=\"${dataicon}\">${p['LABEL']}</a>\n";
			
	}

	public static function loglist_url($p)
{
	global $lbpplugindir;
	
	if (!isset($p['PACKAGE']) && isset($lbpplugindir)) {
		$p['PACKAGE'] = $lbpplugindir;
	}
	
	return "/admin/system/logmanager.cgi?package=${p['PACKAGE']}&name=${p['NAME']}\n";

}

	public static function loglist_button_html ($p)
	{
		global $lbpplugindir;
		
		if (! isset($p['LABEL'])) {
			$SL = LBSystem::readlanguage(0, 0, True);
			$p['LABEL'] = $SL['COMMON.BUTTON_LOGFILE_LIST'];
		}
		if (!isset($p['PACKAGE']) && isset($lbpplugindir)) {
			$p['PACKAGE'] = $lbpplugindir;
		}
		
		if(isset($p['DATA_MINI']) && $p['DATA_MINI'] == 0 ) {
			$datamini = "false";
		} else {
			$datamini = "true";
		}
	
		if (isset($p['DATA_ICON'])) {
			$dataicon = $p['DATA_ICON'];
		} else {
			$dataicon = "action";
		}
	
		return "<a data-role=\"button\" href=\"/admin/system/logmanager.cgi?package=${p['PACKAGE']}&name=${p['NAME']}\" target=\"_blank\" data-inline=\"true\" data-mini=\"${datamini}\" data-icon=\"${dataicon}\">${p['LABEL']}</a>\n";
			
	}
	
	public static function mslist_select_html($p)
	{
		if(isset($p['DATA_MINI']) && $p['DATA_MINI'] == "0" ) {
			$datamini = "false";
		} else {
			$datamini = "true";
		}
		if (!isset($p['FORMID'])) {
			$p['FORMID'] = "select_miniserver";
		}
		
		$miniservers = LBSystem::get_miniservers();
		if (! is_array($miniservers)) {
			return ('<div>No Miniservers defined</div>');
		}
		if (! isset($miniservers[$p['SELECTED']])) {
			$p['SELECTED'] = 1;
		}
		
		$selectkey = $p['SELECTED'];
		$miniservers[$selectkey]['_selected'] = 'selected="selected"';
		
		$html = <<<EOF
		<div class="ui-field-contain">
EOF;
		if (isset($p['LABEL'])) {
			$html .= <<<EOF
		<label for="{$p['FORMID']}">{$p['LABEL']}</label>
EOF;
		}
		$html .= <<<EOF
		<select name="{$p['FORMID']}" id="{$p['FORMID']}" data-mini="$datamini">
EOF;

		foreach ($miniservers as $msnr=>$ms) {
			if (!isset($ms['_selected'])) {
				$ms['_selected'] = "";
			}
			$html .= "\t\t\t<option value=\"$msnr\" " . $ms['_selected'] . ">" . $ms['Name'] . " (" . $ms['IPAddress'] . ")</option>\n";
		}
		$html .= <<<EOF
		</select>
		</div>

EOF;

	return $html;
	}

	// loglevel_select_html
	
	public function loglevel_select_html($p)
{
	global $lbpplugindir;
	$datamini = 1;
	$selected = 0;
	$html = "";
	
	$pluginfolder = isset($p['PLUGIN']) ? $p['PLUGIN'] : $lbpplugindir;
	# print "pluginfolder: $pluginfolder\n";
	$plugin = LBSystem::plugindata($pluginfolder);
	
	if(empty($plugin)) {
		error_log("loglevel_select_html (PHP): Could not determine plugin");
		return "";
	}
	if (empty($plugin['PLUGINDB_LOGLEVELS_ENABLED'])) {
		error_log("loglevel_select_html (PHP): CUSTOM_LOGLEVELS not enabled in plugin.cfg (plugin " . $pluginfolder . ")");
		return "";
	}
	
	$SL = LBSystem::readlanguage(undef, undef, 1);
		
	if(isset($p['DATA_MINI']) && $p['DATA_MINI'] == 0 ) {
		$datamini = "false";
	} else {
		$datamini = "true";
	}
	if (empty($p['FORMID'])) {
		$p['FORMID'] = "select_loglevel";
	}

	$html = '<div data-role="fieldcontain">';
	
	if (isset($p['LABEL']) && $p['LABEL'] == "") {
		
	} elseif (!empty($p['LABEL'])) {
	$html .= " <label for=\"{$p['FORMID']}\" style=\"display:inline-block;\">{$p['LABEL']}</label>\n";
	} else {
		$html .= "<label for=\"{$p['FORMID']}\" style=\"display:inline-block;\">{$SL['PLUGININSTALL.UI_LABEL_LOGGING_LEVEL']}</label>\n";
	}
	$html .= "<fieldset data-role='controlgroup' data-mini='$datamini' style='width:200px;'>\n";
	
	$html .= <<<EOF
	
	<select name="{$p['FORMID']}" id="{$p['FORMID']}" data-mini="$datamini">
		<option value="0">{$SL['PLUGININSTALL.UI_LOG_0_OFF']}</option>
		<option value="3">{$SL['PLUGININSTALL.UI_LOG_3_ERRORS']}</option>
		<option value="4">{$SL['PLUGININSTALL.UI_LOG_4_WARNING']}</option>
		<option value="6">{$SL['PLUGININSTALL.UI_LOG_6_INFO']}</option>
		<option value="7">{$SL['PLUGININSTALL.UI_LOG_7_DEBUG']}</option>
	</select>
	</fieldset>
	</div>
	
	<script>
	\$(document).ready( function()
	{
		\$("#{$p['FORMID']}").val('{$plugin['PLUGINDB_LOGLEVEL']}').change();
	});
		
	\$("#{$p['FORMID']}").change(function(){
		var val = \$(this).val();
		console.log("Loglevel", val);
		post_value('plugin-loglevel', '{$plugin['PLUGINDB_MD5_CHECKSUM']}', val); 
	});
	
	function post_value (action, pluginmd5, value)
	{
	console.log("Action:", action, "Plugin-MD5:", pluginmd5, "Value:", value);
	\$.post ( '/admin/system/tools/ajax-config-handler.cgi', 
		{ 	action: action,
			value: value,
			pluginmd5: pluginmd5
		});
	}

	</script>
EOF;
	
	return $html;

}

	
	
	
	
	
	
	// loglevel_select_html finish
	
	
	
	
	
	
	
	
	public static function loglist_html($p)
	{
		global $lbpplugindir;
		
		if (!isset($p['PACKAGE']) && isset($lbpplugindir)) {
			$p['PACKAGE'] = $lbpplugindir;
		}
		
		$url = "http://" . lbhostname() . ":" . lbwebserverport() . "/admin/system/logmanager.cgi?package=" .  urlencode(${p['PACKAGE']}) . "&name=" .  urlencode(${p['NAME']}) . "&header=none";
		$html = file_get_contents($url);
		
		return $html;
	}
}

////////////////////////////////////////////////////////////
// Christian's Quick and Dirty 'HTML::Template'
////////////////////////////////////////////////////////////
class LBTemplate
 {
	private $valuearray;
	private $template;
		
	function __construct($templatepath)
	{
		// error_log("LBTemplate construct: templatepath: $templatepath");
		$this->template = file_get_contents($templatepath);
	}
	public function param($varname, $value)
	{
			$this->valuearray[$varname] = $value;
	}
	public function paramArray($vararray)
	{
			foreach ($vararray as $key => $value) {
				$this->param($key, $value);
			}
	}
	
	function replaceTmplVar() 
	{
		foreach ($this->valuearray as $tmplvar => $value) {
			// error_log("Key: $tmplvar Value: $value");
			$this->template = str_replace("<TMPL_VAR $tmplvar>", $value, $this->template);
		}
	}
	
	public function output()
	{
	$this->replaceTmplVar();
	echo $this->template;	
	}
	
	public function outputString()
	{
	$this->replaceTmplVar();
	return $this->template;	
	}
 }

# main namespace
	
