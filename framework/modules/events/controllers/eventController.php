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
 * @subpackage Controllers
 * @package    Modules
 */

class eventController extends expController {
//    public $basemodel_name = 'event';
    public $useractions = array(
        'showall'=> 'Show Calendar',
    );
    public $codequality = 'alpha';

	public $remove_configs = array(
        'pagination',
        'rss',
    ); // all options: ('aggregation','categories','comments','ealerts','files','module_title','pagination','rss','tags')

    static function displayname() {
        return "Events";
    }

    static function description() {
        return "Allows managing events and schedules.";
    }

    static function author() {
        return "Dave Leffler";
    }

    static function isSearchable() {
        return true;
    }

    function searchName() {
        return gt("Calendar Event");
    }

    function searchCategory() {
        return gt('Event');
    }

    function showall() {
        global $user;

        $locsql = $this->aggregateWhereClause();
        $time   = (isset($this->params['time']) ? $this->params['time'] : time());
        assign_to_template(array(
            'time'=> $time,
        ));

        $ed = new eventdate();
        $viewtype  = 'default';
        $viewrange = 'all';
        $view = !empty($this->params['view']) ? $this->params['view'] : 'showall';
        switch ($view) {
            case 'showall_Administration':
                $viewtype = "administration";
                break;
            case 'showall_Past Events':
                $viewrange = "past";
                break;
            case 'showall_Monthly Summary':
            case 'showall_Mini-Calendar':
                $viewtype = "minical";
                break;
            case 'showall_Monthly List':
            case 'showall_List':
                $viewtype = "byday";
                $viewrange = "month";
                break;
            case 'showall_Week':
                $viewtype = "byday";
                $viewrange = "week";
                break;
            case 'showall_Day':
                $viewtype = "byday";
                $viewrange = "day";
                break;
            case 'showall_Upcoming Events':
            case 'showall_Upcoming Events - Headlines':
                $viewrange = "upcoming";
                break;
            case 'showall':
                $viewtype = "monthly";
                break;
            default :
        }

        switch ($viewtype) {
            case "minical":
                $monthly    = expDateTime::monthlyDaysTimestamp($time);
                $info       = getdate($time);
                $timefirst  = mktime(0, 0, 0, $info['mon'], 1, $info['year']);
                $now        = getdate(time());
                $currentday = $now['mday'];
                $endofmonth = date('t', $time);
                foreach ($monthly as $weekNum=> $week) {
                    foreach ($week as $dayNum=> $day) {
                        if ($dayNum == $now['mday']) {
                            $currentweek = $weekNum;
                        }
                        if ($dayNum <= $endofmonth) {
//                            $monthly[$weekNum][$dayNum]['number'] = ($monthly[$weekNum][$dayNum]['ts'] != -1) ? $db->countObjects("eventdate", $locsql . " AND date >= " . expDateTime::startOfDayTimestamp($day['ts']) . " AND date <= " . expDateTime::endOfDayTimestamp($day['ts'])) : -1;
                            $monthly[$weekNum][$dayNum]['number'] = ($monthly[$weekNum][$dayNum]['ts'] != -1) ? $ed->find("count", $locsql . " AND date >= " . expDateTime::startOfDayTimestamp($day['ts']) . " AND date <= " . expDateTime::endOfDayTimestamp($day['ts'])) : -1;
                        }
                    }
                }
                $prevmonth = mktime(0, 0, 0, date("m", $timefirst) - 1, date("d", $timefirst) + 10, date("Y", $timefirst));
                $nextmonth = mktime(0, 0, 0, date("m", $timefirst) + 1, date("d", $timefirst) + 10, date("Y", $timefirst));
                assign_to_template(array(
                    "monthly"    => $monthly,
                    "currentweek"=> $currentweek,
                    "currentday" => $currentday,
                    "now"        => $timefirst,
                    "prevmonth"  => $prevmonth,
                    "thismonth"  => $timefirst,
                    "nextmonth"  => $nextmonth
                ));
                break;
            case "byday":
                // Remember this is the code for weekly view and monthly listview
                // Test your fixes on both views
                //   		$startperiod = 0;
                //			$totaldays = 0;
                switch ($viewrange) {
                    case "day":
                        $startperiod = expDateTime::startOfDayTimestamp($time);
                        $totaldays   = 1;
                        $next        = expDateTime::endOfDayTimestamp($startperiod);
                        if (!empty($this->config['starttype'])) $startperiod = $time;
                        assign_to_template(array(
                            "prev_timestamp3"=> strtotime('-3 days', $startperiod),
                            "prev_timestamp2"=> strtotime('-2 days', $startperiod),
                            "prev_timestamp" => strtotime('-1 days', $startperiod),
                            "next_timestamp"=> strtotime('+1 days', $startperiod),
                            "next_timestamp2"=> strtotime('+2 days', $startperiod),
                            "next_timestamp3"=> strtotime('+3 days', $startperiod),
                        ));
                        break;
                    case "week":
                        $startperiod = expDateTime::startOfWeekTimestamp($time);
                        $totaldays   = 7;
                        $next        = strtotime('+7 days', $startperiod);
                        if (!empty($this->config['starttype'])) $startperiod = $time;
                        assign_to_template(array(
                            "prev_timestamp3"=> strtotime('-21 days', $startperiod),
                            "prev_timestamp2"=> strtotime('-14 days', $startperiod),
                            "prev_timestamp" => strtotime('-7 days', $startperiod),
                            "next_timestamp" => $next,
                            "next_timestamp2"=> strtotime('+14 days', $startperiod),
                            "next_timestamp3"=> strtotime('+21 days', $startperiod),
                        ));
                        break;
                    case "twoweek":
                        $startperiod = expDateTime::startOfWeekTimestamp($time);
                        $totaldays   = 14;
                        $next        = strtotime('+14 days', $startperiod);
                        if (!empty($this->config['starttype'])) $startperiod = $time;
                        assign_to_template(array(
                            "prev_timestamp3"=> strtotime('-42 days', $startperiod),
                            "prev_timestamp2"=> strtotime('-28 days', $startperiod),
                            "prev_timestamp" => strtotime('-14 days', $startperiod),
                            "next_timestamp" => $next,
                            "next_timestamp2"=> strtotime('+28 days', $startperiod),
                            "next_timestamp3"=> strtotime('+42 days', $startperiod),
                        ));
                        break;
                    default: // range = month
                        $startperiod = expDateTime::startOfMonthTimestamp($time);
                        $totaldays   = date('t', $time);
                        $next        = strtotime('+1 months', $startperiod);
                        assign_to_template(array(
                            "prev_timestamp3"=> strtotime('-3 months', $startperiod),
                            "prev_timestamp2"=> strtotime('-2 months', $startperiod),
                            "prev_timestamp" => strtotime('-1 months', $startperiod),
                            "next_timestamp" => $next,
                            "next_timestamp2"=> strtotime('+2 months', $startperiod),
                            "next_timestamp3"=> strtotime('+3 months', $startperiod)
                        ));
                       break;
                }

                //                $days = array();
                // added per Ignacio
                //			$endofmonth = date('t', $time);
                //FIXME add external events to $days[$start] for date $start, one day at a time
                $extitems = $this->getExternalEvents($this->loc, $startperiod, $next);
                //FIXME add event registration events
                if (!empty($this->config['aggregate_registrations'])) $regitems = eventregistrationController::getEventsForDates($startperiod, $next);
                for ($i = 1; $i <= $totaldays; $i++) {
                    //                    $info = getdate($time);
                    //                    switch ($viewrange) {
                    //                        case "week":
                    //                            $start = mktime(0,0,0,$info['mon'],$i,$info['year']);  //FIXME this can't be right?
                    //                            break;
                    //                        case "twoweek":
                    ////                            $start = mktime(0,0,0,$info['mon'],$info['mday']+($i-1),$info['year']);  //FIXME this can't be right?
                    //                  		    $start = $startperiod + ($i*86400);
                    //                            break;
                    //                        default:  // range = month
                    //                            $start = mktime(0,0,0,$info['mon'],$i,$info['year']);
                    //                    }
                    $start        = $startperiod + ($i * 86400) - 86400;
//                    $edates       = $db->selectObjects("eventdate", $locsql . " AND date >= " . expDateTime::startOfDayTimestamp($start) . " AND date <= " . expDateTime::endOfDayTimestamp($start));
                    $edates       = $ed->find("all", $locsql . " AND date >= " . expDateTime::startOfDayTimestamp($start) . " AND date <= " . expDateTime::endOfDayTimestamp($start));
                    $days[$start] = $this->getEventsForDates($edates, true, isset($this->config['featured_only']) ? true : false);
                    //                    for ($j = 0; $j < count($days[$start]); $j++) {
                    //                        $thisloc = expCore::makeLocation($this->loc->mod,$this->loc->src,$days[$start][$j]->id);
                    //                        $days[$start][$j]->permissions = array(
                    //                            "manage"=>(expPermissions::check("manage",$thisloc) || expPermissions::check("manage",$this->loc)),
                    //                            "edit"=>(expPermissions::check("edit",$thisloc) || expPermissions::check("edit",$this->loc)),
                    //                            "delete"=>(expPermissions::check("delete",$thisloc) || expPermissions::check("delete",$this->loc))
                    //                        );
                    //                    }
                    if (!empty($extitems[$start])) $days[$start] = array_merge($extitems[$start], $days[$start]);
                    if (!empty($regitems[$start])) $days[$start] = array_merge($regitems[$start], $days[$start]);
                    $days[$start] = expSorter::sort(array('array'=> $days[$start], 'sortby'=> 'eventstart', 'order'=> 'ASC'));
                }
                assign_to_template(array(
                    "time"           => $startperiod,
                    'days'=> $days,
                    "now"        => $startperiod,
                ));
                break;
            case "monthly":
                //                $monthly = array();
                //                $counts = array();
                $info    = getdate($time);
                $nowinfo = getdate(time());
                if ($info['mon'] != $nowinfo['mon']) $nowinfo['mday'] = -10;
                // Grab non-day numbers only (before end of month)
                $week           = 0;
                $currentweek    = -1;
                $timefirst      = mktime(0, 0, 0, $info['mon'], 1, $info['year']);
                $infofirst      = getdate($timefirst);
                $monthly[$week] = array(); // initialize for non days
                $counts[$week]  = array();
                if (($infofirst['wday'] == 0) && (DISPLAY_START_OF_WEEK == 1)) {
                    for ($i = -6; $i < (1 - DISPLAY_START_OF_WEEK); $i++) {
                        $monthly[$week][$i] = array();
                        $counts[$week][$i]  = -1;
                    }
                    $weekday = $infofirst['wday'] + 7; // day number in grid.  if 7+, switch weeks
                } else {
                    for ($i = 1 - $infofirst['wday']; $i < (1 - DISPLAY_START_OF_WEEK); $i++) {
                        $monthly[$week][$i] = array();
                        $counts[$week][$i]  = -1;
                    }
                    $weekday = $infofirst['wday']; // day number in grid.  if 7+, switch weeks
                }
                // Grab day counts (deprecated, handled by the date function)
                // $endofmonth = expDateTime::endOfMonthDay($time);
                $endofmonth = date('t', $time);
                //FIXME add external events to $monthly[$week][$i] for date $start, one day at a time
                $extitems = $this->getExternalEvents($this->loc, $timefirst, expDateTime::endOfMonthTimestamp($timefirst));
                //FIXME add event registration events
                if (!empty($this->config['aggregate_registrations'])) $regitems = eventregistrationController::getEventsForDates($timefirst, expDateTime::endOfMonthTimestamp($timefirst));
                for ($i = 1; $i <= $endofmonth; $i++) {
                    $start = mktime(0, 0, 0, $info['mon'], $i, $info['year']);
                    if ($i == $nowinfo['mday']) $currentweek = $week;
                    #$monthly[$week][$i] = $db->selectObjects("event","location_data='".serialize($this->loc)."' AND (eventstart >= $start AND eventend <= " . ($start+86399) . ") AND approved!=0");
                    //$dates = $db->selectObjects("eventdate",$locsql." AND date = $start");
//                    $dates              = $db->selectObjects("eventdate", $locsql . " AND (date >= " . expDateTime::startOfDayTimestamp($start) . " AND date <= " . expDateTime::endOfDayTimestamp($start) . ")");
                    $dates = $ed->find("all", $locsql . " AND (date >= " . expDateTime::startOfDayTimestamp($start) . " AND date <= " . expDateTime::endOfDayTimestamp($start) . ")");
                    $monthly[$week][$i] = $this->getEventsForDates($dates, true, isset($this->config['featured_only']) ? true : false);
                    if (!empty($extitems[$start])) $monthly[$week][$i] = array_merge($extitems[$start], $monthly[$week][$i]);
                    if (!empty($regitems[$start])) $monthly[$week][$i] = array_merge($regitems[$start], $monthly[$week][$i]);
                    $monthly[$week][$i] = expSorter::sort(array('array'=> $monthly[$week][$i], 'sortby'=> 'eventstart', 'order'=> 'ASC'));
                    $counts[$week][$i]  = count($monthly[$week][$i]);
                    if ($weekday >= (6 + DISPLAY_START_OF_WEEK)) {
                        $week++;
                        $monthly[$week] = array(); // allocate an array for the next week
                        $counts[$week]  = array();
                        $weekday        = DISPLAY_START_OF_WEEK;
                    } else $weekday++;
                }
                // Grab non-day numbers only (after end of month)
                for ($i = 1; $weekday && $i < (8 + DISPLAY_START_OF_WEEK - $weekday); $i++) {
                    $monthly[$week][$i + $endofmonth] = array();
                    $counts[$week][$i + $endofmonth]  = -1;
                }
                assign_to_template(array(
                    "currentweek"=> $currentweek,
                    "monthly"    => $monthly,
                    "counts"     => $counts,
                    "prevmonth3" => strtotime('-3 months', $timefirst),
                    "prevmonth2" => strtotime('-2 months', $timefirst),
                    "prevmonth"  => strtotime('-1 months', $timefirst),
                    "nextmonth"  => strtotime('+1 months', $timefirst),
                    "nextmonth2" => strtotime('+2 months', $timefirst),
                    "nextmonth3" => strtotime('+3 months', $timefirst),
                    "now"        => $timefirst,
                    "today"      => expDateTime::startOfDayTimestamp(time())
                ));
                break;
            case "administration":
                // Check perms and return if cant view
                if (!$user) return;
                $continue = (expPermissions::check("manage", $this->loc) ||
                    expPermissions::check("create", $this->loc) ||
                    expPermissions::check("edit", $this->loc) ||
                    expPermissions::check("delete", $this->loc)
                ) ? 1 : 0;
//                $dates    = $db->selectObjects("eventdate", $locsql . " AND date >= " . expDateTime::startOfDayTimestamp(time()));
                $dates    = $ed->find("all", $locsql . " AND date >= " . expDateTime::startOfDayTimestamp(time()));
                $items    = $this->getEventsForDates($dates);
                //                if (!$continue) {
                //                    foreach ($items as $i) {
                //                        $iloc = expCore::makeLocation($this->loc->mod,$this->loc->src,$i->id);
                //                        if (expPermissions::check("edit",$iloc) ||
                //                            expPermissions::check("delete",$iloc) ||
                //                            expPermissions::check("manage",$iloc)
                //                        ) {
                //                            $continue = true;
                //                        }
                //                    }
                //                }
                if (!$continue) return;
                //                for ($i = 0; $i < count($items); $i++) {
                //                    $thisloc = expCore::makeLocation($this->loc->mod,$this->loc->src,$items[$i]->id);
                //    //				if ($user && $items[$i]->poster == $user->id) $canviewapproval = 1;
                //                    $items[$i]->permissions = array(
                //                        "manage"=>(expPermissions::check("manage",$thisloc) || expPermissions::check("manage",$this->loc)),
                //                        "edit"=>(expPermissions::check("edit",$thisloc) || expPermissions::check("edit",$this->loc)),
                //                        "delete"=>(expPermissions::check("delete",$thisloc) || expPermissions::check("delete",$this->loc))
                //                    );
                //                }
                $items = expSorter::sort(array('array'=> $items, 'sortby'=> 'eventstart', 'order'=> 'ASC'));
                assign_to_template(array(
                    'items'=> $items,
                ));
                break;
            case "default":
            default;
                //                $items = null;
                //                $dates = null;
                $day      = expDateTime::startOfDayTimestamp(time());
                $sort_asc = true; // For the getEventsForDates call
                //                $moreevents = false;
                switch ($viewrange) {
                    case "upcoming":
                        if (!empty($this->config['rss_limit']) && $this->config['rss_limit'] > 0) {
                            $eventlimit = " AND date <= " . ($day + ($this->config['rss_limit'] * 86400));
                        } else {
                            $eventlimit = "";
                        }
//                        $dates = $db->selectObjects("eventdate", $locsql . " AND date >= " . $day . $eventlimit . " ORDER BY date ASC ");
                        $dates = $ed->find("all", $locsql . " AND date >= " . $day . $eventlimit . " ORDER BY date ASC ");
                        $begin = $day;
                        $end   = null;
                        //					$moreevents = count($dates) < $db->countObjects("eventdate",$locsql." AND date >= $day");
                        break;
                    case "past":
//                        $dates = $db->selectObjects("eventdate", $locsql . " AND date < $day ORDER BY date DESC ");
                        $dates = $ed->find("all", $locsql . " AND date < $day ORDER BY date DESC ");
                        //					$moreevents = count($dates) < $db->countObjects("eventdate",$locsql." AND date < $day");
                        $sort_asc = false;
                        $begin    = null;
                        $end      = $day;
                        break;
                    case "today":
//                        $dates = $db->selectObjects("eventdate", $locsql . " AND (date >= " . expDateTime::startOfDayTimestamp($day) . " AND date <= " . expDateTime::endOfDayTimestamp($day) . ")");
                        $dates = $ed->find("all", $locsql . " AND (date >= " . expDateTime::startOfDayTimestamp($day) . " AND date <= " . expDateTime::endOfDayTimestamp($day) . ")");
                        $begin = $day;
                        $end   = expDateTime::endOfDayTimestamp($day);
                        break;
                    case "day":
//                        $dates = $db->selectObjects("eventdate", $locsql . " AND (date >= " . expDateTime::startOfDayTimestamp($day) . " AND date <= " . expDateTime::endOfDayTimestamp($day) . ")");
                        $dates = $ed->find("all", $locsql . " AND (date >= " . expDateTime::startOfDayTimestamp($time) . " AND date <= " . expDateTime::endOfDayTimestamp($time) . ")");
                        $begin = expDateTime::startOfDayTimestamp($time);
                        $end   = expDateTime::endOfDayTimestamp($time);
                        break;
                    case "next":
//                        $dates = array($db->selectObject("eventdate", $locsql . " AND date >= $day"));
                        $dates = array($ed->find("all", $locsql . " AND date >= $day"));
                        break;
                    case "month":
//                        $dates = $db->selectObjects("eventdate", $locsql . " AND (date >= " . expDateTime::startOfMonthTimestamp(time()) . " AND date <= " . expDateTime::endOfMonthTimestamp(time()) . ")");
                        $dates = $ed->find("all", $locsql . " AND (date >= " . expDateTime::startOfMonthTimestamp(time()) . " AND date <= " . expDateTime::endOfMonthTimestamp(time()) . ")");
                        $begin = expDateTime::startOfMonthTimestamp($day);
                        $end   = expDateTime::endOfMonthTimestamp($day);
                        break;
                    case "all":
                    default;
//                        $dates = $db->selectObjects("eventdate", $locsql);
                        $dates = $ed->find("all", $locsql);
                        $begin = null;
                        $end   = null;
                }
                $items = $this->getEventsForDates($dates, $sort_asc, isset($this->config['featured_only']) ? true : false);
                //FIXME add external events to $items for date >= ".expDateTime::startOfMonthTimestamp(time()) . " AND date <= " . expDateTime::endOfMonthTimestamp(time())
                $extitems = $this->getExternalEvents($this->loc, $begin, $end);
                // we need to crunch these down
                $extitem = array();
                foreach ($extitems as $key=> $value) {
                    $extitem[] = $value;
                }
                $items = array_merge($items, $extitem);
                    //FIXME add event registration events
                if (!empty($this->config['aggregate_registrations'])) $regitems = eventregistrationController::getEventsForDates($begin, $end);
                 // we need to crunch these down
                $regitem = array();
                foreach ($regitems as $key=> $value) {
                    $regitem[] = $value;
                }
                $items = array_merge($items, $regitem);
                $items = expSorter::sort(array('array'=> $items, 'sortby'=> 'eventstart', 'order'=> 'ASC'));
                // Upcoming events can be configured to show a specific number of events.
                // The previous call gets all events in the future from today
                // If configured, cut the array to the configured number of events
                //			if ($template->viewconfig['num_events']) {
                //				switch ($viewrange) {
                //					case "upcoming":
                //					case "past":
                //						$moreevents = $template->viewconfig['num_events'] < count($items);
                //						break;
                //				}
                //				$items = array_slice($items, 0, $template->viewconfig['num_events']);
                //			}
                //                for ($i = 0; $i < count($items); $i++) {
                //                    $thisloc = expCore::makeLocation($this->loc->mod,$this->loc->src,$items[$i]->id);
                //                    $items[$i]->permissions = array(
                //                        'manage'=>(expPermissions::check('manage',$thisloc) || expPermissions::check('manage',$this->loc)),
                //                        'edit'=>(expPermissions::check('edit',$thisloc) || expPermissions::check('edit',$this->loc)),
                //                        'delete'=>(expPermissions::check('delete',$thisloc) || expPermissions::check('delete',$this->loc))
                //                    );
                //                }
                assign_to_template(array(
                    'items'=> $items,
                    "now"        => $day,
                ));
        }
    }

