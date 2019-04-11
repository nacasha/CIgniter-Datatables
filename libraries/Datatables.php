<?php defined('BASEPATH') OR exit('No direct script access allowed');

require 'DatatablesBuilder.php';

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
class Datatables
{
	protected $datatables = array();

	public function init()
	{
		return new DatatablesBuilder();
	}

	public function create($dt_name, $source)
	{
		$source->init($dt_name);

		$this->datatables[$dt_name] = $source;
	}

	public function generate($dt_name)
	{
		if (isset($this->datatables[$dt_name]))
		{
			$this->datatables[$dt_name]->generate($dt_name);
		} else
		{
			exit("Datatables with id <b>${dt_name}</b> not found");
		}
	}

	public function jquery($dt_name)
	{
		if (isset($this->datatables[$dt_name]))
		{
			$this->datatables[$dt_name]->jquery($dt_name);
		} else
		{
			exit("Datatables with id <b>${dt_name}</b> not found");
		}
	}
}
