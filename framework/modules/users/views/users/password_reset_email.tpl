{*
 * Copyright (c) 2004-2012 OIC Group, Inc.
 *
 * This file is part of Exponent
 *
 * Exponent is free software; you can redistribute
 * it and/or modify it under the terms of the GNU
 * General Public License as published by the Free
 * Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * GPL: http://www.gnu.org/licenses/gpl.txt
 *
 *}

{'You requested that your password be reset at'|gettext} {$smarty.const.URL_FULL}.
\r\n\r\n
{'Please follow this link to confirm that you do want it reset'|gettext}:
{link controller=users action=confirm_password_reset token=$token->token uid=$token->uid}