    /**
   	 * default view for individual item
   	 */
   	function show() {
       expHistory::set('viewable', $this->params);
       $eventdate = new eventdate($this->params['id']);
       if (!empty($eventdate->event->feedback_form)) {
           assign_to_template(array(
              'feedback_form'=>$eventdate->event->feedback_form,
          ));
        }

       assign_to_template(array(
           'event'=>$eventdate,
       ));
   }

    function edit() {
        parent::edit();
        $allforms = array();
        $allforms[''] = gt('Disallow Feedback');
        assign_to_template(array(
            'allforms'=> array_merge($allforms, expCore::buildNameList("forms", "event/email", "tpl", "[!_]*")),
            'checked_date'=> !empty($this->params['date_id']) ? $this->params['date_id'] : null,
        ));
    }

    /**
     * Delete a recurring event by asking for which event dates to delete
     *
     */
    function delete_recurring() {
        $item = $this->event->find('first','id='.$this->params['id']);
        if ($item->is_recurring == 1) { // need to give user options
            assign_to_template(array(
                'checked_date'=> $this->params['date_id'],
                'event'=>$item,
            ));
        }  else {  // Process a regular delete
            $item->delete();
        }
    }

    /**
     * Delete selected event dates for a recurring event and event if all event dates deleted
     *
     */
    function delete_selected() {
        $item = $this->event->find('first','id='.$this->params['id']);
        if ($item && $item->is_recurring == 1) {
            $event_remaining = false;
            $eventdates = $item->eventdate[0]->find('all','event_id='.$item->id);
            foreach ($eventdates as $ed) {
                if (array_key_exists($ed->id,$this->params['dates'])) {
                    $ed->delete();
                } else {
                    $event_remaining = true;
                }
            }
            if (!$event_remaining) {
                $item->delete();  // model will also ensure we delete all event dates
            }
            expHistory::back();
        } else {
        	echo SITE_404_HTML;
        }
    }

