<?php defined('BASEPATH') OR exit('No direct script access allowed');

  /**
  * CIgniter DataTables
  * CodeIgniter library for Datatables server-side processing / AJAX, easy to use :3
  *
  * @package    CodeIgniter
  * @subpackage libraries
  * @version    1.5
  *
  * @author     Izal Fathoni (izal.fat23@gmail.com)
  * @link 		https://github.com/nacasha/CIgniter-Datatables
  */
class DatatablesBuilder
{

    private $CI;
    private $searchable 	= array();
    private $style 			= '';
	private $connection 	= 'default';

	private $dt_options		= array(
		'searchDelay' 	=> '500',
		'autoWidth' 	=> 'false'
	);
	private $ax_options 	= '';

    /**
     * Load the necessary library from codeigniter and caching the query
     * We use Codeigniter Active Record to generate query
     */
    public function __construct()
    {
        $this->CI =& get_instance();

        $this->_db = $this->CI->load->database($this->connection, TRUE);
        $this->CI->load->helper('url');
        $this->CI->load->library('table');

        $this->_db->start_cache();
    }

    public function __destruct()
    {
        $this->_db->stop_cache();
        $this->_db->flush_cache();
    }

    /**
     * Select column want to fetch from database
     *
     * @param  string
     * @return object
     */
    public function select($columns)
    {
        $this->_db->select($columns);

        $this->searchable = $columns;
        return $this;
    }

    public function from($table)
    {
        $this->_db->from($table);

        $this->table = $table;
        return $this->_db;
    }
	
    public function where($data,$table)
    {
        $this->_db->where($data);

        $this->table = $table;
        return $this->_db;
    }
	
    public function like($data,$table)
    {
        $this->_db->like($data);

        $this->table = $table;
        return $this->_db;
    }

    public function style($data)
    {
        foreach ($data as $option => $value) {
            $this->style .= "$option=\"$value\"";
        }

        return $this;
    }

    /**
     * Set heading for the table
     *
     * @param  string $label    heading label
     * @param  string $source   column names
     * @param  method $function formatting the output
     * @return object
     */
    public function column($label, $source, $function = null)
    {
        $this->table_heading[] 		= $label;
        $this->columns[] 			= array($label, $source, $function);

        return $this;
    }

    /**
     * Initialize Datatables
     */
    public function init($dt_name)
    {
		if (isset($_REQUEST['dt_name'])) {
			if ($_REQUEST['dt_name'] == $dt_name) {
				if(isset($_REQUEST['draw']) && isset($_REQUEST['length']) && isset($_REQUEST['start']))
				{
					$this->json();
					exit;
				}
			}
        }
    }

    /**
     * Set searchable columns from table
     *
     * @param  string $data columns name
     * @return object
     */
    public function searchable($data)
    {
        $this->searchable = $data;
        return $this;
    }

    /**
     *	Add options to datatables jquery
     *
     * @param array / string 	$option options name
     * @param string 			$value  value
     */
    public function set_options($option, $value = null)
    {
		if ($option == 'ajax.data') {
			$this->ax_options .= $value;
		} else {
			if(is_array($option)) {
				foreach ($option as $opt => $value) {
					$this->dt_options[$opt] = $value;
				}
			} else {
				$this->dt_options[$option] = $value;
			}
		}

        return $this;
	}

	/**
     * Generate the datatables table (lol)
     *
     * @return html table
     */
    public function generate($id)
    {
		$this->CI->table->set_template(array(
            'table_open' => "<table id=\"$id\" $this->style>"
        ));
        $this->CI->table->set_heading($this->table_heading);

        echo $this->CI->table->generate();
    }

    /**
     * Jquery for datatables
     *
     * @return javascript
     */
    public function jquery($id)
    {
		$dt_options	= '';
		$ax_options = $this->ax_options;

		foreach ($this->dt_options as $opt => $value) {
			$dt_options .= "$opt: $value, \n";
		}

		$output = "
        <script type=\"text/javascript\" defer=\"defer\">
            function createDatatable() {
				erTable_{$id} = $(\"#{$id}\").DataTable({
                    processing: true,
                    serverSide: true,
                    {$dt_options}
                    ajax: {
                        url: \"". site_url(uri_string()) ."\",
						type: \"POST\",
                        data: function (d, dt) {
							d.dt_name = \"{$id}\"

							{$ax_options}
						}
					}
                });
            };

            createDatatable();
        </script>";

        echo $output;
    }

    /**
     * Generate JSON for datatables
     *
     * @return json
     */
    public function json()
    {
        $draw		= $_REQUEST['draw'];
        $length		= $_REQUEST['length'];
        $start		= $_REQUEST['start'];
        $order_by	= $_REQUEST['order'][0]['column'];
        $order_dir	= $_REQUEST['order'][0]['dir'];
        $search		= $_REQUEST['search']["value"];

        $output['data'] 	= array();

        if($this->searchable == '*') {
            $field = $this->_db->list_fields($this->table);
            $this->searchable = implode(',', $field);
		}

        $column = explode(',', $this->searchable);
		$this->searchable = array();

        foreach($column as $key => $col) {
            $col = strtolower($col);
            $col = strstr($col, ' as ', true) ?: $col;
            $this->searchable[] = $col;
		}

		if($search != "") {
			for($i=0; $i< count($this->searchable);$i++){
				if($i==0) $this->_db->like($this->searchable[$i], $search);
				else $this->_db->or_like($this->searchable[$i], $search);
			}
		}

        /** ---------------------------------------------------------------------- */
        /** Count records in database */
        /** ---------------------------------------------------------------------- */

        $total = $this->_db->count_all_results();

        $output['query_count'] 	= $this->_db->last_query();
        $output['recordsTotal'] = $output['recordsFiltered'] = $total;

        /** ---------------------------------------------------------------------- */
        /** Generate JSON */
		/** ---------------------------------------------------------------------- */

		if ($length != -1) {
			$this->_db->limit($length, $start);
		}
        $this->_db->order_by($this->columns[$order_by][1], $order_dir);

        $result 			= $this->_db->get()->result_array();
        $output['query'] 	=  $this->_db->last_query();

        foreach ($result as $row) {
            $arr = array();
            foreach ($this->columns as $key => $column) {
                $row_output = $row[$column[1]];
                if(isset($this->columns[$key][2])){
                    $row_output = call_user_func_array($this->columns[$key][2], array($row_output, $row));
                }
                $arr[] = $row_output;
            }
            $output['data'][] = $arr;
        }

        echo json_encode($output);
    }

}
