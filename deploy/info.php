<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'resource_report';
$app['version'] = '1.4.6';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('resource_report_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('resource_report_app_name');
$app['category'] = lang('base_category_reports');
$app['subcategory'] = lang('base_category_system');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'app-reports-core >= 1:1.4.3',
    'app-reports-database-core >= 1:1.4.3',
    'app-tasks-core',
);

$app['core_file_manifest'] = array(
    'app-resource-report.cron' => array( 'target' => '/etc/cron.d/app-resource-report'),
    'resource2db' => array(
        'target' => '/usr/sbin/resource2db',
        'mode' => '0755',
    ),
);