    function delete_all_past() {
        $locsql = $this->aggregateWhereClause();
        $ed = new eventdate();
        $dates = $ed->find("all",$locsql." AND date < ".strtotime('-1 months',time()));
        foreach ($dates as $date) {
            $date->delete();  // event automatically deleted if all assoc eventdates are deleted
        }
        expHistory::back();
    }

    function send_feedback() {
        $success = false;
        if (isset($this->params['id'])) {
            $ed = new eventdate($this->params['id']);
//            $email_addrs = array();
            if ($ed->event->feedback_email != '') {
                $msgtemplate = get_template_for_action($this, 'email/_'.$this->params['formname'], $this->loc);
                $msgtemplate->assign('params', $this->params);
                $msgtemplate->assign('event', $ed);
                $email_addrs = explode(',', $ed->event->feedback_email);
                //This is an easy way to remove duplicates
                $email_addrs = array_flip(array_flip($email_addrs));
                $email_addrs = array_map('trim', $email_addrs);
                $mail = new expMail();
                $success += $mail->quickSend(array(
                        "text_message"=>$msgtemplate->render(),
                        'to'=>$email_addrs,
                        'from'=>!empty($this->params['email']) ? $this->params['email'] : trim(SMTP_FROMADDRESS),
                        'subject'=>$this->params['subject'],
                ));
            }
        }

        if ($success) {
            flash('message', gt('Your feedback was successfully sent.'));
        } else {
            flash('error', gt('We could not send your feedback.  Please contact your administrator.'));
        }
        expHistory::back();
    }

