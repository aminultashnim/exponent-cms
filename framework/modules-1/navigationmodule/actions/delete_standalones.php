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

if (!defined('EXPONENT')) exit('');

if ($user->is_admin == 1 || $user->is_acting_admin == 1) {
    if (empty($_POST['deleteit'])) {
        echo SITE_403_HTML;
    } else {
        foreach ($_POST['deleteit'] as $page) {
            $section = $db->selectObject('section','id='.intval($page));
            if ($section) {
                navigationmodule::deleteLevel($section->id);
                $db->delete('section','id=' . $section->id);
                $db->decrement('section','rank',1,'rank > ' . $section->rank . ' AND parent='.$section->parent);
            }
        }
    }
    expSession::clearAllUsersSessionCache('navigationmodule');
    expHistory::back();
//		redirect_to($_SERVER['HTTP_REFERER'].'#tab=1');
} else {
	echo SITE_404_HTML;
}

?>