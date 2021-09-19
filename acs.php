<?php

/* Copyright (C) 2014-2021 Bontiv <prog.bontiv@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

define('NOLOGIN', true);
define('NOCSRFCHECK', true);

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include(substr($tmp, 0, ($i + 1)) . "/main.inc.php");
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php");
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php")) $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php")) $res = @include("../../../main.inc.php");
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once 'lib/autoload.php';

/** @var \OneLogin\Saml2\Auth $login */
$login = get_saml();

$login->processResponse();

if ($login->isAuthenticated()) {
    $user = new User($db);
    $admin = new User($db);
    $admin->fetch('', 'admin');

    $res = $user->fetch('', $login->getNameId());
    $user->firstname = $login->getAttribute('givenName')[0];
    $user->lastname = $login->getAttribute('sn')[0];
    $user->admin = in_array('ADMINISTRATOR', $login->getAttribute('type')) ? 1 : 0;
    $user->email = $login->getAttribute('mail')[0];

    if ($res <= 0) {
        $user->login = $login->getNameId();
        $user->create($admin);

    } else {
        $user->update($admin);
    }


    $_SESSION["dol_login"]=$user->login;
    $_SESSION["dol_authmode"]='saml';
    $_SESSION["dol_tz"]=isset($dol_tz)?$dol_tz:'';
    $_SESSION["dol_tz_string"]=isset($dol_tz_string)?$dol_tz_string:'';
    $_SESSION["dol_dst"]=isset($dol_dst)?$dol_dst:'';
    $_SESSION["dol_dst_observed"]=isset($dol_dst_observed)?$dol_dst_observed:'';
    $_SESSION["dol_dst_first"]=isset($dol_dst_first)?$dol_dst_first:'';
    $_SESSION["dol_dst_second"]=isset($dol_dst_second)?$dol_dst_second:'';
    $_SESSION["dol_screenwidth"]=isset($dol_screenwidth)?$dol_screenwidth:'';
    $_SESSION["dol_screenheight"]=isset($dol_screenheight)?$dol_screenheight:'';
    $_SESSION["dol_company"]=$conf->global->MAIN_INFO_SOCIETE_NOM;
    $_SESSION["dol_entity"]=$conf->entity;
    // Store value into session (values stored only if defined)
    if (! empty($dol_hide_topmenu))         $_SESSION['dol_hide_topmenu']=$dol_hide_topmenu;
    if (! empty($dol_hide_leftmenu))        $_SESSION['dol_hide_leftmenu']=$dol_hide_leftmenu;
    if (! empty($dol_optimize_smallscreen)) $_SESSION['dol_optimize_smallscreen']=$dol_optimize_smallscreen;
    if (! empty($dol_no_mouse_hover))       $_SESSION['dol_no_mouse_hover']=$dol_no_mouse_hover;
    if (! empty($dol_use_jmobile))          $_SESSION['dol_use_jmobile']=$dol_use_jmobile;

    dol_syslog("This is a new started user session. _SESSION['dol_login']=".$_SESSION["dol_login"]." Session id=".session_id());

    $db->begin();
    $user->update_last_login_date();

    $loginfo = 'TZ='.$_SESSION["dol_tz"].';TZString='.$_SESSION["dol_tz_string"].';Screen='.$_SESSION["dol_screenwidth"].'x'.$_SESSION["dol_screenheight"];

    // Call triggers for the "security events" log
    $user->trigger_mesg = $loginfo;
    // Call triggers
    include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
    $interface=new Interfaces($db);
    $result=$interface->run_triggers('USER_LOGIN',$user,$user,$langs,$conf);

    $hookmanager->initHooks(array('login'));
    $parameters=array('dol_authmode'=>'saml', 'dol_loginfo'=>$loginfo);
    $reshook=$hookmanager->executeHooks('afterLogin',$parameters,$user,$action);    // Note that $action and $object may have been modified by some hooks

    $db->commit();
    header('Location: ' . DOL_URL_ROOT);
}

if (isset($_REQUEST['RelayState'])) {
    $login->redirectTo($_REQUEST['RelayState']);
}
?>
<a href="login.php">Login</a>
<a href="logout.php">Logout</a>