    function ical() {
//        global $db;

        // id & date_id set if single event, else
        //   src & time (opt?) set for longer list/month, etc...
//        if (isset($_GET['date_id']) || isset($_GET['title']) || isset($_GET['src'])) {
        if (isset($this->params['date_id']) || isset($this->params['title']) || isset($this->params['src'])) {
//          $config = $db->selectObject("calendarmodule_config","sef_url='".expString::sanitize($_GET['title'])."'");
            $cfg = new expConfig();
            $configs = $cfg->find('all',"location_data LIKE '%event%'");  // get all event module configs
            foreach ($configs as $config) {
                $loc = expUnserialize($config->location_data);
                if (isset($this->params['title'])) {
                    if ($this->params['title'] == $config->feed_sef_url) {
//                        $icalconfig = $config;
                        break;
                    }
                } elseif (isset($this->params['src'])) {
                    if ($this->params['src'] == $loc->src) {
//                        $icalconfig = $config;
                        break;
                    }
                }
            }
            $this->config = $config->config;
            $this->loc = $loc;

//            if (!empty($this->params['title'])) {
//                if (!empty($config)) {
//        //            $loc = expUnserialize($config->location_data);
//                    $locsql = "(location_data='".$config->location_data."'";
//                } else {
//                    echo SITE_404_HTML;
//                    exit();
//                }
//            } else {
//                $loc = expCore::makeLocation('calendarmodule',$this->params['src'],'');
//                $locsql = "(location_data='".serialize($loc)."'";
//                $config = $db->selectObject("calendarmodule_config","location_data='".serialize($loc)."'");
//            }
//            if (!$config) {
//                $config->enable_ical = 1;
//            }


            if ($this->config['enable_ical']) {
                $ed = new eventdate();
                if (isset($this->params['date_id'])) {  // get single specific event only
//                    $dates = array($db->selectObject("eventdate","id=".$this->params['date_id']));
                    $dates = $ed->find('first',"id=".$this->params['date_id']);
                    $Filename = "Event-" . $this->params['date_id'];
                } else {
                    $locsql = $this->aggregateWhereClause();
//                    if (!empty($config->aggregate)) {
//                        $locations = unserialize($config->aggregate);
//                        foreach ($locations as $source) {
//                            $tmploc = new stdClass();
//                            $tmploc->mod = 'calendarmodule';
//                            $tmploc->src = $source;
//                            $tmploc->int = '';
//                            $locsql .= " OR location_data='".serialize($tmploc)."'";
//                        }
//                    }
//                    $locsql .= ')';

                    $day = expDateTime::startOfDayTimestamp(time());
                    if (isset($this->config['rss_limit']) && ($this->config['rss_limit'] > 0)) {
                        $rsslimit = " AND date <= " . ($day + ($this->config['rss_limit'] * 86400));
                    } else {
                        $rsslimit = "";
                    }

//                    $cats = $db->selectObjectsIndexedArray("category");
//                    $cats[0] = new stdClass();
//                    $cats[0]->name = 'None';

                    if (isset($this->params['time'])) {
                        $time = $this->params['time'];  // get current month's events
//                        $dates = $db->selectObjects("eventdate",$locsql." AND (date >= ".expDateTime::startOfMonthTimestamp($time)." AND date <= ".expDateTime::endOfMonthTimestamp($time).")");
                        $dates = $ed->find('all',$locsql." AND (date >= ".expDateTime::startOfMonthTimestamp($time)." AND date <= ".expDateTime::endOfMonthTimestamp($time).")");
                    } else {
                        $time = date('U',strtotime("midnight -1 month",time()));  // previous month also
//                        $dates = $db->selectObjects("eventdate",$locsql." AND date >= ".expDateTime::startOfDayTimestamp($time).$rsslimit);
                        $dates = $ed->find('all',$locsql." AND date >= ".expDateTime::startOfDayTimestamp($time).$rsslimit);
                    }
        //			$title = $db->selectValue('container', 'title', "internal='".serialize($loc)."'");
                    $title = $this->config['feed_title'];
                    $Filename = preg_replace('/\s+/','',$title);  // without whitespace
                }

                if (!function_exists("quoted_printable_encode")) {  // function added in php v5.3.0
                    function quoted_printable_encode($input, $line_max = 75) {
                       $hex = array('0','1','2','3','4','5','6','7',
                                              '8','9','A','B','C','D','E','F');
                       $lines = preg_split("/(?:\r\n|\r|\n)/", $input);
                       $linebreak = "=0D=0A=\r\n";
                       /* the linebreak also counts as characters in the mime_qp_long_line
                        * rule of spam-assassin */
                       $line_max = $line_max - strlen($linebreak);
                       $escape = "=";
                       $output = "";
                       $cur_conv_line = "";
                       $length = 0;
                       $whitespace_pos = 0;
                       $addtl_chars = 0;

                       // iterate lines
                       for ($j=0; $j<count($lines); $j++) {
                         $line = $lines[$j];
                         $linlen = strlen($line);

                         // iterate chars
                         for ($i = 0; $i < $linlen; $i++) {
                           $c = substr($line, $i, 1);
                           $dec = ord($c);

                           $length++;

                           if ($dec == 32) {
                              // space occurring at end of line, need to encode
                              if (($i == ($linlen - 1))) {
                                 $c = "=20";
                                 $length += 2;
                              }

                              $addtl_chars = 0;
                              $whitespace_pos = $i;
                           } elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) {
                              $h2 = floor($dec/16); $h1 = floor($dec%16);
                              $c = $escape . $hex["$h2"] . $hex["$h1"];
                              $length += 2;
                              $addtl_chars += 2;
                           }

                           // length for wordwrap exceeded, get a newline into the text
                           if ($length >= $line_max) {
                             $cur_conv_line .= $c;

                             // read only up to the whitespace for the current line
                             $whitesp_diff = $i - $whitespace_pos + $addtl_chars;

                            /* the text after the whitespace will have to be read
                             * again ( + any additional characters that came into
                             * existence as a result of the encoding process after the whitespace)
                             *
                             * Also, do not start at 0, if there was *no* whitespace in
                             * the whole line */
                             if (($i + $addtl_chars) > $whitesp_diff) {
                                $output .= substr($cur_conv_line, 0, (strlen($cur_conv_line) -
                                               $whitesp_diff)) . $linebreak;
                                $i =  $i - $whitesp_diff + $addtl_chars;
                              } else {
                                $output .= $cur_conv_line . $linebreak;
                              }

                            $cur_conv_line = "";
                            $length = 0;
                            $whitespace_pos = 0;
                          } else {
                            // length for wordwrap not reached, continue reading
                            $cur_conv_line .= $c;
                          }
                        } // end of for

                        $length = 0;
                        $whitespace_pos = 0;
                        $output .= $cur_conv_line;
                        $cur_conv_line = "";

                        if ($j<=count($lines)-1) {
                          $output .= $linebreak;
                        }
                      } // end for

                      return trim($output);
                    } // end quoted_printable_encode
                }

                $tz = DISPLAY_DEFAULT_TIMEZONE;
                $msg = "BEGIN:VCALENDAR\n";
                $msg .= "VERSION:2.0\n";  // version for iCalendar files vs vCalendar files
                $msg .= "CALSCALE:GREGORIAN\n";
                $msg .= "METHOD: PUBLISH\n";
                $msg .= "PRODID:<-//ExponentCMS//EN>\n";
                if (isset($this->config['rss_cachetime']) && ($this->config['rss_cachetime'] > 0)) {
                    $msg .= "X-PUBLISHED-TTL:PT".$this->config['rss_cachetime']."M\n";
                }
                $msg .= "X-WR-CALNAME:$Filename\n";

                $items = $this->getEventsForDates($dates);

                for ($i = 0; $i < count($items); $i++) {

                    // Convert events stored in local time to GMT
                    $eventstart = new DateTime(date('r',$items[$i]->eventstart),new DateTimeZone($tz));
                    $eventstart->setTimezone(new DateTimeZone('GMT'));
                    $eventend = new DateTime(date('r',$items[$i]->eventend),new DateTimeZone($tz));
                    $eventend->setTimezone(new DateTimeZone('GMT'));
                    if ($items[$i]->is_allday) {
                        $dtstart = "DTSTART;VALUE=DATE:" . date("Ymd", $items[$i]->eventstart) . "\n";
                        $dtend = "DTEND;VALUE=DATE:" . date("Ymd", strtotime("midnight +1 day",$items[$i]->eventstart)) . "\n";
                    } else {
                        $dtstart = "DTSTART;VALUE=DATE-TIME:" . $eventstart->format("Ymd\THi00") . "Z\n";
                        if($items[$i]->eventend) {
                            $dtend = "DTEND;VALUE=DATE-TIME:" . $eventend->format("Ymd\THi00") . "Z\n";
                        } else {
                            $dtend = "DTEND;VALUE=DATE-TIME:" . $eventstart->format("Ymd\THi00") . "Z\n";
                        }
                    }

                    if (!isset($this->params['style'])) {
                        // it's going to Outlook so remove all formatting from body text
                //		$body = chop(strip_tags(str_replace(array("<br />","<br>","br/>","\r","\n"),"\r\n",$items[$i]->body)));
                //		$body = chop(strip_tags(str_replace(array("<br />","<br>","br/>"),"\r",$items[$i]->body)));
                //		$body = str_replace(array("\r","\n"), "=0D=0A=", $body);
                        $body = chop(strip_tags(str_replace(array("<br />","<br>","br/>","</p>"),"\n",$items[$i]->body)));
                        $body = str_replace(array("\r"),"",$body);
                        $body = str_replace(array("&#160;")," ",$body);
                        $body = expString::convertSmartQuotes($body);
                        $body = quoted_printable_encode($body);
                //		$body = str_replace(array("\n"), "=0D=0A", $body);

                        // $body = chop(strip_tags(str_replace(array("<br />","<br>","br/>"),"\r",$items[$i]->body)));
                        // $body = wordwrap($body);
                        // $body = str_replace("\n","\n  ",$body);

                //		$body = chop(strip_tags(str_replace(array("<br />","<br>","br/>"),"\r",$items[$i]->body)));
                //		$body = chop(strip_tags(str_replace(array("<br />","<br>","br/>"),"\n",$items[$i]->body)));
                //		$body = str_replace(array("\r","\n"), "=0D=0A=", $body);
                //		$body = str_replace(array("\r"), "=0D=0A=", $body);
                //		$body = str_replace(array("\r","\n"), "\r\n", $body);

                    } elseif ($this->params['style'] == "g") {
                        // It's going to Google (doesn't like quoted-printable, but likes html breaks)
                        $body = $items[$i]->body;
                        $body = chop(strip_tags(str_replace(array("<br />","<br>","br/>","</p>"),"\n",$items[$i]->body)));
        //				$body = chop(strip_tags($items[$i]->body,"<br><p>"));
                        $body = str_replace(array("\r"),"",$body);
                        $body = str_replace(array("&#160;")," ",$body);
                        $body = expString::convertSmartQuotes($body);
                        $body = str_replace(array("\n"),"<br />",$body);
                    } else {
                        // It's going elsewhere (doesn't like quoted-printable)
                        $body = $items[$i]->body;
                        $body = chop(strip_tags(str_replace(array("<br />","<br>","br/>","</p>"),"\n",$items[$i]->body)));
        //				$body = chop(strip_tags($items[$i]->body,"<br><p>"));
                        $body = str_replace(array("\r"),"",$body);
                        $body = str_replace(array("&#160;")," ",$body);
                        $body = expString::convertSmartQuotes($body);
                        $body = str_replace(array("\n")," -- ",$body);
                    }
                    $title = $items[$i]->title;

                    $msg .= "BEGIN:VEVENT\n";
                    $msg .= $dtstart . $dtend;
                    $msg .= "UID:" . $items[$i]->eventdate->id . "\n";
                    $msg .= "DTSTAMP:" . date("Ymd\THis", time()) . "Z\n";
                    if($title) { $msg .= "SUMMARY:$title\n";}
        //			if($body) { $msg .= "DESCRIPTION;ENCODING=QUOTED-PRINTABLE:".quoted_printable_encode($body)."\n";}
                    if($body) { $msg .= "DESCRIPTION;ENCODING=QUOTED-PRINTABLE:".$body."\n";}
                //	if($link_url) { $msg .= "URL: $link_url\n";}
//                    $msg .= "CATEGORIES:".$cats[$items[$i]->category_id]->name."\n";
                    $msg .= "END:VEVENT\n";
                }
                $msg .= "END:VCALENDAR";

                // Kick it out as a file download
                ob_end_clean();

            //	$mime_type = (EXPONENT_USER_BROWSER == 'IE' || EXPONENT_USER_BROWSER == 'OPERA') ? 'application/octet-stream;' : "text/x-vCalendar";
            //	$mime_type = "text/x-vCalendar";
                $mime_type = "text/Calendar";
                header('Content-Type: ' . $mime_type);
                header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header("Content-length: ".strlen($msg));
                header('Content-Transfer-Encoding: binary');
                header('Content-Encoding:');
            //	header("Content-Disposition: inline; filename=".$Filename.".ics");
                header('Content-Disposition: attachment; filename="' . $Filename . '.ics"');
                // IE need specific headers
            //	if (EXPONENT_USER_BROWSER == 'IE') {
                    header('Cache-Control: no-cache, must-revalidate');
                    header('Pragma: public');
                    header('Vary: User-Agent');
            //	} else {
                    header('Pragma: no-cache');
            //	}
                echo $msg;
                exit();
            } else {
                echo SITE_404_HTML;
            }
        } else {
            echo SITE_404_HTML;
        }
    }

    function send_reminders() {  //FIXME we need to complete this
        global $user;
        global $db;

        // first, find the calendar/event data
        if (isset($_GET['id'])) {
            $config = $db->selectObject("calendarmodule_config","id='".intval($_GET['id'])."'");
        } elseif (isset($_GET['title'])) {
            $config = $db->selectObject("calendarmodule_config","sef_url='".expString::sanitize($_GET['title'])."'");
        } elseif (isset($_GET['src'])) {
            $config = $db->selectObject("calendarmodule_config","location_data='".serialize(expCore::makeLocation('calendarmodule',expString::sanitize($_GET['src']),''))."'");
        } else {
            print_r("<br><strong><em>Exponent - ".gt('No Calendar Selected!')."</em></strong><br>");
            exit();
        }
        if (!$config) {
        	print_r("<br><strong><em>Exponent - ".gt('Calendar Not Found!')."</em></strong><br>");
        	exit();
        }

        $loc = unserialize($config->location_data);
        $locsql = "(location_data='".serialize($loc)."'";
        if (!empty($config->aggregate)) {
        	$locations = unserialize($config->aggregate);
        	foreach ($locations as $source) {
        		$tmploc = new stdClass();
        		$tmploc->mod = 'calendarmodule';
        		$tmploc->src = $source;
        		$tmploc->int = '';
        		$locsql .= " OR location_data='".serialize($tmploc)."'";
        	}
        }
        $locsql .= ')';

        $view = (isset($_GET['view']) ? $_GET['view'] : '');
        if ($view == "") {
        	$view = "_reminder";	// default reminder view
        }

        $template = new template('calendarmodule',$view,$loc);
        //if ($title == '') {
        //$title = $db->selectValue('container', 'title', "internal='".serialize($loc)."'");
        $title = $config->feed_title;
        //}
        $template->assign('moduletitle',$title);

        $time = (isset($_GET['time']) ? $_GET['time'] : time());
        $time = intval($time);

        $template->assign("time",$time);

        // $viewparams = $template->viewparams;
        // if ($viewparams === null) {
        	// $viewparams = array("type"=>"byday", "range"=>"week");
        // }



        // if ($viewparams['type'] == "minical") {
        	// $monthly = expDateTime::monthlyDaysTimestamp($time);
        	// $info = getdate($time);
        	// $timefirst = mktime(12,0,0,$info['mon'],1,$info['year']);
        	// $now = getdate(time());
        	// $currentday = $now['mday'];
        	// $endofmonth = date('t', $time);
        	// foreach ($monthly as $weekNum=>$week) {
        		// foreach ($week as $dayNum=>$day) {
        			// if ($dayNum == $now['mday']) {
        				// $currentweek = $weekNum;
        			// }
        			// if ($dayNum <= $endofmonth) {
        				// $monthly[$weekNum][$dayNum]['number'] = ($monthly[$weekNum][$dayNum]['ts'] != -1) ? $db->countObjects("eventdate",$locsql." AND date = ".$day['ts']) : -1;
        			// }
        		// }
        	// }
        // //eDebug($monthly);
        	// $template->assign("monthly",$monthly);
        	// $template->assign("currentweek",$currentweek);
        	// $template->assign("currentday",$currentday);
        	// $template->assign("now",$timefirst);
        	// $prevmonth = mktime(0, 0, 0, date("m",$timefirst)-1, date("d",$timefirst)+10,   date("Y",$timefirst));
        	// $nextmonth = mktime(0, 0, 0, date("m",$timefirst)+1, date("d",$timefirst)+10,   date("Y",$timefirst));
        	// $template->assign("prevmonth",$prevmonth);
        	// $template->assign("thismonth",$timefirst);
        	// $template->assign("nextmonth",$nextmonth);



        // } else if ($viewparams['type'] == "byday") {
        // Remember this is the code for weekly view and monthly listview
        // Test your fixes on both views before submitting your changes to cvs
        $startperiod = expDateTime::startOfDayTimestamp($time);
        $totaldays = $_GET['days'];
        if ($totaldays == "") {
        	$totaldays = 7;	// default 7 days of events
        }

        //	if ($viewparams['range'] == "week") {
        //		$startperiod = expDateTime::startOfWeekTimestamp($time);
        //		$totaldays = 7;
        //	} else if ($viewparams['range'] == "twoweek") {
        //		$startperiod = expDateTime::startOfWeekTimestamp($time);
        //		$totaldays = 14;
        //	} else {  // range = month
        //		$startperiod = expDateTime::startOfMonthTimestamp($time);
        //		$totaldays  = date('t', $time);
        //	}
        //	$template->assign("prev_timestamp",$startperiod - 3600);
        //	$template->assign("next_timestamp",$startperiod+(($totaldays * 86400) + 3600));

        //	$days = array();
        $count = 0;
        $info = getdate($startperiod);
        for ($i = 0; $i < $totaldays; $i++) {
        	$start = mktime(0,0,0,$info['mon'],$info['mday']+$i,$info['year']);
        	// if ( $viewparams['range'] == "week" ) {
        		// $start = mktime(0,0,0,$info['mon'],$info['mday']+$i,$info['year']);
        	// } else if ( $viewparams['range'] == "twoweek" ) {
        		// $start = mktime(0,0,0,$info['mon'],$info['mday']+$i,$info['year']);
        // //          $start = $startperiod + ($i*86400);
        	// } else {  // range = month
        		// $start = mktime(0,0,0,$info['mon'],$i,$info['year']);
        	// }
        	//$edates = $db->selectObjects("eventdate",$locsql." AND date = '".$start."'");
        	$edates = $db->selectObjects("eventdate",$locsql." AND (date >= ".expDateTime::startOfDayTimestamp($start)." AND date <= ".expDateTime::endOfDayTimestamp($start).")");
        	$days[$start] = array();
        	$days[$start] = calendarmodule::_getEventsForDates($edates);
        	for ($j = 0; $j < count($days[$start]); $j++) {
        		$thisloc = expCore::makeLocation($loc->mod,$loc->src,$days[$start][$j]->id);
        		$days[$start][$j]->permissions = array(
        			"manage"=>(expPermissions::check("manage",$thisloc) || expPermissions::check("manage",$loc)),
        			"edit"=>(expPermissions::check("edit",$thisloc) || expPermissions::check("edit",$loc)),
        			"delete"=>(expPermissions::check("delete",$thisloc) || expPermissions::check("delete",$loc))
        		);
        	}
        	$counts[$start] = count($days[$start]);
        	$count += count($days[$start]);
        	$days[$start] = expSorter::sort(array('array'=>$days[$start],'sortby'=>'eventstart', 'order'=>'ASC'));
        }
        $template->assign("days",$days);
        $template->assign("counts",$counts);
        $template->assign("start",$startperiod);
        $template->assign("totaldays",$totaldays);



        // } else if ($viewparams['type'] == "monthly") {
        	// $monthly = array();
        	// $counts = array();
        	// $info = getdate($time);
        	// $nowinfo = getdate(time());
        	// if ($info['mon'] != $nowinfo['mon']) $nowinfo['mday'] = -10;
        	// // Grab non-day numbers only (before end of month)
        	// $week = 0;
        	// $currentweek = -1;
        	// $timefirst = mktime(12,0,0,$info['mon'],1,$info['year']);
        	// $infofirst = getdate($timefirst);
        	// $monthly[$week] = array(); // initialize for non days
        	// $counts[$week] = array();
        	// if ( ($infofirst['wday'] == 0) && (DISPLAY_START_OF_WEEK == 1) ) {
        		// for ($i = -6; $i < (1-DISPLAY_START_OF_WEEK); $i++) {
        			// $monthly[$week][$i] = array();
        			// $counts[$week][$i] = -1;
        		// }
        		// $weekday = $infofirst['wday']+7; // day number in grid.  if 7+, switch weeks
        	// } else {
        		// for ($i = 1 - $infofirst['wday']; $i < (1-DISPLAY_START_OF_WEEK); $i++) {
        			// $monthly[$week][$i] = array();
        			// $counts[$week][$i] = -1;
        		// }
        		// $weekday = $infofirst['wday']; // day number in grid.  if 7+, switch weeks
        	// }
        	// // Grab day counts (deprecated, handled by the date function)
        	// // $endofmonth = expDateTime::endOfMonthDay($time);
        	// $endofmonth = date('t', $time);
        	// for ($i = 1; $i <= $endofmonth; $i++) {
        		// $start = mktime(0,0,0,$info['mon'],$i,$info['year']);
        		// if ($i == $nowinfo['mday']) $currentweek = $week;
        		// #$monthly[$week][$i] = $db->selectObjects("calendar","location_data='".serialize($loc)."' AND (eventstart >= $start AND eventend <= " . ($start+86399) . ") AND approved!=0");
        		// //$dates = $db->selectObjects("eventdate",$locsql." AND date = $start");
        		// $dates = $db->selectObjects("eventdate",$locsql." AND date = '".$start."'");
        		// $monthly[$week][$i] = calendarmodule::_getEventsForDates($dates);
        		// $counts[$week][$i] = count($monthly[$week][$i]);
        		// if ($weekday >= (6+DISPLAY_START_OF_WEEK)) {
        			// $week++;
        			// $monthly[$week] = array(); // allocate an array for the next week
        			// $counts[$week] = array();
        			// $weekday = DISPLAY_START_OF_WEEK;
        		// } else $weekday++;
        	// }
        	// // Grab non-day numbers only (after end of month)
        	// for ($i = 1; $weekday && $i < (8+DISPLAY_START_OF_WEEK-$weekday); $i++) {
        		// $monthly[$week][$i+$endofmonth] = array();
        		// $counts[$week][$i+$endofmonth] = -1;
        	// }
        // //eDebug($monthly);
        	// $template->assign("currentweek",$currentweek);
        	// $template->assign("monthly",$monthly);
        	// $template->assign("counts",$counts);
        	// $template->assign("nextmonth",$timefirst+(86400*45));
        	// $template->assign("prevmonth",$timefirst-(86400*15));
        	// $template->assign("now",$timefirst);
        	// $template->assign("today",strtotime('today')-39600);
        // } else if ($viewparams['type'] == "administration") {
        	// // Check perms and return if cant view
        	// if ($viewparams['type'] == "administration" && !$user) return;
        	// $continue = (
        		// expPermissions::check("manage",$loc) ||
        		// expPermissions::check("create",$loc) ||
        		// expPermissions::check("edit",$loc) ||
        		// expPermissions::check("delete",$loc)
        		// ) ? 1 : 0;
        	// $dates = $db->selectObjects("eventdate",$locsql);
        	// $items = calendarmodule::_getEventsForDates($dates);
        	// if (!$continue) {
        		// foreach ($items as $i) {
        			// $iloc = expCore::makeLocation($loc->mod,$loc->src,$i->id);
        			// if (expPermissions::check("edit",$iloc) ||
        				// expPermissions::check("delete",$iloc) ||
        				// expPermissions::check("manage",$iloc)
        			// ) {
        				// $continue = true;
        			// }
        		// }
        	// }
        	// if (!$continue) return;
        	// for ($i = 0; $i < count($items); $i++) {
        		// $thisloc = expCore::makeLocation($loc->mod,$loc->src,$items[$i]->id);
        		// if ($user && $items[$i]->poster == $user->id) $canviewapproval = 1;
        		// $items[$i]->permissions = array(
        			// "manage"=>(expPermissions::check("manage",$thisloc) || expPermissions::check("manage",$loc)),
        			// "edit"=>(expPermissions::check("edit",$thisloc) || expPermissions::check("edit",$loc)),
        			// "delete"=>(expPermissions::check("delete",$thisloc) || expPermissions::check("delete",$loc))
        		// );
        	// }
        	// $items = expSorter::sort(array('array'=>$items,'sortby'=>'eventstart', 'order'=>'ASC'));
        	// $template->assign("items",$items);
        // } else if ($viewparams['type'] == "default") {
        	// if (!isset($viewparams['range'])) $viewparams['range'] = "all";
        	// $items = null;
        	// $dates = null;
        	// $day = expDateTime::startOfDayTimestamp(time());
        	// $sort_asc = true; // For the getEventsForDates call
        	// $moreevents = false;
        	// switch ($viewparams['range']) {
        		// case "all":
        			// $dates = $db->selectObjects("eventdate",$locsql);
        			// break;
        		// case "upcoming":
        			// $dates = $db->selectObjects("eventdate",$locsql." AND date >= $day ORDER BY date ASC ");
        // //			$moreevents = count($dates) < $db->countObjects("eventdate",$locsql." AND date >= $day");
        			// break;
        		// case "past":
        			// $dates = $db->selectObjects("eventdate",$locsql." AND date < $day ORDER BY date DESC ");
        // //			$moreevents = count($dates) < $db->countObjects("eventdate",$locsql." AND date < $day");
        			// $sort_asc = false;
        			// break;
        		// case "today":
        			// $dates = $db->selectObjects("eventdate",$locsql." AND date = $day");
        			// break;
        		// case "next":
        			// $dates = array($db->selectObject("eventdate",$locsql." AND date >= $day"));
        			// break;
        		// case "month":
        			// $dates = $db->selectObjects("eventdate",$locsql." AND date >= ".expDateTime::startOfMonthTimestamp(time()) . " AND date <= " . expDateTime::endOfMonthTimestamp(time()));
        			// break;
        	// }
        	// $items = calendarmodule::_getEventsForDates($dates,$sort_asc,$template->viewconfig['featured_only'] ? true : false);
        // // Upcoming events can be configured to show a specific number of events.
        // // The previous call gets all events in the future from today
        // // If configured, cut the array to the configured number of events
        // //			if ($template->viewconfig['num_events']) {
        // //				switch ($viewparams['range']) {
        // //					case "upcoming":
        // //					case "past":
        // //						$moreevents = $template->viewconfig['num_events'] < count($items);
        // //						break;
        // //				}
        // //				$items = array_slice($items, 0, $template->viewconfig['num_events']);
        // //eDebug($items);
        // //			}
        	// for ($i = 0; $i < count($items); $i++) {
        		// $thisloc = expCore::makeLocation($loc->mod,$loc->src,$items[$i]->id);
        		// if ($user && $items[$i]->poster == $user->id) $canviewapproval = 1;
        		// $items[$i]->permissions = array(
        			// 'manage'=>(expPermissions::check('manage',$thisloc) || expPermissions::check('manage',$loc)),
        			// 'edit'=>(expPermissions::check('edit',$thisloc) || expPermissions::check('edit',$loc)),
        			// 'delete'=>(expPermissions::check('delete',$thisloc) || expPermissions::check('delete',$loc))
        		// );
        	// }
        	// //Get the image file if there is one.
        	// for ($i = 0; $i < count($items); $i++) {
        		// if (isset($items[$i]->file_id) && $items[$i]->file_id > 0) {
        			// $file = $db->selectObject('file', 'id='.$items[$i]->file_id);
        			// $items[$i]->image_path = $file->directory.'/'.$file->filename;
        		// }
        	// }
        // //eDebug($items);
        	// $template->assign('items',$items);
        	// $template->assign('moreevents',$moreevents);
        // }


        if ($count == 0) {
        	print_r("<br><strong><em>Exponent - ".gt('No Events to Send!')."</em></strong><br>");
        	exit();
        }

        // $template->assign('in_approval',$inapproval);
        // $template->assign('canview_approval_link',$canviewapproval);
        // $template->register_permissions(
        	// array('manage','configure','create','edit','delete'),
        	// $loc
        // );

        //$cats = $db->selectObjectsIndexedArray("category","location_data='".serialize($loc)."'");
        //$cats = $db->selectObjectsIndexedArray("category");
        //$cats[0] = null;
        //$cats[0]->name = '<i>'.gt('No Category').'</i>';
        //$cats[0]->color = "#000000";
        //$template->assign("categories",$cats);

        $template->assign("config",$config);

        //Get the tags that have been selected to be shown in the grouped by tag views
        // if (isset($config->show_tags)) {
        	// $available_tags = unserialize($config->show_tags);
        // } else {
        	// $available_tags = array();
        // }

        // if (isset($items) && is_array($items)) {
        	// for ($i = 0; $i < count($items); $i++) {
        	// //Get the tags for this calendar event
        		// $selected_tags = array();
        		// $tag_ids = unserialize($items[$i]->tags);
        		// if(!empty($tag_ids)) {$selected_tags = $db->selectObjectsInArray('tags', $tag_ids, 'name');}
        		// $items[$i]->tags = $selected_tags;

        	// //If this module was configured to group the newsitems by tags, then we need to change the data array a bit
        		// if (isset($config->group_by_tags) && $config->group_by_tags == true) {
        			// $grouped_news = array();
        			// foreach($items[$i]->tags as $tag) {
        				// if (in_array($tag->id, $available_tags) || count($available_tags) == 0) {
        					// if (!isset($grouped_news[$tag->name])) { $grouped_news[$tag->name] = array();}
        					// array_push($grouped_news[$tag->name],$items[$i]);
        				// }
        			// }
        		// }
        	// }
        // }


        // format and send email

        $subject = $config->email_title_reminder." - $title";
        $from_addr = $config->email_address_reminder;
        $headers = array(
        	"From"=>$from = $config->email_from_reminder,
        	"Reply-to"=>$reply = $config->email_reply_reminder
        	);

        // set up the html message
        $template->assign("showdetail",$config->email_showdetail);
        $htmlmsg = $template->render();

        // now the same thing for the text message
        $msg = chop(strip_tags(str_replace(array("<br />","<br>","br/>"),"\n",$htmlmsg)));

        // Saved.  do notifs
        $notifs = unserialize($config->reminder_notify);
        $emails = array();
        foreach ($db->selectObjects('calendar_reminder_address',"calendar_id='".$config->id."'") as $c) {
        	if ($c->user_id != 0) {
        		$u = user::getUserById($c->user_id);
        		$emails[] = $u->email;
        	} else if ($c->group_id != 0) {
        		$grpusers = group::getUsersInGroup($c->group_id);
        		foreach ($grpusers as $u) {
        			$emails[] = $u->email;
        		}
        	} else if ($c->email != '') {
        		$emails[] = $c->email;
        	}
        }
        if (empty($emails)) {
        	print_r("<br><strong><em>Exponent - ".gt('No One to Send Reminders to!')."</em></strong><br>");
        	exit();
        }

        $emails = array_flip(array_flip($emails));
        $emails = array_map('trim', $emails);
        $headers = array(
        	"MIME-Version"=>"1.0",
        	"Content-type"=>"text/html; charset=".LANG_CHARSET
        );
        $mail = new expMail();
        $mail->quickSend(array(
        		'headers'=>$headers,
        		'html_message'=>$htmlmsg,
        		"text_message"=>$msg,
        		'to'=>$emails,
        		'from'=>array(trim($config->email_address_reminder)=>$config->email_from_reminder),
        		'subject'=>$subject,
        ));

        print_r("<p>".gt('The following reminder was sent via email').":</p><br>");
        print_r($htmlmsg);
    }

    function getEventsForDates($edates, $sort_asc = true, $featuredonly = false) {
        global $db;
        $events     = array();
        $featuresql = "";
        if ($featuredonly) $featuresql = " AND is_featured=1";
        foreach ($edates as $edate) {
            $o = $db->selectObject("event", "id=" . $edate->event_id . $featuresql);
            if ($o != null) {
                $o->eventdate = $edate;
                $o->eventstart += $edate->date;
                $o->eventend += $edate->date;
                $events[] = $o;
            }
        }
        $events = expSorter::sort(array('array'=> $events, 'sortby'=> 'eventstart', 'order'=> $sort_asc ? 'ASC' : 'DESC'));
        return $events;
    }

    function getExternalEvents($loc, $startdate, $enddate) {  //FIXME this still needs to be converted
        global $db;

        $extevents = array();
        $dy        = 0;
        $url = 0;
//        $config    = $db->selectObject("calendarmodule_config", "location_data='" . serialize($loc) . "'");
//        if (!empty($config)) foreach ($db->selectObjects('calendar_external', "calendar_id='" . $config->id . "'") as $extcal) {
//            if ($extcal->type == GOOGLE_TYPE) {
            if (!empty($this->config['pull_gcal'])) foreach ($this->config['pull_gcal'] as $extgcalurl) {
                $url++;
                if (!empty($startdate)) $begin = date("Y-m-d\Th:i:sP", expDateTime::startOfDayTimestamp($startdate));
                if (!empty($enddate)) $end = date("Y-m-d\Th:i:sP", expDateTime::endOfDayTimestamp($enddate));

                if (substr($extgcalurl, -5) == 'basic') {
                    $extgcalurl = substr($extgcalurl, 0, strlen($extgcalurl) - 5) . 'full';
                }
                $feed = $extgcalurl . "?orderby=starttime&singleevents=true";
                if (!empty($startdate)) $feed .= "&start-min=" . $begin;
                if (!empty($enddate)) $feed .= "&start-max=" . $end;

                // XML method
                //                $s = simplexml_load_file($feed);
                //               	foreach ($s->entry as $item) {
                //               		$gd = $item->children('http://schemas.google.com/g/2005');
                //                    if (!empty($gd->when)) {
                //                       $dtstart = $gd->when->attributes()->startTime;
                //                    } elseif (!empty($gd->recurrence)){
                //                       $dtstart = $gd->recurrence->when->attributes()->startTime;
                //                    } else {
                //                        $dtstart = $item->attributes()->When;
                //                    }
                //                    //FIXME must convert $dtstart timezone
                //                    $eventdate = expDateTime::startOfDayTimestamp(strtotime($dtstart));
                //                    $extevents[$eventdate][$dy] = new stdClass();
                //                    $extevents[$eventdate][$dy]->eventdate = $eventdate;
                //                    $extevents[$eventdate][$dy]->eventstart += strtotime($dtstart);
                //                    if (!empty($gd->when)) {
                //                        $dtend = $gd->when->attributes()->endTime;
                //                    } elseif (!empty($gd->recurrence)) {
                //                        $dtend = $gd->recurrence->when->attributes()->endTime;
                //                    }
                //                    //FIXME must convert $dtend timezone
                //                    if (!empty($dtend)) $extevents[$eventdate][$dy]->eventend += strtotime($dtend);
                //                    // dtstart required, one occurrence, (orig. start date)
                //                    $extevents[$eventdate][$dy]->title = $item->title;
                //                    $extevents[$eventdate][$dy]->body = $item->content;
                // End XML method

                // DOM method
                $doc = new DOMDocument();
                $doc->load($feed);
                $entries = $doc->getElementsByTagName("entry");
                foreach ($entries as $item) {
                    $times   = $item->getElementsByTagName("when");
                    $dtstart = $times->item(0)->getAttributeNode("startTime")->value;
                    //                  //FIXME must convert $dtstart & $dtend timezone
                    $eventdate                             = expDateTime::startOfDayTimestamp(strtotime($dtstart));
                    $extevents[$eventdate][$dy]            = new stdClass();
                    $extevents[$eventdate][$dy]->eventdate = $eventdate;
                    $dtend                                 = $times->item(0)->getAttributeNode("endTime")->value;
                    if (strlen($dtstart) > 10) {
                        $extevents[$eventdate][$dy]->eventstart = (intval(substr($dtstart, 11, 2)) * 3600) + (intval(substr($dtstart, 14, 2)) * 60);
                        if (date("I", $eventdate)) $extevents[$eventdate][$dy]->eventstart += 3600;
                        $extevents[$eventdate][$dy]->eventend = (intval(substr($dtend, 11, 2)) * 3600) + (intval(substr($dtend, 14, 2)) * 60);
                        if (date("I", $eventdate)) $extevents[$eventdate][$dy]->eventend += 3600;
                    } else {
                        $extevents[$eventdate][$dy]->eventstart = null;
                        $extevents[$eventdate][$dy]->is_allday  = 1;
                    }
                    $titles                            = $item->getElementsByTagName("title");
                    $extevents[$eventdate][$dy]->title = $titles->item(0)->nodeValue;
                    $contents                          = $item->getElementsByTagName("content");
                    $extevents[$eventdate][$dy]->body  = $contents->item(0)->nodeValue;
                    // End DOM method

//                    $extevents[$eventdate][$dy]->location_data = serialize(expCore::makeLocation('extevent',$extcal->id));
                    $extevents[$eventdate][$dy]->location_data = 'gcalevent'.$url;
                    $dy++;
                }
            }
//            else if ($extcal->type == ICAL_TYPE) {
        if (!empty($this->config['pull_ical'])) foreach ($this->config['pull_ical'] as $exticalurl) {
            $url++;
                require_once BASE . 'external/iCalcreator.class.php';
                $v = new vcalendar(); // initiate new CALENDAR
                $v->setConfig('url', $exticalurl);
                $v->parse();
                if ($enddate == null) {
                    $startYear  = null;
                    $startMonth = null;
                    $startDay   = null;
                } else {
                    $startYear  = date('Y', $startdate);
                    $startMonth = date('n', $startdate);
                    $startDay   = date('j', $startdate);
                }
                if ($enddate == null) {
                    $endYear  = null;
                    $endMonth = null;
                    $endDay   = null;
                } else {
                    $endYear  = date('Y', $enddate);
                    $endMonth = date('n', $enddate);
                    $endDay   = date('j', $enddate);
                }
                $eventArray = $v->selectComponents($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, 'vevent');
                if (!empty($eventArray)) foreach ($eventArray as $year => $yearArray) {
                    if (!empty($yearArray)) foreach ($yearArray as $month => $monthArray) {
                        if (!empty($monthArray)) foreach ($monthArray as $day => $dailyEventsArray) {
                            if (!empty($dailyEventsArray)) foreach ($dailyEventsArray as $vevent) {
                                $yesterday = false;
                                $currddate = $vevent->getProperty('x-current-dtstart');
                                $thisday = explode('-',$currddate[1]);
                                // if member of a recurrence set,
                                // returns array( 'x-current-dtstart', <DATE>)
                                // <DATE> = (string) date("Y-m-d [H:i:s][timezone/UTC offset]")
                                $dtstart = $vevent->getProperty('dtstart',false,true);
                                //FIXME must convert $dtstart['TZID'] $dtstart['value']['tz']timezone
                                $dtend = $vevent->getProperty('dtend',false,true);
                                //FIXME must convert $dtend['TZID'] $dtend['value']['tz']timezone
                                $eventdate                             = expDateTime::startOfDayTimestamp(iCalUtilityFunctions::_date2timestamp($dtstart['value']));
                                $extevents[$eventdate][$dy]            = new stdClass();
                                $extevents[$eventdate][$dy]->eventdate = $eventdate;
                                if (!empty($dtstart['value']['hour'])) {
                                    $extevents[$eventdate][$dy]->eventstart = ($dtstart['value']['hour'] * 3600) + ($dtstart['value']['min'] * 60);
                                    if (date("I", $eventdate)) $extevents[$eventdate][$dy]->eventstart += 3600;
                                } else {
                                    // check for all day event
                                    if ($dtstart['value']['day'] != $thisday[2]) $yesterday = true;
                                    $extevents[$eventdate][$dy]->eventstart = null;
                                    $extevents[$eventdate][$dy]->is_allday  = 1;
                                }
                                if (!empty($dtend['value']['hour'])) {
                                    $extevents[$eventdate][$dy]->eventend = ($dtend['value']['hour'] * 3600) + ($dtend['value']['min'] * 60);
                                    if (date("I", $eventdate)) $extevents[$eventdate][$dy]->eventend += 3600;
                                }
                                // dtstart required, one occurrence, (orig. start date)
                                $extevents[$eventdate][$dy]->title = $vevent->getProperty('summary');
                                $extevents[$eventdate][$dy]->body  = $vevent->getProperty('description');

//                                $extevents[$eventdate][$dy]->location_data = serialize(expCore::makeLocation('extevent',$extcal->id));
                                $extevents[$eventdate][$dy]->location_data = 'icalevent'.$url;
                                if (!$yesterday) {
                                    $dy++;
                                } else {
                                    unset($extevents[$eventdate][$dy]);
                                }
                            }
                        }
                    }
                }
            }
//        }
        return $extevents;
    }

}

?>