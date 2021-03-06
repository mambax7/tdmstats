<?php
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright     {@link https://xoops.org/ XOOPS Project}
 * @license       {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package       tdmstats
 * @since
 * @author        TDM   - TEAM DEV MODULE FOR XOOPS
 * @author        XOOPS Development Team
 */

use XoopsModules\Tdmstats;

$GLOBALS['xoopsOption']['template_main'] = 'tdmstats_stats.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

$helper = Tdmstats\Helper::getInstance();

if (!$grouppermHandler->checkRight('istats_view', 8, $groups, $xoopsModule->getVar('mid'))) {
    redirect_header(XOOPS_URL, 1, _AM_QUERYNOPERM);
}

//utile
//strftime( "%H H %M mn %S s", 82.5 * 60 ) => '02 H 22 mn 30 s'
//

setlocale(LC_ALL, $helper->getConfig('setlocal'), $helper->getConfig('setlocal2'));
$thisday    = date('d');
$thismonth  = date('m');
$thisyear   = date('Y');
$thisnumday = date('w');

$xoopsTpl->assign('lang_by_weekday', _AM_BY_WEEKDAY);
$xoopsTpl->assign('lang_by_week', _AM_BY_WEEK);
$xoopsTpl->assign('lang_by_mth', _AM_BY_MTH);
$xoopsTpl->assign('lang_by_hour', _AM_BY_HOUR);
$xoopsTpl->assign('lang_by_page', _AM_BY_PAGE);
$xoopsTpl->assign('lang_by_day', _AM_BY_DAY);
$xoopsTpl->assign('lang_date_date', _AM_DATE_DATE);
$xoopsTpl->assign('lang_date_visits', _AM_DATE_VISITS);
$xoopsTpl->assign('lang_date_percent', _AM_DATE_PERCENT);

$month     = [
    '01' => '31',
    '02' => '28',
    '03' => '31',
    '04' => '30',
    '05' => '31',
    '06' => '30',
    '07' => '31',
    '08' => '31',
    '09' => '30',
    '10' => '31',
    '11' => '30',
    '12' => '31',
];
$this_mths = date('m');
$mths      = $month[$this_mths];
$leap      = date('L');
if ('2' == $this_mths && $leap > 0) {
    ++$mths;
}

////info day
$day = [];
global $xoopsDB;
//$mth = formatTimeStamp(time(), 'Y-m-');
$day_info = getResult('SELECT DISTINCT day, count FROM ' . $xoopsDB->prefix('tdmstats_mth_days') . ' ORDER BY count DESC LIMIT 3');

if ($day_info) {
    for ($i = 0, $iMax = count($day_info); $i < $iMax; ++$i) {
        $day['info'][] = $day_info[$i]['count'];
        $day['day'][]  = $day_info[$i]['day'];

        $xoopsTpl->append('item_days', ['id' => 'day' . $i, 'day' => $day_info[$i]['day'], 'info' => $day_info[$i]['count']]);
    }
}
///day
$day = [];
global $xoopsDB;
$mth       = formatTimestamp(time(), 'Y-m-');
$day_info  = getResult('select distinct day, count from ' . $xoopsDB->prefix('tdmstats_mth_days') . " order by day limit $mths");
$day_total = getResult('SELECT SUM(count) AS sum FROM ' . $xoopsDB->prefix('tdmstats_mth_days') . '');
// $day_max = getResult("select max(count) as max from ".$xoopsDB->prefix("tdmstats_mth_days")." order by day");

for ($i = 0; $i < $mths; ++$i) {
    if ($day_total[0]['sum'] > 0) {
        $day_percent = $day_info[$i]['count'] * 100 / $day_total[0]['sum'];
    } else {
        $day_percent = 0;
    }

    $day['info'][]    = $day_info[$i]['count'];
    $day['day'][]     = $day_info[$i]['day'];
    $day['percent'][] = round($day_percent, '2');

    if ($day_percent > 0) {
        //$xoopsTpl->append('days', array('id' => 'day'.$i, 'day' => $day_info[$i]['day'], 'info' => $day_info[$i]['count'], 'percent' => round($day_percent, '2')));
        $xoopsTpl->append(
            'days_map',
            [
                'id'      => 'day' . $i,
                'day'     => $day_info[$i]['day'],
                'info'    => $day_info[$i]['count'],
                'percent' => round($day_percent, '2'),
            ]
        );
    }
}

///week info
$week     = [
    0 => _AM_WD_7,
    1 => _AM_WD_1,
    2 => _AM_WD_2,
    3 => _AM_WD_3,
    4 => _AM_WD_4,
    5 => _AM_WD_5,
    6 => _AM_WD_6,
];
$week_day = [];
global $xoopsDB;
$week_info = getResult('SELECT * FROM ' . $xoopsDB->prefix('tdmstats_week') . ' ORDER BY count DESC LIMIT 3');
//$week_sum = getResult("select SUM(count) AS sum from ".$xoopsDB->prefix("tdmstats_week")."");

if ($week_info) {
    for ($i = 0, $iMax = count($week_info); $i < $iMax; ++$i) {
        //$week_day = $week[$week_info[$i]['day']];
        $day = strftime('%A', mktime(0, 0, 0, 1, $week_info[$i]['day'], 1973));

        $xoopsTpl->append('item_weeks', ['id' => 'week_day' . $i, 'week_day' => $day, 'info' => $week_info[$i]['count']]);
    }
}

///////////WEEK////////////
$week     = [
    0 => _AM_WD_7,
    1 => _AM_WD_1,
    2 => _AM_WD_2,
    3 => _AM_WD_3,
    4 => _AM_WD_4,
    5 => _AM_WD_5,
    6 => _AM_WD_6,
];
$week_day = [];
global $xoopsDB;
$week_info = getResult('SELECT * FROM ' . $xoopsDB->prefix('tdmstats_week') . ' ORDER BY day');
$week_sum  = getResult('SELECT SUM(count) AS sum FROM ' . $xoopsDB->prefix('tdmstats_week') . '');

if ($week_info) {
    for ($i = 0, $iMax = count($week_info); $i < $iMax; ++$i) {
        if ($week_sum[0]['sum'] > 0) {
            $week_day_percent = $week_info[$i]['count'] * 100 / $week_sum[0]['sum'];
        } else {
            $week_day_percent = 0;
        }
        $day = strftime('%A', mktime(0, 0, 0, 1, $week_info[$i]['day'], 1973));
        //$week_day['info'][] = $week_info[$i]['count'];
        //$week_day['week_day'][] = $week_day[$i]['day'];
        //$week_day['percent'][] = round($week_day_percent, '2');

        if ($week_day_percent > 0) {
            //$xoopsTpl->append('week_days', array('id' => 'week_day'.$i, 'week_day' => $week[$i], 'info' => $week_info[$i]['count'], 'percent' => round($week_day_percent, '2')));
            $xoopsTpl->append(
                'week_days_map',
                [
                    'id'       => 'week_day' . $i,
                    'week_day' => $day,
                    'info'     => $week_info[$i]['count'],
                    'percent'  => round($week_day_percent, '2'),
                ]
            );
        }
    }
}

///mont info///
global $xoopsDB;
$year     = formatTimestamp(time(), 'Y');
$mth_info = getResult('select distinct mth, year, count from ' . $xoopsDB->prefix('tdmstats_mth') . " where year='$year' order by count DESC limit 3");

// $mth_result = PrintStats($mth_sum[0]['sum'], $mth_max[0]['max'], $mth_info, count($mth_info));
if ($mth_info) {
    for ($i = 0, $iMax = count($mth_info); $i < $iMax; ++$i) {
        $mth = strftime('%B %Y', mktime(0, 0, 0, $mth_info[$i]['mth'], 01, $mth_info[$i]['year']));
        //$mth['percent'][] = round($mth_percent, '2');

        $xoopsTpl->append('item_mths', ['id' => 'mth' . $i, 'mth' => $mth, 'info' => $mth_info[$i]['count']]);
    }
}
///mont///

$mth = [];
global $xoopsDB;
$year     = formatTimestamp(time(), 'Y');
$mth_info = getResult('select distinct mth, year, count from ' . $xoopsDB->prefix('tdmstats_mth') . " where year='$year' order by id desc");
//$mth_max = getResult("select max(count) as max from ".$xoopsDB->prefix("tdmstats_mth")." where year='$year'");
$mth_sum = getREsult('select sum(count) as sum from ' . $xoopsDB->prefix('tdmstats_mth') . " where year='$year'");

// $mth_result = PrintStats($mth_sum[0]['sum'], $mth_max[0]['max'], $mth_info, count($mth_info));
if ($mth_info) {
    for ($i = 0, $iMax = count($mth_info); $i < $iMax; ++$i) {
        if ($mth_sum[0]['sum'] > 0) {
            $mth_percent = $mth_info[$i]['count'] * 100 / $mth_sum[0]['sum'];
        } else {
            $mth_percent = 0;
        }
        //echo "nous sommes le". strftime ("%A %d %B %Y et il est %Hh%M", 1207742661);
        //$week = strftime("%B %Y", mktime(0, 0, 0,$mth_info[$i]['mth'] ,01 ,$mth_info[$i]['year']));
        $mth = strftime('%B %Y', mktime(0, 0, 0, $mth_info[$i]['mth'], 01, $mth_info[$i]['year']));
        //$mth['info'][] = $mth_info[$i]['count'];
        //$mth['week'][] = $mth_info[$i]['week'];
        //$mth['percent'][] = round($mth_percent, '2');

        if ($mth_percent > 0) {
            //$xoopsTpl->append('mths', array('id' => 'mth'.$i, 'mth' => $mth_info[$i]['mth'], 'year' => $mth_info[$i]['year'], 'info' => $mth_info[$i]['count'], 'percent' => round($mth_percent, '2')));
            $xoopsTpl->append(
                'mths_map',
                [
                    'id'      => 'mth' . $i,
                    'mth'     => $mth,
                    'year'    => $mth_info[$i]['year'],
                    'info'    => $mth_info[$i]['count'],
                    'percent' => round($mth_percent, '2'),
                ]
            );
        }
    }
}
///week info
global $xoopsDB;
$last_info = getResult('select distinct week, year, count from ' . $xoopsDB->prefix('tdmstats_week_count') . " where year='$year' order by count desc limit 3");
//$week_max = getResult("select max(count) as max from ".$xoopsDB->prefix("tdmstats_week_count")." where year='$year'");
//$last_sum = getResult("select sum(count) as sum from ".$xoopsDB->prefix("tdmstats_week_count")." where year='$year'");
//$week_result = PrintStats($week_sum[0]['sum'], $week_max[0]['max'], $week_info, count($week_info));

if ($last_info) {
    for ($i = 0, $iMax = count($last_info); $i < $iMax; ++$i) {
        //$last['info'][] = $last_info[$i]['count'];
        //$last['week'][] = $last_info[$i]['week'];
        //$last['percent'][] = round($last_percent, '2');

        $xoopsTpl->append(
            'item_lasts',
            [
                'id'   => 'last' . $i,
                'week' => $last_info[$i]['week'],
                'year' => $last_info[$i]['year'],
                'info' => $last_info[$i]['count'],
            ]
        );
    }
}
//////WEEK/////////////
$last = [];
global $xoopsDB;
//$year = formatTimeStamp(time(), 'Y');
$last_info = getResult('select distinct week, year, count from ' . $xoopsDB->prefix('tdmstats_week_count') . " where year='$year' order by id desc");
//$week_max = getResult("select max(count) as max from ".$xoopsDB->prefix("tdmstats_week_count")." where year='$year'");
$last_sum = getResult('select sum(count) as sum from ' . $xoopsDB->prefix('tdmstats_week_count') . " where year='$year'");
//$week_result = PrintStats($week_sum[0]['sum'], $week_max[0]['max'], $week_info, count($week_info));

if ($last_info) {
    for ($i = 0, $iMax = count($last_info); $i < $iMax; ++$i) {
        if ($last_sum[0]['sum'] > 0) {
            $last_percent = $last_info[$i]['count'] * 100 / $last_sum[0]['sum'];
        } else {
            $last_percent = 0;
        }

        $last['info'][]    = $last_info[$i]['count'];
        $last['week'][]    = $last_info[$i]['week'];
        $last['percent'][] = round($last_percent, '2');

        if ($last_percent > 0) {
            $xoopsTpl->append(
                'lasts',
                [
                    'id'      => 'last' . $i,
                    'week'    => $last_info[$i]['week'],
                    'year'    => $last_info[$i]['year'],
                    'info'    => $last_info[$i]['count'],
                    'percent' => round($last_percent, '2'),
                ]
            );
            $xoopsTpl->append(
                'lasts_map',
                [
                    'id'      => 'last' . $i,
                    'week'    => $last_info[$i]['week'],
                    'year'    => $last_info[$i]['year'],
                    'info'    => $last_info[$i]['count'],
                    'percent' => round($last_percent, '2'),
                ]
            );
        }
    }
}

$xoopsTpl->assign('lang_mth_mth', _AM_MTH_VISITS);

/////////////ITEM HOUR
global $xoopsDB;
$hour_info = getResult('SELECT * FROM ' . $xoopsDB->prefix('tdmstats_hour') . ' ORDER BY count DESC LIMIT 3');
//$max_hour   = getResult("select max(count) as max from ".$xoopsDB->prefix("tdmstats_hour")."");
if ($hour_info) {
    for ($i = 0, $iMax = count($hour_info); $i < $iMax; ++$i) {
        //$hour['info'][] = $hour_info[$i]['count'];
        //$hour['week'][] = $hour_info[$i]['hour'];
        //$hour['percent'][] = round($hour_percent, '2');

        $xoopsTpl->append(
            'item_hours',
            [
                'id'   => 'hour' . $i,
                'hour' => $hour_info[$i]['hour'],
                'info' => $hour_info[$i]['count'],
            ]
        );
    }
}
/////////////HOUR
$hour = [];
global $xoopsDB;
$hour_info = getResult('SELECT * FROM ' . $xoopsDB->prefix('tdmstats_hour') . ' ORDER BY hour');
//$max_hour   = getResult("select max(count) as max from ".$xoopsDB->prefix("tdmstats_hour")."");
$hour_sum = getResult('SELECT sum(count) AS sum FROM ' . $xoopsDB->prefix('tdmstats_hour') . '');
if ($hour_info) {
    for ($i = 0, $iMax = count($hour_info); $i < $iMax; ++$i) {
        if ($hour_sum[0]['sum'] > 0) {
            $hour_percent = $hour_info[$i]['count'] * 100 / $hour_sum[0]['sum'];
        } else {
            $hour_percent = 0;
        }

        //$hour['info'][] = $hour_info[$i]['count'];
        //$hour['week'][] = $hour_info[$i]['hour'];
        //$hour['percent'][] = round($hour_percent, '2');

        if ($hour_percent > 0) {
            //$xoopsTpl->append('hours', array('id' => 'hour'.$i, 'hour' => $hour_info[$i]['hour'], 'info' => $hour_info[$i]['count'], 'percent' => round($hour_percent, '2')));
            $xoopsTpl->append(
                'hours_map',
                [
                    'id'      => 'hour' . $i,
                    'hour'    => $hour_info[$i]['hour'],
                    'info'    => $hour_info[$i]['count'],
                    'percent' => round($hour_percent, '2'),
                ]
            );
        }
    }
}

//page item
global $xoopsDB;
$page_info = getResult('SELECT DISTINCT page, count FROM ' . $xoopsDB->prefix('tdmstats_page') . ' ORDER BY count DESC LIMIT 3 ');

if ($page_info) {
    for ($i = 0, $iMax = count($page_info); $i < $iMax; ++$i) {
        //$page['info'][] = $page_info[$i]['count'];
        //$page['page'][] = (strlen(basename($page_info[$i]['page'])) > 50 ? substr(basename($page_info[$i]['page']),0,(50))."..." : basename($page_info[$i]['page']));
        $url = (mb_strlen(basename($page_info[$i]['page'])) > 20 ? mb_substr(basename($page_info[$i]['page']), 0, 20) . '...' : basename($page_info[$i]['page']));
        //$title = $page_info[$i]['page'];
        //$page['percent'][] = round($page_percent, '2');

        $xoopsTpl->append('item_pages', ['id' => 'page' . $i, 'page' => $url, 'info' => $page_info[$i]['count']]);
    }
}

/**
 * @feature
 * Displays Top xx page requests
 */
$page = [];
global $xoopsDB;
$max       = $helper->getConfig('maxpage');
$page_info = getResult('select distinct page, count from ' . $xoopsDB->prefix('tdmstats_page') . " order by count desc limit $max ");
//$page_max = getResult("select max(count) as max from ".$xoopsDB->prefix("tdmstats_page")."");
$page_sum = getResult('SELECT sum(count) AS sum FROM ' . $xoopsDB->prefix('tdmstats_page') . '');
//$page_result = PrintStats($page_sum[0]['sum'], $page_max[0]['max'], $page_info, count($page_info), 300);
if ($page_info) {
    for ($i = 0, $iMax = count($page_info); $i < $iMax; ++$i) {
        if ($page_sum[0]['sum'] > 0) {
            $page_percent = $page_info[$i]['count'] * 100 / $page_sum[0]['sum'];
        } else {
            $page_percent = 0;
        }

        //$page['info'][] = $page_info[$i]['count'];
        //$page['page'][] = (strlen(basename($page_info[$i]['page'])) > 50 ? substr(basename($page_info[$i]['page']),0,(50))."..." : basename($page_info[$i]['page']));
        $url               = (mb_strlen(basename($page_info[$i]['page'])) > 20 ? mb_substr(basename($page_info[$i]['page']), 0, 20) . '...' : basename($page_info[$i]['page']));
        $title             = $page_info[$i]['page'];
        $page['percent'][] = round($page_percent, '2');

        if ($page_percent > 0) {
            //$xoopsTpl->append('pages', array('id' => 'page'.$i, 'page' => $url, 'title' => $title, 'info' => $page_info[$i]['count'], 'percent' => round($page_percent, '2')));
            $xoopsTpl->append(
                'pages_map',
                [
                    'id'      => 'page' . $i,
                    'page'    => $url,
                    'title'   => $page_info[$i]['page'],
                    'info'    => $page_info[$i]['count'],
                    'percent' => round($page_percent, '2'),
                ]
            );
        }
    }
}

//item module
global $xoopsDB;

$module_info = getResult('SELECT DISTINCT modules, count FROM ' . $xoopsDB->prefix('tdmstats_modules') . ' ORDER BY count DESC LIMIT 3 ');

if ($module_info) {
    for ($i = 0, $iMax = count($module_info); $i < $iMax; ++$i) {
        //$module['info'][] = $module_info[$i]['count'];
        //$module['modules'][] = $module_info[$i]['modules'];
        //$module['percent'][] = round($module_percent, '2');

        $xoopsTpl->append(
            'item_modules',
            [
                'id'     => 'modules' . $i,
                'module' => $module_info[$i]['modules'],
                'info'   => $module_info[$i]['count'],
            ]
        );
    }
}
/**
 * @feature
 * Displays Top module requests
 */
/////////////modules
$module = [];
global $xoopsDB;
$max         = $helper->getConfig('maxpage');
$module_info = getResult('select distinct modules, count from ' . $xoopsDB->prefix('tdmstats_modules') . " order by count desc limit $max ");
$module_sum  = getResult('SELECT sum(count) AS sum FROM ' . $xoopsDB->prefix('tdmstats_page') . '');

if ($module_info) {
    for ($i = 0, $iMax = count($module_info); $i < $iMax; ++$i) {
        if ($module_sum[0]['sum'] > 0) {
            $module_percent = $module_info[$i]['count'] * 100 / $module_sum[0]['sum'];
        } else {
            $module_percent = 0;
        }

        $module['info'][]    = $module_info[$i]['count'];
        $module['modules'][] = $module_info[$i]['modules'];
        $module['percent'][] = round($module_percent, '2');

        if ($module_percent > 0) {
            $xoopsTpl->append(
                'modules',
                [
                    'id'      => 'modules' . $i,
                    'modules' => $module_info[$i]['modules'],
                    'info'    => $module_info[$i]['count'],
                    'percent' => round($module_percent, '2'),
                ]
            );
            $xoopsTpl->append(
                'modules_map',
                [
                    'id'      => 'modules' . $i,
                    'modules' => $module_info[$i]['modules'],
                    'info'    => $module_info[$i]['count'],
                    'percent' => round($module_percent, '2'),
                ]
            );
        }
    }
}
///USERCOUNT////////////////////////

//usercount item
$date        = formatTimestamp(time(), 'Y-m-d');
$date_before = date('Y-m-d', strtotime('-6 day'));
//netoyage
$xoopsDB->query('delete from ' . $xoopsDB->prefix('tdmstats_usercount') . " WHERE date < '$date_before'");

$user_info = getResult('SELECT *, SUM(count) AS sum FROM ' . $xoopsDB->prefix('tdmstats_usercount') . ' GROUP BY ip ORDER BY count DESC LIMIT 3');
//$total_hour = getResult("select SUM(count) AS sum from ".$xoopsDB->prefix("tdmstats_today_hour")."");

if ($user_info) {
    for ($i = 0, $iMax = count($user_info); $i < $iMax; ++$i) {
        if ($user_info[$i]['sum'] > 0) {
            $userid = !empty($user_info[$i]['userid']) ? \XoopsUser::getUnameFromId($user_info[$i]['userid']) : mb_substr($user_info[$i]['ip'], 0, 6) . '..';
            //$count = $user_info[$i]['count'] ;
            $count = gmstrftime('%H H %M mn %S s', $user_info[$i]['sum']);

            //$hour['hour'][] = $hour_info[$i]['hour'];
            //$hour['percent'][] = round($hour_percent, '2');

            $xoopsTpl->append('item_users', ['id' => 'hour' . $i, 'userid' => $userid, 'info' => $count]);
        }
    }
}

$user_info  = getResult('SELECT *, SUM(count) AS sum FROM ' . $xoopsDB->prefix('tdmstats_usercount') . ' GROUP BY ip ORDER BY count DESC');
$user_total = getResult('SELECT SUM(count) AS sum FROM ' . $xoopsDB->prefix('tdmstats_usercount') . '');

if ($user_info) {
    for ($i = 0, $iMax = count($user_info); $i < $iMax; ++$i) {
        if ($user_total[0]['sum'] > 0) {
            $user_percent = $user_info[$i]['sum'] * 100 / $user_total[0]['sum'];
            // 4*100/62,5 =6,4%
        } else {
            $user_percent = 0;
        }

        $userid = !empty($user_info[$i]['userid']) ? \XoopsUser::getUnameFromId($user_info[$i]['userid']) : mb_substr($user_info[$i]['ip'], 0, 6) . '..';
        $count  = gmstrftime('%H H %M mn %S s', $user_info[$i]['sum']);

        if ($user_percent > 0) {
            //$xoopsTpl->append('hours', array('id' => 'hour'.$i, 'hour' => $hour_info[$i]['hour'], 'info' => $hour_info[$i]['count'], 'percent' => round($hour_percent, '2')));
            $xoopsTpl->append(
                'users_map',
                [
                    'id'      => 'user' . $i,
                    'userid'  => $userid,
                    'info'    => $count,
                    'percent' => round($user_percent, '2'),
                ]
            );
        }
    }
}
////////////////////////////////
