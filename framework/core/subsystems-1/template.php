<?php

##################################################
#
# Copyright (c) 2004-2011 OIC Group, Inc.
# Copyright (c) 2006-2007 Maxim Mueller
# Written and Designed by James Hunt
#
# This file is part of Exponent
#
# Exponent is free software; you can redistribute
# it and/or modify it under the terms of the GNU
# General Public License as published by the Free
# Software Foundation; either version 2 of the
# License, or (at your option) any later version.
#
# GPL: http://www.gnu.org/licenses/gpl.txt
#
##################################################
/** @define "BASE" "../../.." */

if (!defined('EXPONENT')) exit('');

$userjsfiles = array();

/* exdoc
 * The definition of this constant lets other parts of the system know 
 * that the subsystem has been included for use.
 * @node Subsystems:Template
 */
//define('SYS_TEMPLATE',1);

define('TEMPLATE_FALLBACK_VIEW',BASE.'framework/core/views/viewnotfound.tpl');

include_once(BASE.'external/Smarty-2/libs/Smarty.class.php');

class BaseTemplate {
	// Smarty template object.
	var $tpl;
	
	// This is the directory of the particular module, used to identify the moduel
	var $module = "";
	
	// The full server-side filename of the .tpl file being used.
	// This will be used by modules on the outside, for retrieving view configs.
	var $viewfile = "";
	
	// Name of the view (for instance, 'Default' for 'Default.tpl')
	var $view = "";
	
	// Full server-side directory path of the .tpl file being used.
	var $viewdir = "";
	
	//fix for the wamp/lamp issue
	var $langdir = "";
	//	
	
	function __construct($item_type, $item_dir, $view = "Default") {
		
		include_once(BASE.'external/Smarty-2/libs/Smarty.class.php');
		
		// Set up the Smarty template variable we wrap around.
		$this->tpl = new Smarty();
		//Some (crappy) wysiwyg editors use php as their default initializer
		//FJD - this might break some editors...we'll see.
		$this->tpl->php_handling = SMARTY_PHP_REMOVE;

		$this->tpl->caching = false;
		$this->tpl->cache_dir = BASE . 'tmp/cache';

		//$this->tpl->plugins_dir[] = BASE . 'framework/core/subsystems-1/template/Smarty/plugins';
		$this->tpl->plugins_dir[] = BASE . 'framework/plugins';
		// now reverse the array so we can bypass looking in our root folder for old plugins
		$this->tpl->plugins_dir = array_reverse($this->tpl->plugins_dir);
		
		//autoload filters
		$this->tpl->autoload_filters = array('post' => array('includemiscfiles'));
		
		$this->viewfile = exponent_template_getViewFile($item_type, $item_dir, $view);
		$this->viewdir = realpath(dirname($this->viewfile));

		$this->module = $item_dir;

		$this->view = substr(basename($this->viewfile),0,-4);
		
		//fix for the wamp/lamp issue
		//checks necessary in case a file from /views/ is used
		//should go away, the stuff should be put into a CoreModule
		//then this can be simplified
		//TODO: generate this through $this->viewfile using find BASE/THEME_ABSOLUTE and replace with ""
		if($item_type != "") {
			$this->langdir .= $item_type . "/";
		}
		if($item_dir != "") {
			$this->langdir .= $item_dir . "/";
		}
		$this->langdir .= "views/";
		
		
		$this->tpl->template_dir = $this->viewdir;
		
		$this->tpl->compile_dir = BASE . 'tmp/views_c';
		$this->tpl->compile_id = md5($this->viewfile);
		
		$this->tpl->assign("__view", $this->view);
		$this->tpl->assign("__redirect", expHistory::getLastNotEditable());
	}
	
	/*
	 * Assign a variable to the template.
	 *
	 * @param string $var The name of the variable - how it will be referenced inside the Smarty code
	 * @param mixed $val The value of the variable.
	 */
	function assign($var, $val) {
		$this->tpl->assign($var, $val);
	}
	
	/*
	 * Render the template and echo it to the screen.
	 */
	function output() {
		// javascript registration
		
		$this->tpl->display($this->view.'.tpl');
	}
	
	function register_permissions($perms, $locs) {
		$permissions_register = array();
		if (!is_array($perms)) $perms = array($perms);
		if (!is_array($locs)) $locs = array($locs);
		foreach ($perms as $perm) {
			foreach ($locs as $loc) {
				$permissions_register[$perm] = (expPermissions::check($perm, $loc) ? 1 : 0);
			}
		}
		$this->tpl->assign('permissions', $permissions_register);
	}
	
	/*
	 * Render the template and return the result to the caller.
	 */
	function render() { // Caching support?
		return $this->tpl->fetch($this->view.'.tpl');
	}
}
/*
 * Wraps the template system in use, to provide a uniform and consistent
 * interface to templates.
 */
//TODO: prepare this class for multiple template systems
class template extends BaseTemplate {	
		
	var $module = '';	
	
