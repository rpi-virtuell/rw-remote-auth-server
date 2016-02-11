=== RW Remote Auth Server ===
Contributors: f.staude
Tags:
Requires at least: 4.0
Tested up to: 4.4.2
Stable tag: 0.1.9
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html



== Description ==


== Hooks ==

= Filter =

rw_remote_auth_server_translationpath


= Actions =

rw_remote_auth_server_init

rw_remote_auth_server_autoload_register

rw_remote_auth_server_autoload_unregister

== Defines ==

Possible defines in wp_config.php

RW_REMOTE_AUTH_SERVER_API_ENDPOINT



== Installation ==


== Frequently Asked Questions ==

= Question =

Answer


== Screenshots ==


== Changelog ==

= 0.1.9 =
* fix backlink after password change on last screen

= 0.1.8 =
* fix backlink after password change

= 0.1.7 =
* change backlink js

= 0.1.6 =
* remove home_url filter for WordPress 4.4 compability, use js instead

= 0.1.5 =
* added message on login screen at reauth=1

= 0.1.4 =
* login screen, change register link to cas client
* login screen, change back link to cas client
* login screen, change lostpassword link to cas client
* lost password screen, change login link, added cas info
* reset password mail, change url, added cas info
* reset password mail, change server name, added cas client
* password change screen, added redirect_to url for cas client

= 0.1.3 =
* Added ping command ( #3 )
* Added selftest ( #2 )

= 0.1.2 =
* added check if user exists

= 0.1.1 =
* Added support for WordPress Plugin GitHub updater ( #1 )

= 0.1 =
* First version published


== Credits ==




