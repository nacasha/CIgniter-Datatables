<?php defined('BASEPATH') OR exit('No direct script access allowed');

  /**
  * CIgniter DataTables
  * CodeIgniter library for Datatables server-side processing / AJAX, easy to use :3
  *
  * @package    CodeIgniter
  * @subpackage libraries
  * @version    1.1 (development)
  *
  * @author     Izal Fathoni (izal.fat23@gmail.com)
  * @link 		https://github.com/izalfat23/CIgniter-Datatables
  */
class Datatables
{

	private $CI;
	private $searchable 	= array();
	private $js_options 	= '';
	private $style 			= '';
	private $connection 	= 'default';

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

		$this->query_builder = $this->_db;

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

    /**
     * Initialize datatable id
     *
     * @param  string $id id attribute of table
     * @return object     [description]
     */
	public function datatable($id)
	{
		$this->id = $id;
		return $this;
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
	public function init()
	{
		if(isset($_REQUEST['draw']) && isset($_REQUEST['length']) && isset($_REQUEST['start']))
		{
			$this->json();
			exit;
		}

		$this->CI->table->set_template(array(
			'table_open' => "<table id=\"$this->id\" $this->style>"
		));
		$this->CI->table->set_heading($this->table_heading);

		$this->CI->datatables->id 		= $this->id;
		$this->CI->datatables->js_options 	= $this->js_options;
	}

	/**
	 * Generate the datatables table (lol)
	 *
	 * @return html table
	 */
	public function generate()
	{
		echo $this->CI->table->generate();
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
		if(is_array($option)) {
			foreach ($option as $opt => $value) {
				$this->js_options .= "$opt: $value,\n";
			}
		} else {
			$this->js_options .= "$option: $value,\n";
		}

		return $this;
	}

	/**
	 * Jquery for datatables
	 *
	 * @return javascript
	 */
	public function jquery()
	{
		$output = '
		<script type="text/javascript" defer="defer">;
			function createDatatable() {
					erTable_'. $this->id .' = $("#'. $this->id .'").DataTable({
					processing: true,
					serverSide: true,
					searchDelay: 500,
					autoWidth : false,
					' .$this->js_options. '
					ajax: {
						url: "'. site_url(uri_string()) .'",
						type:"POST",
						data: {},
					},
				});
			};

			function refreshDatatable(){
				' .$this->id. 'destroy();
				createDatatable();
			};

			createDatatable();
		</script>';

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
			$col = str_replace(' ', '', $col);
			$col = strstr($col, 'as', true) ?: $col;
			$this->searchable[] = $col;
		}

		if(is_array($this->searchable)) {
			$this->searchable = implode(',', $this->searchable);
		}

		if($search != "") {
			$this->_db->where("CONCAT($this->searchable) LIKE ('%$search%')");
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

		$this->_db->limit($length, $start);
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