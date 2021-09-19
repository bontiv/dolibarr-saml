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

/**
 *	\file       htdocs/saml/template/login.php
 *	\ingroup    saml
 *	\brief      Home page of saml top menu
 */

define('NOLOGIN', true);
define('NOCSRFCHECK', true);

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once 'lib/autoload.php';

/** @var \OneLogin\Saml2\Auth $login */
$login = get_saml();

$newpath = DOL_MAIN_URL_ROOT . '/index.php?mainmenu=home&leftmenu=home';
$landingpage=(empty($user->conf->MAIN_LANDING_PAGE)?(empty($conf->global->MAIN_LANDING_PAGE)?'':$conf->global->MAIN_LANDING_PAGE):$user->conf->MAIN_LANDING_PAGE);
if (! empty($landingpage))    // Example: /index.php
{
    $newpath=dol_buildpath($landingpage, 1);
}

$login->login($newpath);

die;
// Load translation files required by the page
$langs->loadLangs(array("saml@saml"));

$action=GETPOST('action', 'alpha');


// Securite acces client
if (! $user->rights->saml->read) accessforbidden();
$socid=GETPOST('socid','int');
if (isset($user->societe_id) && $user->societe_id > 0)
{
	$action = '';
	$socid = $user->societe_id;
}

$max=5;
$now=dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("",$langs->trans("SAMLArea"));

print load_fiche_titre($langs->trans("SAMLArea"),'','saml.png@saml');

print '<div class="fichecenter"><div class="fichethirdleft">';




print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$NBMAX=3;
$max=3;

print '</div></div></div>';

llxFooter();

$db->close();