	function __construct($module, $view = null, $loc = null, $caching=false, $type=null) {
		$type = !isset($type) ? 'modules' : $type;

		//parent::__construct("modules", $module, $view);
		parent::__construct($type, $module, $view);
		
		$this->viewparams = exponent_template_getViewParams($this->viewfile);
				
		if ($loc == null) {
			$loc = expCore::makeLocation($module);
		}
		
		$this->tpl->assign("__loc",$loc);
		$this->tpl->assign("__name", $module);
		
		// View Config
		global $db;
		$container_key = serialize($loc);
		$cache = expSession::getCacheValue('containermodule');
		if (isset($cache[$container_key])){
			$container = $cache[$container_key];
		}else{
			$container = $db->selectObject("container","internal='".$container_key."'");
			$cache[$container_key] = $container;
		}
		$this->viewconfig = ($container && isset($container->view_data) && $container->view_data != "" ? unserialize($container->view_data) : array());
		$this->tpl->assign("__viewconfig", $this->viewconfig);
	}
}

class controllerTemplate extends baseTemplate {
	function __construct($controller, $viewfile) {
		include_once(BASE.'external/Smarty-2/libs/Smarty.class.php');
		
		// Set up the Smarty template variable we wrap around.
		$this->tpl = new Smarty();
		//Some (crappy) wysiwyg editors use php as their default initializer
		//FJD - this might break some editors...we'll see.
		$this->tpl->php_handling = SMARTY_PHP_REMOVE;

		$this->tpl->caching = false;
		$this->tpl->cache_dir = BASE . 'tmp/cache';

		//$this->tpl->plugins_dir[] = BASE . 'framework/core/subsystems-1/template/Smarty/plugins';
		$this->tpl->plugins_dir[] = BASE . 'framework/plugins';
		// now reverse the array so we can bypass looking in our root folder for old plugins
		$this->tpl->plugins_dir = array_reverse($this->tpl->plugins_dir);

		//autoload filters
		$this->tpl->autoload_filters = array('post' => array('includemiscfiles'));
		
		$this->viewfile = $viewfile;
		$this->viewdir = realpath(dirname($this->viewfile));

		$this->module = $controller->baseclassname;
				
		$this->view = substr(basename($this->viewfile),0,-4);
		
		//fix for the wamp/lamp issue
		//checks necessary in case a file from /views/ is used
		//should go away, the stuff should be put into a CoreModule
		//then this can be simplified
		//TODO: generate this through $this->viewfile using find BASE/THEME_ABSOLUTE and replace with ""
		
		$this->langdir .= 'framework/'.$controller->relative_viewpath . "/";
		
		$this->tpl->template_dir = $this->viewdir;
		
		$this->tpl->compile_dir = BASE . 'tmp/views_c';
		$this->tpl->compile_id = md5($this->viewfile);
		
		$this->tpl->assign("__view", $this->view);
		$this->tpl->assign("__redirect", expHistory::getLastNotEditable());
		
		$this->tpl->assign("__loc",$controller->loc);
		$this->tpl->assign("__name", $controller->baseclassname);
		
	}
}

/* exdoc
 *
 * Control Template wrapper
 *
 */
class ControlTemplate extends BaseTemplate {
	
	var $viewitem = "";

	function __construct($control, $view = "Default", $loc = null) {
		parent::__construct("controls", $control, $view);
		$this->tpl->assign("__name", $control);
	}

	/*
	 * Render the template and return the result to the caller.
	 * temporary override for testing functionality
	 */
//	function render() {
//		//pump the viewitem into the view layer
//		
//		$this->tpl->assign("vi", $this->viewitem);
//		$this->tpl->assign("dm", $this->viewitem->datamodel);
//		
//		//call childobjects show() method recursively, based on render depth setting
//		//assign output
//		
//		return $this->tpl->fetch($this->view.'.tpl');
//	}
}

/*
 * Form Template Wrapper
 *
 * This class is used for site wide forms.  
 *
 * @package Subsystems
 * @subpackage Template
 */
class formtemplate extends BaseTemplate {

	function __construct($form, $view) {
		parent::__construct("forms", $form, $view);
		$this->tpl->assign("__name", $form);
	}
}

class filetemplate extends BaseTemplate {
	function __construct($file) {
		parent::__construct("", "", $file);
	}
}

/*
 * Standalone Template Class
 *
 * A standalone template is a template (tpl) file found in either
 * THEME_ABSOLUTE/views or BASE/views, which uses
 * the corresponding views_c directory for compilation.
 * 
 * @param string $view The name of the standalone view.
 */
class standalonetemplate extends BaseTemplate {
	function __construct($view) {
		parent::__construct("globalviews", "", $view);
	}
}

/*
 * Retrieve Module-Independent View File
 *
 * Looks in the theme and the /views directory for a .tpl file
 * corresponding to the passed view.
 *
 * @param string $type One of "modules"", "controls"", "forms" or ""
 * @param string $name The name the object we are requesting a view from
 * @param string $view The name of the requested view
 *
 * @return string The full filepath of the view template
 */
