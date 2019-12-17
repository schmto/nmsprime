# source environment variables to use php 7.1
source scl_source enable rh-php71

dir="/var/www/nmsprime"

cd "$dir"
/opt/rh/rh-php71/root/usr/bin/php artisan config:cache
/opt/rh/rh-php71/root/usr/bin/php artisan clear-compiled
/opt/rh/rh-php71/root/usr/bin/php artisan optimize
/opt/rh/rh-php71/root/usr/bin/php artisan migrate
#/opt/rh/rh-php71/root/usr/bin/php artisan queue:restart
pkill -f "artisan queue:work"
/opt/rh/rh-php71/root/usr/bin/php artisan auth:nms
/opt/rh/rh-php71/root/usr/bin/php artisan route:cache
/opt/rh/rh-php71/root/usr/bin/php artisan view:clear

systemctl reload httpd

rm -f storage/framework/sessions/*
chown -R apache storage bootstrap/cache /var/log/nmsprime
systemctl restart nmsprimed
systemd-tmpfiles --create
