# SAML FOR <a href="https://www.dolibarr.org">DOLIBARR ERP CRM</a>

## Features
Use SAML v2 to login into Dolibarr.

EXPERIMENTAL VERSION

/!\ Do not use in production!

## Configuration

Change SAML settings in the following file:
lib/saml_settigns.php

Configuration will use Dolibarr admin panel in production version.

Be carefull. This module force SAML authentification in Dolibarr. You will not be able to use "standard" authentication system. The variable $dolibarr_main_authentication do not affect this module.


Licenses
--------

### Main code

![GPLv3 logo](img/gplv3.png)

GPLv3 or (at your option) any later version.

See file COPYING for more information.

#### Documentation

All texts and readmes.

![GFDL logo](img/gfdl.png)
