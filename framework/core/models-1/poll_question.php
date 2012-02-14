<?php
##################################################
#
# Copyright (c) 2004-2012 OIC Group, Inc.
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

class poll_question {
	static function form($object) {
		$form = new form();
		if (!isset($object->id)) {
			$object->question = '';
			$object->open_results = 1;
			$object->open_voting = 1;
			$object->is_active = 0;
		} else {
			$form->meta('id',$object->id);
		}
		
		$form->register('question',gt('Question'),new textcontrol($object->question));
		$form->register('open_results',gt('Results are Publically Viewable'),new checkboxcontrol($object->open_results,1));
		$form->register('open_voting',gt('Open Voting?'),new checkboxcontrol($object->open_voting,1));
		$form->register('submit','',new buttongroupcontrol(gt('Save'),'',gt('Cancel')));
		
		return $form;
	}
	
	static function update($values,$object) {
		$object->question = $values['question'];
		$object->open_results = (isset($values['open_results']) ? 1 : 0);
		$object->open_voting = (isset($values['open_voting']) ? 1 : 0);
		return $object;
	}
}

?>