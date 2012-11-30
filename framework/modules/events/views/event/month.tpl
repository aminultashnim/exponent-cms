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

    {$myloc=serialize($__loc)}
	<table id="calendar" summary="{$moduletitle|default:'Calendar'|gettext}">
        <div class="caption">
            &laquo;&#160;
            <a class="nav module-actions" href="javascript:void(0);" rel="{$prevmonth3}" title="{$prevmonth3|format_date:"%B %Y"}">{$prevmonth3|format_date:"%b"}</a>&#160;&#160;&laquo;&#160;
            <a class="nav module-actions" href="javascript:void(0);" rel="{$prevmonth2}" title="{$prevmonth2|format_date:"%B %Y"}">{$prevmonth2|format_date:"%b"}</a>&#160;&#160;&laquo;&#160;
            <a class="nav module-actions" href="javascript:void(0);" rel="{$prevmonth}" title="{$prevmonth|format_date:"%B %Y"}">{$prevmonth|format_date:"%b"}</a>&#160;&#160;&laquo;&#160;&#160;&#160;&#160;&#160;
            <strong>{$time|format_date:"%B %Y"}</strong>&#160;&#160;&#160;&#160;&#160;&#160;&raquo;&#160;&#160;
            <a class="nav module-actions" href="javascript:void(0);" rel="{$nextmonth}" title="{$nextmonth|format_date:"%B %Y"}">{$nextmonth|format_date:"%b"}</a>&#160;&#160;&raquo;&#160;
            <a class="nav module-actions" href="javascript:void(0);" rel="{$nextmonth2}" title="{$nextmonth2|format_date:"%B %Y"}">{$nextmonth2|format_date:"%b"}</a>&#160;&#160;&raquo;&#160;
            <a class="nav module-actions" href="javascript:void(0);" rel="{$nextmonth3}" title="{$nextmonth3|format_date:"%B %Y"}">{$nextmonth3|format_date:"%b"}</a>&#160;&#160;&raquo;
        </div>
		<tr class="daysoftheweek">
            {if $config.show_weeks}<th></th>{/if}
			{if $smarty.const.DISPLAY_START_OF_WEEK == 0}
			<th scope="col" abbr="{'Sun'|gettext}" title="'Sunday'|gettext}">{'Sunday'|gettext}</th>
			{/if}
			<th scope="col" abbr="{'Mon'|gettext}" title="{'Monday'|gettext}">{'Monday'|gettext}</th>
			<th scope="col" abbr="{'Tue'|gettext}" title="{'Tuesday'|gettext}">{'Tuesday'|gettext}</th>
			<th scope="col" abbr="{'Wed'|gettext}" title="{'Wednesday'|gettext}">{'Wednesday'|gettext}</th>
			<th scope="col" abbr="{'Thu'|gettext}" title="{'Thursday'|gettext}">{'Thursday'|gettext}</th>
			<th scope="col" abbr="{'Fri'|gettext}" title="{'Friday'|gettext}">{'Friday'|gettext}</th>
			<th scope="col" abbr="{'Sat'|gettext}" title="{'Saturday'|gettext}">{'Saturday'|gettext}</th>
			{if $smarty.const.DISPLAY_START_OF_WEEK != 0}
			<th scope="col" abbr="{'Sun'|gettext}" title="{'Sunday'|gettext}">{'Sunday'|gettext}</th>
			{/if}
		</tr>
        {$dayts=$now}
        {$dst=false}
		{foreach from=$monthly item=week key=weeknum}
            {*{$moredata=0}*}
			{*{foreach name=w from=$week key=day item=events}*}
                {*{$number=$counts[$weeknum][$day]}*}
                {*{if $number > -1}{$moredata=1}{/if}*}
			{*{/foreach}*}
			{*{if $moredata == 1}*}
                <tr class="week{if $currentweek == $weeknum} currentweek{/if}">
                    {if $config.show_weeks}
                        <td class="week{if $currentweek == $weeknum} currentweek{/if}">{$weeknum}</td>
                    {/if}
                    {foreach name=w from=$week key=day item=items}
                        {$number=$counts[$weeknum][$day]}
                        <td {if $dayts == $today}class="today"{elseif $number == -1}class="notinmonth"{else}class="oneday"{/if}>
                            {if $number > -1}
                                {if $number == 0}
                                    <span class="number{if $dayts == $today} today{/if}">
                                        {$day}
                                    </span>
                                {else}
                                    <a class="number" href="{link action=showall view=showall_Day time=$dayts}" title="{$dayts|format_date:'%A, %B %e, %Y'}">{$day}</a>
                                {/if}
                            {/if}
                            {foreach name=e from=$items item=item}
                                {if !empty($item->color)}
                                    {$style=" style=\"background:`$item->color`;color:`$item->color|contrast`\""}
                                {else}
                                    {$style=''}
                                {/if}
                                <div class="calevent{if $dayts == $today} today{/if}"{$style}>
                                    <a{if $config.usecategories && !empty($item->color)} class="{$item->color}"{/if}{$style}{if $config.show_allday && $item->is_allday == 1} style="border-color: {$item->color|brightness:+150};border-style: solid;padding-left: 2px;border-top: 0;border-bottom: 0;border-right: 0;"{/if}
                                        {if substr($item->location_data,1,8) != 'calevent'}
                                            href="{if $item->location_data != 'eventregistration'}{if $config.lightbox}#" class="calpopevent" id="{$item->date_id}{else}{link action=show date_id=$item->date_id}{/if}{else}{link controller=eventregistration action=showByTitle title=$item->title}{/if}"
                                        {/if}
                                        title="{if $item->is_allday == 1}{'All Day'|gettext}{elseif $item->eventstart != $item->eventend}{$item->eventstart|format_date:$smarty.const.DISPLAY_TIME_FORMAT} {'to'|gettext} {$item->eventend|format_date:$smarty.const.DISPLAY_TIME_FORMAT}{else}{$item->eventstart|format_date:$smarty.const.DISPLAY_TIME_FORMAT}{/if} - {$item->body|summarize:"html":"para"}">
                                        {if $item->expFile[0]->url != ""}
                                            <div class="image">
                                                {img file_id=$item->expFile[0]->id title="`$item->title`" class="large-img" id="enlarged-image" w=92}
                                                {clear}
                                            </div>
                                        {/if}
                                        {$item->title}
                                    </a>
                                    {permissions}
                                        {if substr($item->location_data,0,3) == 'O:8'}
                                        <div class="item-actions">
                                                {if $permissions.edit == 1}
                                                    {if $myloc != $item->location_data}
                                                        {if $permissions.manage == 1}
                                                            {icon img='arrow_merge.png' action=merge id=$item->id title="Merge Aggregated Content"|gettext}
                                                        {else}
                                                            {icon img='arrow_merge.png' title="Merged Content"|gettext}
                                                        {/if}
                                                    {/if}
                                                    {icon img="edit.png" action=edit record=$item date_id=$item->date_id title="Edit this Event"|gettext}
                                                    {icon img="copy.png" action=copy record=$item date_id=$item->date_id title="Copy this Event"|gettext}
                                                {/if}
                                                {if $permissions.delete == 1}
                                                    {if $item->is_recurring == 0}
                                                        {icon img="delete.png" action=delete record=$item date_id=$item->date_id title="Delete this Event"|gettext}
                                                    {else}
                                                        {icon img="delete.png" action=delete_recurring record=$item date_id=$item->date_id title="Delete this Event"|gettext}
                                                    {/if}
                                                {/if}
                                            </div>
                                        {/if}
                                    {/permissions}
                                </div>
                            {/foreach}
                            {if $number != -1}{$dayts=$dayts+86400}
                                {if !$dst}
                                    {if (date('I',$now) && !date('I',$dayts))}
                                        {$dayts=$dayts+3600}
                                        {$dst=true}
                                    {elseif (!date('I',$now) && date('I',$dayts))}
                                        {$dayts=$dayts-3600}
                                        {$dst=true}
                                    {/if}
                                {/if}
                            {/if}
                        </td>
                    {/foreach}
                </tr>
			{*{/if}*}
		{/foreach}
	</table>

