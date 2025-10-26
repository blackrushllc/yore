<?php
/**
 *
 * Something I liked in Omar's Hub.  Whatever happened to that guy?
 *
 * From https://github.com/olsonhost/Hub/tree/master
 *
 * Example with regex:
 *
 * '@/index/(?P<name>[a-zA-Z]+)/(?P<age>\d+)/(?P<country>[a-zA-Z]+)@' => 'Index\Pages\Index'
 *
 * BACKEND PREFIX: sys
 *
 */
return array (

    /* MAIN */
    '/exc'        => 'Error\Pages\Exc',
    '/404'        => 'Error\Pages\Index',
    '/error'      => 'Error\Pages\Error',
    '/'	          => 'Index\Pages\Index',
    '/index'      => 'Index\Pages\Index',
    '/login'       => 'Access\Pages\Login',
    '/logout'       => 'Access\Pages\Logout',

    /* TXAPI PAGES */
    '/tx' => 'Tx\Pages\Index',
    '/tx/index' => 'Tx\Pages\Index',
    '@/tx/credentials/get(.+)?@' => 'Tx\Pages\Index',
    '@/tx/credentials/add(.+)?@' => 'Tx\Pages\Index',
    '@/tx/update(.+)?@'    => 'Tx\Pages\Update',
    '@/tx/begin(.+)?@'     => 'Tx\Pages\Begin',
    '@/tx/clean(.+)?@'     => 'Tx\Pages\Clean',
    '@/tx/download(.+)?@'  => 'Tx\Pages\Download',
    '@/tx/init(.+)?@'      => 'Tx\Pages\Init',
    '/tx/end'      => 'Tx\Pages\End',
    '/tx/purge'     => 'Tx\Pages\Purge',
    '@/tx/push(.+)?@'      => 'Tx\Pages\Push',
    '@/tx/sendsms(.+)?@'=> 'Tx\Pages\Sendsms',
    '@/tx/launch(.+)?@'    => 'Tx\Pages\Launch',
    '@/tx/log(.+)?@'    => 'Tx\Pages\Log',
    '@/tx/build(.+)?@'    => 'Tx\Pages\Build',
    /* SECURE PAGES */
    '/sys/home'       => 'Admin\Pages\Index',
);