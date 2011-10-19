<?php
/**
 *  This file is part of Exponent
 *  Exponent is free software; you can redistribute
 *  it and/or modify it under the terms of the GNU
 *  General Public License as published by the Free
 *  Software Foundation; either version 2 of the
 *  License, or (at your option) any later version.
 *
 * The file that holds the expHTMLEditorController class.
 *
 * @link http://www.gnu.org/licenses/gpl.txt GPL http://www.gnu.org/licenses/gpl.txt
 * @package Exponent-CMS
 * @copyright 2004-2011 OIC Group, Inc.
 * @author Adam Kessler <adam@oicgroup.net>
 * @version 2.0.0
 */

/**
 * This is the class expHTMLEditorController
 *
 * @subpackage Core-Controllers
 * @package Framework
 */

class expHTMLEditorController extends expController {

    function displayname() { return "Editors"; }
    function description() { return "Mostly for CKEditor"; }
    function author() { return "Phillip Ball"; }
    function hasSources() { return false; }
	function hasContent() { return false; }
	protected $add_permissions = array('activate'=>"activate",'preview'=>"preview CKEditor toolbars");
    
    function manage () {
        global $db;
        // yet more gluecode
        if (SITE_WYSIWYG_EDITOR=="FCKeditor") {
	        flash('error','FCKeditor is deprecated!');
	        redirect_to(array("module"=>"administration","action"=>"configure_site"));
        }

        expHistory::set('manageable',$this->params);

        // otherwise, on to cke
        $configs = $db->selectObjects('htmleditor_ckeditor',1);
        
        assign_to_template(array('configs'=>$configs));
    }

    function update () {
        global $db;
        $obj = $db->selectObject('htmleditor_ckeditor',"id=".$this->params['id']);
        $obj->name = $this->params['name'];
        $obj->data = stripSlashes($this->params['data']);
        $obj->skin = $this->params['skin'];
        $obj->scayt_on = $this->params['scayt_on'];
        $obj->paste_word = $this->params['paste_word'];
        $obj->plugins = stripSlashes($this->params['plugins']);
        if (empty($this->params['id'])) {
            $db->insertObject($obj,'htmleditor_ckeditor');
        } else {
            $db->updateObject($obj,'htmleditor_ckeditor',null,'id');
        }
		if ($this->params['active']) {
			$this->activate();
		}
        expHistory::back();
    }

    function edit() {
        global $db;
        expHistory::set('editable', $this->params);
        $tool = @$db->selectObject('htmleditor_ckeditor',"id=".$this->params['id']);
        $tool->data = @stripSlashes($tool->data);
        assign_to_template(array('record'=>$tool));
    }
    
	function delete() {
	    global $db;
	    expHistory::set('editable', $this->params);
	    @$db->delete('htmleditor_ckeditor',"id=".$this->params['id']);

	    expHistory::back();
	}

    function activate () {
        global $db;
        
        $db->toggle('htmleditor_ckeditor',"active",'active=1');
        if ($this->params['id']!="default") {
            $active = $db->selectObject('htmleditor_ckeditor',"id=".$this->params['id']);
            $active->active = 1;
        
            $db->updateObject($active,'htmleditor_ckeditor',null,'id');
        }

        expHistory::back();
    }

    function preview () {
        global $db;
        if ($this->params['id']=="default") {
            $demo->name="Default";
            $demo->data="default";
			$demo->skin = 'kama';
        } else {
            $demo = $db->selectObject('htmleditor_ckeditor',"id=".$this->params['id']);
        }
        assign_to_template(array('demo'=>$demo));
    }

}

?>