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

/**
 * @subpackage Core-Definitions
 * @package Framework
 */
return array(
	'id'=>array(
		DB_FIELD_TYPE=>DB_DEF_ID,
		DB_PRIMARY=>true,
		DB_INCREMENT=>true),
	'from_status_id'=>array(
		DB_FIELD_TYPE=>DB_DEF_INTEGER),
    'to_status_id'=>array(
		DB_FIELD_TYPE=>DB_DEF_INTEGER),
	'comment'=>array(
		DB_FIELD_TYPE=>DB_DEF_STRING,
		DB_FIELD_LEN=>10000),
	'poster'=>array(
		DB_FIELD_TYPE=>DB_DEF_INTEGER),
	'created_at'=>array(
		DB_FIELD_TYPE=>DB_DEF_TIMESTAMP),
	'orders_id'=>array(
		DB_FIELD_TYPE=>DB_DEF_INTEGER),
);

?>
