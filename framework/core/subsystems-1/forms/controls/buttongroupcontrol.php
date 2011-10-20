<?php

##################################################
#
# Copyright (c) 2004-2011 OIC Group, Inc.
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

/**
 * Button Group Control
 *
 * A group of buttons
 *
 * @package Subsystems-Forms
 * @subpackage Control
 */
class buttongroupcontrol extends formcontrol {

	var $submit = "Submit";
	var $reset = "";
	var $cancel = "";
	var $class = "";
	var $validateJS = "";

	function name() { return "Button Group"; }

	function __construct($submit = "Submit", $reset = "", $cancel = "", $class="") {
		$this->submit = $submit;
		$this->reset = $reset;
		$this->cancel = $cancel;
		$this->class = $class;
	}

	function toHTML($label,$name) {
	    $disabled = $this->disabled != false ? " disabled" : "";
		if ($this->submit . $this->reset . $this->cancel == "") return "";
		$html = "<div id=\"".$name."Control\" class=\"control buttongroup".$disabled."\">";
		$html .= $this->controlToHTML($name);
		$html .= "</div>";			
		return $html;
	}

	function controlToHTML($name) {
		if ($this->submit . $this->reset . $this->cancel == "") return "";
		if (empty($this->id)) $this->id = $name;
		$html = "";
		if ($this->submit != "") {
			$html .= '<button type="submit" id="'.$this->id.'Submit" class="submit button awesome '.BTN_SIZE.' '.BTN_COLOR;
			if ($this->disabled) $html .= " disabled";
			$html .='" type="submit" value="' . $this->submit . '"';
			if ($this->disabled) $html .= " disabled";
			$html .= ' onclick="if (checkRequired(this.form)';
			if (isset($this->onclick)) $html .= ' '.$this->onclick;
			$html .= ') ';
			if ($this->validateJS != "") {
				$html .= '{ if (' . $this->validateJS . ') { return true; } else { return false; } }';
			} else {
				$html .= '{ return true; }';
			}
			$html .= ' else { return false; }"';
			$html .= ' />';
			$html .= $this->submit;
			$html .= ' </button>';

		}
		//if ($this->reset != "") $html .= '<input class="button" type="reset" value="' . $this->reset . '"' . ($this->disabled?" disabled":"") . ' />';
		if ($this->cancel != "") {
			$html .= '<button type="cancel" class="cancel button awesome '.BTN_SIZE.' '.BTN_COLOR.'" onclick="document.location.href=\''.expHistory::getLastNotEditable().'\'; return false;"';
//			$html .= '<button type="cancel" class="cancel button awesome '.BTN_SIZE.' '.BTN_COLOR.'" onclick="document.location.href=\''.expHistory::getLast().'\'; return false;"';
			$html .= '>';
			$html .= $this->cancel;
			$html .= '</button>';
		}
		
		expCSS::pushToHead(array(
		    "unique"=>"button",
		    "corecss"=>"button",
		    )
		);
		
		return $html;
	}

	static function parseData($name, $values, $for_db = false) {
		return;
	}

}

?>
