<?php

namespace Deployer;

require 'vendor/jalogut/magento2-deployer-plus/recipe/magento_2_2.php';

// Use timestamp for release name
set('release_name', function () {
    return date('YmdHis');
});

// Magento dir into the project root. Set "." if magento is installed on project root
set('magento_dir', '.');
// [Optional] Git repository. Only needed if not using build + artifact strategy
set('repository', 'git@github.com:proxify-ab/snusme-magento2.git');
// Space separated list of languages for static-content:deploy
set('languages', 'de_CH  en_US  ru_RU');

set('shared_files', [
    '{{magento_dir}}/app/etc/env.php',
    '{{magento_dir}}/var/sitemap.xml',
]);

task(
    'files:permissions',
    'cd {{magento_dir}} && sudo chmod -R g+w var vendor pub/static pub/media app/etc && chmod u+x bin/magento'
);

// OPcache configuration
task('cache:clear:opcache', 'systemctl reload php7.1-fpm.service')->onStage('prod');
task('cache:clear:opcache', 'systemctl reload php7.2-fpm.service')->onStage('stage');
after('cache:clear', 'cache:clear:opcache');

// Build host
localhost('build');

// Remote Servers
/*host('dev_master')
    ->hostname('<hostname>')
    ->user('<user>')
    ->set('deploy_path', '~')
    ->stage('dev')
    ->roles('master');*/

host('stage_master')
    ->hostname('staging.snusme.com')
    ->user('root')
    ->set('deploy_path', '/var/www/staging.snusme.com')
    ->forwardAgent()
    ->stage('stage')
    ->roles('master');

host('prod_master')
    ->hostname('snusme.com')
    ->user('root')
    ->set('deploy_path', '/var/www/snusme.com')
    ->stage('prod')
    ->forwardAgent()
    ->roles('master');

// ---- Multi-server Configuration ----
// Tasks available only for specific roles
// task('config:import')->onRoles('master');
// task('database:upgrade')->onRoles('master');
// task('crontab:update')->onRoles('master');
//
//host('prod_slave_1')
//    ->hostname('<hostname>')
//    ->user('<user>')
//    ->set('deploy_path', '~')
//    ->stage('prod')
//    ->roles('slave');
//
//host('prod_slave_2')
//    ->hostname('<hostname>')
//    ->user('<user>')
//    ->set('deploy_path', '~')
//    ->stage('prod')
//    ->roles('slave');