function exponent_template_getViewFile($type="", $name="", $view="Default") {
	$viewfilepath = expCore::resolveFilePaths($type, $name, "tpl", $view);
	// Something is really screwed up.
	if ($viewfilepath == false) {
		// Fall back to something that won't error.
		return BASE . 'framework/core/views/viewnotfound.tpl';
	}
	//return first match
	return array_shift($viewfilepath);	
}

//DEPRECATED: backward compatibility wrapper
function exponent_template_getModuleViewFile($name, $view, $recurse=true) {
	return exponent_template_getViewFile("modules", $name, $view);
}

// I think these still need to be i18n-ized
function exponent_template_getViewConfigForm($module,$view,$form,$values) {
	$form_file = "";
	$resolved_path = null;
	$resolved_path = expCore::resolveFilePaths("modules", $module , "form" , $view);
	if (isset($resolved_path) && $resolved_path != '') {
		$filepath = array_shift(expCore::resolveFilePaths("modules", $module , "form" , $view));
	} else {
		$filepath = false;
	}

	if ($filepath != false) {
		$form_file = $filepath;
	}
	
	require_once(BASE."framework/core/subsystems-1/forms.php");
	if ($form == null) $form = new form();
	if ($form_file == "") return $form;
	
	$form->register(null,"",new htmlcontrol("<hr size='1' /><b>Layout Configuration</b>"));
	
	$fh = fopen($form_file,"r");
	while (($control_data = fgetcsv($fh,65536,"\t")) !== false) {
		$data = array();
		foreach ($control_data as $d) {
			if ($d != "") $data[] = $d;
		}
		if (!isset($values[$data[0]])) $values[$data[0]] = 0;
		if ($data[2] == "checkbox") {
			$form->register("_viewconfig[".$data[0]."]",$data[1],new checkboxcontrol($values[$data[0]],true));
		} else if ($data[2] == 'text') {
			$form->register("_viewconfig[".$data[0]."]",$data[1],new textcontrol($values[$data[0]]));
		} else {
			$options = array_slice($data,3);
			$form->register("_viewconfig[".$data[0]."]",$data[1],new dropdowncontrol($values[$data[0]],$options));
		}
	}
	
	$form->register("submit","",new buttongroupcontrol("Save","","Cancel"));
	
	return $form;
}

function exponent_template_getViewConfigOptions($module,$view) {
	$form_file = "";
	$filepath = array_shift(expCore::resolveFilePaths("modules", $module, "form", $view));
	if ($filepath != false) {
		$form_file = $filepath;
	}
	if ($form_file == "") return array(); // no form file, no options
	
	$fh = fopen($form_file,"r");
	$options = array();
	while (($control_data = fgetcsv($fh,65536,"\t")) !== false) {
		$data = array();
		foreach ($control_data as $d) {
			if ($d != "") $data[] = $d;
		}
		$options[$data[0]] = $data[1];
	}
	return $options;
}

function exponent_template_getFormTemplates($type) {
    $forms = array();

    //Get the forms from the base form diretory
    if (is_dir(BASE.'forms/'.$type)) {
        if ($dh = opendir(BASE.'forms/'.$type)) {
             while (false !== ($file = readdir($dh))) {
                if ( (substr($file,-4,4) == ".tpl") && ($file{0} != '_')) {
                    $forms[substr($file,0,-4)] = substr($file,0,-4);
                }
            }
        }
    }
    //Get the forms from the themes form directory.  If the theme has forms of the same
    //name as the base form dir, then they will overwrite the ones already  in the array $forms.
    if (is_dir(THEME_ABSOLUTE.'forms/'.$type)) {
        if ($dh = opendir(THEME_ABSOLUTE.'forms/'.$type)) {
             while (false !== ($file = readdir($dh))) {
                if ( (substr($file,-4,4) == ".tpl") && ($file{0} != '_')) {
                    $forms[substr($file,0,-4)] = substr($file,0,-4);
                }
            }
        }
    }

    return $forms;
}

/**
 * Generates a list of email templates/forms
 * @param $type
 * @return array
 */
function exponent_template_listFormTemplates($type) {  //FIXME only used by calendarmodule edit action
	return expCore::buildNameList("forms", $type, "tpl", "[!_]*");
}

/* exdoc
 *
 * Looks through the module's views directory and returns
 * all non-internal views that are found there.
 * Returns an array of all standard view names.
 * This array is unsorted.
 *
 * @param string $module The classname of the module to get views for.
 * @param string $lang deprecated, was used to list language specific templates
 * @node Subsystems:Template
 */
function exponent_template_listModuleViews($module, $lang = LANG) {  //FIXME only used by containermodule edit action and administrationmodule examplecontent action
	return expCore::buildNameList("modules", $module, "tpl", "[!_]*");
}

function exponent_template_getViewParams($viewfile) {
	$base = substr($viewfile,0,-4);
	$vparam = null;
	if (is_readable($base.'.config')) {
		$vparam = include($base.'.config');
	}
	return $vparam;
}

?>