{if $config.lightbox}
{css unique="cal-lightbox" link="`$asset_path`css/lightbox.css"}

{/css}

{script unique="shadowbox" yui3mods=1}
{literal}
    EXPONENT.YUI3_CONFIG.modules = {
        'yui2-lightbox' : {
            fullpath: EXPONENT.PATH_RELATIVE+'framework/modules/events/assets/js/lightbox.js',
            requires : ['yui2-dom','yui2-event','yui2-connectioncore','yui2-json','yui2-selector','yui2-animation']
        }
    }
    YUI(EXPONENT.YUI3_CONFIG).use('node','yui2-container','yui2-yahoo-dom-event','yui2-lightbox', function(Y) {
        var YAHOO = Y.YUI2;
        var lb2 = new this.EXPONENT.Lightbox(
            {
                animate: true,
                maxWidth: 500
            }
        );
        YAHOO.util.Event.addListener(YAHOO.util.Selector.query("a.calpopevent"), "click", function (e) {
            target = YAHOO.util.Event.getTarget(e);
            lb2.cfg.contentURL = EXPONENT.PATH_RELATIVE+"index.php?controller=event&action=show&view=show&ajax_action=1&date_id="+target.id;
            lb2.show(e);
        }, lb2, true);
    });
{/literal}
{/script}
{/if}