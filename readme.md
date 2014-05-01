This PHP script will allow you to use your rackspace cloud account as a replacement for the now discontinued free DynDNS domain service.

To have the script run every 5 minutes just add the following to your crontab:

*/5 * * * * php /home/*user*/dyndns/dnsupdate.php

The script checks your server's current ip with icanhazip.com and stores your ip in a txt file which it compares on every run. If the ip has changed it will send a DNS modification request to rackspace's DNS API.