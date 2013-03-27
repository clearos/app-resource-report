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
     * @param string $range range information
     *
     * @return array load summary data
     * @throws Engine_Exception
     */

    public function get_load_data($range = 'today')
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['timeline_select'] = array('load_1min', 'load_5min', 'load_15min');
        $sql['timeline_from'] = 'resource';

        $options['range'] = $range;

        $entries = $this->_run_query('resource', $sql, $options);

        // Format report data
        //-------------------

        $report_data = $this->_get_data_info('system_load');

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
     * @param string $range range information
     *
     * @return array memory summary data
     * @throws Engine_Exception
     */

    public function get_memory_data($range = 'today')
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['timeline_select'] = array('memory_free', 'memory_cached', 'memory_buffers', 'memory_kernel');
        $sql['timeline_from'] = 'resource';

        $options['range'] = $range;

        $entries = $this->_run_query('resource', $sql, $options);

        // Parse report data
        //------------------

        $report_data = $this->_get_data_info('memory');

        $megabytes = 1024;

        foreach ($entries as $entry) {
            $report_data['data'][] = array(
                $entry['timestamp'], 
                (int) round($entry['memory_kernel'] / $megabytes),
                (int) round($entry['memory_buffers'] / $megabytes),
                (int) round($entry['memory_cached'] / $megabytes),
                (int) round($entry['memory_free'] / $megabytes)
            );
            $total[] = round(($entry['memory_kernel'] + $entry['memory_buffers'] + $entry['memory_cached'] + $entry['memory_free']) / $megabytes);
        }

        // Add format information
        //-----------------------

        $report_data['format']['series_max'] = ceil(max($total)/$megabytes) * 1000;

        return $report_data;
    }

    /**
     * Returns swap memory summary data.
     *
     * @param string $range range information
     *
     * @return array swap memory summary data
     * @throws Engine_Exception
     */

    public function get_swap_data($range = 'today')
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['timeline_select'] = array('swap_free', 'swap_used');
        $sql['timeline_from'] = 'resource';

        $options['range'] = $range;

        $entries = $this->_run_query('resource', $sql, $options);

        // Format report data
        //-------------------

        $report_data = $this->_get_data_info('swap');

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

        $report_data['format']['series_max'] = $series_max;

        return $report_data;
    }

    /**
     * Returns uptime summary data.
     *
     * @param string $range range information
     *
     * @return array uptime summary data
     * @throws Engine_Exception
     */

    public function get_uptime_data($range = 'today')
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['timeline_select'] = array('uptime', 'uptime_idle');
        $sql['timeline_from'] = 'resource';

        $options['range'] = $range;

        $entries = $this->_run_query('resource', $sql, $options);

        // Format report data
        //-------------------

        $report_data = $this->_get_data_info('uptime');

        $days = 60 * 60 * 24;

        foreach ($entries as $entry) {
            $report_data['data'][] = array(
                $entry['timestamp'], 
                (float) sprintf('%.2f', $entry['uptime']/$days),
                (float) sprintf('%.2f', $entry['uptime_idle']/$days)
            );
        }

        return $report_data;
    }

    /**
     * Returns processes summary data.
     *
     * @param string $range range information
     *
     * @return array processes summary data
     * @throws Engine_Exception
     */

    public function get_process_data($range = 'today')
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get report data
        //----------------

        $sql['timeline_select'] = array('processes_total', 'processes_running');
        $sql['timeline_from'] = 'resource';

        $options['range'] = $range;

        $entries = $this->_run_query('resource', $sql, $options);

        // Format report data
        //-------------------

        $report_data = $this->_get_data_info('processes');

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

        // Initialize
        //-----------

        $this->_initialize_tables('resource_report', 'resource');

        // Get stats
        //----------

        $stats = new Stats();

        $load_averages = $stats->get_load_averages();
        $uptimes = $stats->get_uptimes();
        $memory_stats = $stats->get_memory_stats();
        $process_stats = $stats->get_process_stats();

        // Insert report data
        //----------------

        $sql['insert'] 
            = 'resource (`load_1min`, `load_5min`, `load_15min`, `processes_total`, `processes_running`, ' .
            '`memory_free`, `memory_cached`, `memory_buffers`, `memory_kernel`, `swap_free`, `swap_used`, ' .
            '`uptime`, `uptime_idle`)';

        $sql['values'] 
            = $load_averages['one'] . ',' .
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
        // System Load
        //------------

        $reports['system_load'] = array(
            'app' => 'resource_report',
            'title' => lang('base_system_load'),
            'api_data' => 'get_load_data',
            'chart_type' => 'timeline',
            'format' => array(
                'baseline_format' => 'timestamp'
            ),
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
            'app' => 'resource_report',
            'title' => lang('base_memory'),
            'api_data' => 'get_memory_data',
            'chart_type' => 'timeline_stack',
            'format' => array(
                'series_label' => lang('base_megabytes'),
                'baseline_format' => 'timestamp'
            ),
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
            'app' => 'resource_report',
            'title' => lang('base_swap_memory'),
            'api_data' => 'get_swap_data',
            'chart_type' => 'timeline_stack',
            'format' => array(
                'series_label' => lang('base_megabytes'),
                'baseline_format' => 'timestamp'
            ),
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
            'app' => 'resource_report',
            'title' => lang('base_processes'),
            'api_data' => 'get_process_data',
            'chart_type' => 'timeline',
            'format' => array(
                'baseline_format' => 'timestamp'
            ),
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

        // Uptime
        //-------

        $reports['uptime'] = array(
            'app' => 'resource_report',
            'title' => lang('base_uptime'),
            'api_data' => 'get_uptime_data',
            'chart_type' => 'timeline',
            'format' => array(
                'series_label' => lang('base_days'),
                'baseline_format' => 'timestamp'
            ),
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
