FROM registry.cn-hangzhou.aliyuncs.com/litosrc/php:7.4-apache
WORKDIR /var/www/html
ADD --chown=www-data:www-data . /var/www/html
ADD php.ini /usr/local/etc/php/php.ini