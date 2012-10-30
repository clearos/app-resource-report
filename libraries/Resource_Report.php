<?php

/**
 * Resource report class.
 *
 * @category   Apps
 * @package    Resource_Report
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/resource_report/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\resource_report;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');
clearos_load_language('resource_report');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\base\Stats as Stats;
use \clearos\apps\reports_database\Database_Report as Database_Report;

clearos_load_library('base/Stats');
clearos_load_library('reports_database/Database_Report');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Resource report class.
 *
 * @category   Apps
 * @package    Resource_Report
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/resource_report/
 */

class Resource_Report extends Database_Report
{
    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Resource report constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct();
    }

    /**
     * Returns load summary data.
     *
     * @return array load summary data
     * @throws Engine_Exception
     */

    public function get_load_data($range = 'today', $records = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['select'] = 'load_1min, load_5min, load_15min, timestamp';
        $sql['from'] = 'resource';
        $sql['where'] = 'load_1min IS NOT NULL';
        $sql['group_by'] = '';
        $sql['order_by'] = 'timestamp DESC';

        $options['range'] = $range;
        $options['cache_time'] = 0; // FIXME: no cache for testing

        $entries = $this->_run_query('resource', $sql, $options);

        // Format report data
        //-------------------

        $info = $this->get_report_info('system_load');

        $report_data = array();
        $report_data['header'] = $info['headers'];
        $report_data['type'] = $info['types'];

        foreach ($entries as $entry) {
            $report_data['data'][] = array(
                $entry['timestamp'], 
                (float) $entry['load_1min'],
                (float) $entry['load_5min'],
                (float) $entry['load_15min']
            );
        }

        return $report_data;
    }

    /**
     * Returns memory summary data.
     *
     * @return array memory summary data
     * @throws Engine_Exception
     */

    public function get_memory_data($range = 'today', $records = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['select'] = 'memory_free, memory_cached, memory_buffers, memory_kernel, timestamp';
        $sql['from'] = 'resource';
        $sql['where'] = 'memory_free IS NOT NULL';
        $sql['group_by'] = '';
        $sql['order_by'] = 'timestamp DESC';

        $options['range'] = $range;
        $options['cache_time'] = 0; // FIXME: no cache for testing

        $entries = $this->_run_query('resource', $sql, $options);

        // Parse report data
        //------------------

        $info = $this->get_report_info('memory');

        $report_data = array();
        $report_data['header'] = $info['headers'];
        $report_data['type'] = $info['types'];

        $megabytes = 1024;

        foreach ($entries as $entry) {
            $report_data['data'][] = array(
                $entry['timestamp'], 
                (int) round($entry['memory_kernel'] / $megabytes),
                (int) round($entry['memory_buffers'] / $megabytes),
                (int) round($entry['memory_cached'] / $megabytes),
                (int) round($entry['memory_free'] / $megabytes)
            );
        }

        // Add format information
        //-----------------------

        $total = $entry['memory_kernel'] + $entry['memory_buffers'] + $entry['memory_cached'] + $entry['memory_free']; 
        $series_max = ceil($total / $megabytes / $megabytes) * 1000;

        $report_data['format'] = array(
            'series_max' => $series_max,
            'series_units' => lang('base_megabytes'),
            'baseline_units' => 'timestamp',
            'baseline_label' => lang('base_date')
        );

        return $report_data;
    }

    /**
     * Returns swap memory summary data.
     *
     * @return array swap memory summary data
     * @throws Engine_Exception
     */

    public function get_swap_data($range = 'today', $records = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['select'] = 'swap_free, swap_used, timestamp';
        $sql['from'] = 'resource';
        $sql['where'] = 'swap_free IS NOT NULL';
        $sql['group_by'] = '';
        $sql['order_by'] = 'timestamp DESC';

        $options['range'] = $range;
        $options['cache_time'] = 0; // FIXME: no cache for testing

        $entries = $this->_run_query('resource', $sql, $options);

        // Format report data
        //-------------------

        $info = $this->get_report_info('swap');

        $report_data = array();
        $report_data['header'] = $info['headers'];
        $report_data['type'] = $info['types'];

        $megabytes = 1024;

        foreach ($entries as $entry) {
            $report_data['data'][] = array(
                $entry['timestamp'], 
                (int) round($entry['swap_free'] / $megabytes),
                (int) round($entry['swap_used'] / $megabytes)
            );
        }

        // Add format information
        //-----------------------

        $total = $entry['swap_free'] + $entry['swap_used'];
        $series_max = ceil($total / 1000000) * 1000;

        $report_data['format'] = array(
            'series_max' => $series_max,
            'series_units' => lang('base_megabytes'),
        );

        return $report_data;
    }

    /**
     * Returns uptime summary data.
     *
     * @return array uptime summary data
     * @throws Engine_Exception
     */

    public function get_uptime_data($range = 'today', $records = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['select'] = 'uptime, uptime_idle, timestamp';
        $sql['from'] = 'resource';
        $sql['where'] = 'uptime IS NOT NULL';
        $sql['group_by'] = '';
        $sql['order_by'] = 'timestamp DESC';

        $options['range'] = $range;
        $options['cache_time'] = 0; // FIXME: no cache for testing

        $entries = $this->_run_query('resource', $sql, $options);

        // Format report data
        //-------------------

        $info = $this->get_report_info('uptime');

        $report_data = array();
        $report_data['header'] = $info['headers'];
        $report_data['type'] = $info['types'];
        $days = 60 * 60 * 24;

        foreach ($entries as $entry) {
            $report_data['data'][] = array(
                $entry['timestamp'], 
                (float) sprintf('%.2f', $entry['uptime']/$days),
                (float) sprintf('%.2f', $entry['uptime_idle']/$days)
            );
        }

        // Add format information
        //-----------------------

        $report_data['format'] = array(
            'series_units' => lang('base_days'),
        );

        return $report_data;
    }

    /**
     * Returns processes summary data.
     *
     * @return array processes summary data
     * @throws Engine_Exception
     */

    public function get_process_data($range = 'today', $records = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['select'] = 'processes_total, processes_running, timestamp';
        $sql['from'] = 'resource';
        $sql['where'] = 'processes_total IS NOT NULL';
        $sql['group_by'] = '';
        $sql['order_by'] = 'timestamp DESC';

        $options['range'] = $range;
        $options['cache_time'] = 0; // FIXME: no cache for testing

        $entries = $this->_run_query('resource', $sql, $options);

        // Format report data
        //-------------------

        $info = $this->get_report_info('processes');

        $report_data = array();
        $report_data['header'] = $info['headers'];
        $report_data['type'] = $info['types'];

        foreach ($entries as $entry) {
            $report_data['data'][] = array(
                $entry['timestamp'], 
                (int) $entry['processes_total'],
                (int) $entry['processes_running']
            );
        }

        return $report_data;
    }

    /**
     * Inserts resource data into databasel.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function insert_data()
    {
        clearos_profile(__METHOD__, __LINE__);

        $stats = new Stats();

        // Get stats
        //----------

        $load_averages = $stats->get_load_averages();
        $uptimes = $stats->get_uptimes();
        $memory_stats = $stats->get_memory_stats();
        $process_stats = $stats->get_process_stats();

        // Insert report data
        //----------------

        $sql['insert'] = "resource (`load_1min`, `load_5min`, `load_15min`, `processes_total`, `processes_running`, `memory_free`, `memory_cached`, `memory_buffers`, `memory_kernel`, `swap_free`, `swap_used`, `uptime`, `uptime_idle`)";

        $sql['values'] = 
            $load_averages['one'] . ',' .
            $load_averages['five'] . ',' .
            $load_averages['fifteen'] . ',' .
            $process_stats['total'] . ',' .
            $process_stats['running'] . ',' .
            $memory_stats['free'] . ',' .
            $memory_stats['cached'] . ',' .
            $memory_stats['buffers'] . ',' .
            $memory_stats['kernel_and_apps'] . ',' .
            $memory_stats['swap_free'] . ',' .
            $memory_stats['swap_used'] . ',' .
            $uptimes['uptime'] . ',' .
            $uptimes['idle']
        ;

        $this->_run_insert('resource', $sql);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Report engine definition.
     *
     * @return array report definition
     */
    
    protected function _get_definition()
    {
        // Overview
        //---------

        $reports['overview'] = array(
            'title' => lang('base_overview'),
            'url' => 'resource_report',
            'app' => 'resource_report',
            'report' => 'overview',
        );

        // System Load
        //------------

        $reports['system_load'] = array(
            'title' => lang('base_system_load'),
            'method' => 'get_load_data',
            'app' => 'resource_report',
            'url' => 'resource_report/system_load/index/full',
            'report' => 'system_load',
            'chart_type' => 'line',
            'library' => 'Resource_Report',
            'headers' => array(
                lang('base_date'),
                lang('base_1_minute_load'),
                lang('base_5_minute_load'),
                lang('base_15_minute_load'),
            ),
            'types' => array(
                'timestamp',
                'float',
                'float',
                'float'
            ),
        );

        // Memory
        //-------

        $reports['memory'] = array(
            'title' => lang('base_memory'),
            'method' => 'get_memory_data',
            'app' => 'resource_report',
            'url' => 'resource_report/memory/index/full',
            'report' => 'memory',
            'chart_type' => 'line_stack',
            'library' => 'Resource_Report',
            'headers' => array(
                lang('base_date'),
                lang('base_kernel_and_apps'),
                lang('base_buffers'),
                lang('base_cached'),
                lang('base_free'),
            ),
            'types' => array(
                'timestamp',
                'int',
                'int',
                'int',
                'int'
            ),
        );

        // Swap Memory
        //------------

        $reports['swap'] = array(
            'title' => lang('base_swap_memory'),
            'method' => 'get_swap_data',
            'app' => 'resource_report',
            'url' => 'resource_report/swap/index/full',
            'report' => 'swap',
            'chart_type' => 'line_stack',
            'library' => 'Resource_Report',
            'headers' => array(
                lang('base_date'),
                lang('base_swap_memory_free'),
                lang('base_swap_memory_used')
            ),
            'types' => array(
                'timestamp',
                'int',
                'int'
            ),
        );

        // Processes
        //----------

        $reports['processes'] = array(
            'title' => lang('base_processes'),
            'method' => 'get_process_data',
            'app' => 'resource_report',
            'url' => 'resource_report/processes/index/full',
            'report' => 'processes',
            'chart_type' => 'line',
            'library' => 'Resource_Report',
            'headers' => array(
                lang('base_date'),
                lang('base_processes'),
                lang('base_running_processes')
            ),
            'types' => array(
                'timestamp',
                'int',
                'int'
            ),
        );

        // Update
        //-------

        $reports['uptime'] = array(
            'title' => lang('base_uptime'),
            'method' => 'get_uptime_data',
            'app' => 'resource_report',
            'url' => 'resource_report/uptime/index/full',
            'report' => 'uptime',
            'chart_type' => 'line',
            'library' => 'Resource_Report',
            'headers' => array(
                lang('base_date'),
                lang('base_uptime'),
                lang('base_idle_time')
            ),
            'types' => array(
                'timestamp',
                'float',
                'float'
            ),
        );

        // Done
        //-----

        return $reports;
    }
}
