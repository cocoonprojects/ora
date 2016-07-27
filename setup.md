cron for notifications


*/15 *  *  *  * centos  /usr/bin/php /var/www/vhosts/cocoon/public/index.php closepolls idea-items >> /tmp/cocoon-cron-idea-items.log  2>&1
*/15 *  *  *  * centos  /usr/bin/php /var/www/vhosts/cocoon/public/index.php closepolls completed-items >> /tmp/cocoon-cron-completed-items.log  2>&1
5    0  *  *  * centos  /usr/bin/php /var/www/vhosts/cocoon/public/index.php reminder >> /tmp/cocoon-cron-remider.log 2>&1
