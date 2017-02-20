# CIgniter-Datatables
CodeIgniter library for Datatables server-side processing / AJAX, easy to use :3

## Features ##
1. Easy to use.
2. Generates Datatable and JSON for server side processing in just one controller.
3. Use CodeIgniter Query Builder Class to produce query (support all functions). [Read Documentation](https://www.codeigniter.com/userguide3/database/query_builder.html)
4. Support columns rendering/formatting.
5. Able to define searchable table columns.
6. Configurable datatables options. [Read Documentation](https://datatables.net/reference/option/)

## Wiki
1. [Basic Usage](https://github.com/izalfat23/CIgniter-Datatables/wiki/Basic-Usage)




## Installing

* jQuery

	```
	<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
	```

* DataTables

	```
	<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
	```

* CIgniter Datatables Library

	Download and place to your codeigniter libraries folder

## Usage

Use CodeIgniter Query Builder Class/Active Record to build SQL query. [Read Query Builder Documentation](https://www.codeigniter.com/userguide3/database/query_builder.html)

Create new object Datatables.

```php
$t = new Datatables;
// or use $this->datatables
```

Select columns and table. NOTE : Don't use `->get()` or other method for executing the query, let the library do for you.

```php
$t->query_builder
	->select('first_name, last_name, age, salary')->from('employees');
```

Use `column()` to add column to datatables.

```php
$t->datatable('employees_table') // table's id
    ->column('First Name', 'first_name')
    ->column('Last Name', 'last_name')
    ->column('Age Name', 'age')
    ->column('Salary', 'title');
```

Initialize the configurations

```
$t->init();
```

Generate table in views

```
$this->datatables->generate();
$this->datatables->jquery();
```

## Example

Controllers

```php
$t = new Datatables;
$t->query_builder
	->select('first_name, last_name, age, salary')->from('employees');

$t->datatable('employees_table') // table's id
    ->column('First Name', 'first_name')
    ->column('Last Name', 'last_name')
    ->column('Age Name', 'age')
    ->column('Salary', 'title');

$t->init();
```

Views

```
$this->datatables->generate();
$this->datatables->jquery();
```

## Column Rendering/Formatting


```php
$t->datatable('employees_table')
    ->column('Name', 'name', function($data, $row){
    	return $row['first_name'] .' '. $row['last_name'];
    })
    ->column('Age', 'age')
    ->column('Salary', 'salary', function($data, $row){
		return 'Rp. '. $data;
    });

$t->init();
```

## Custom searchable column


```php
$t->datatable('employees_table')
    ->searchable('first_name, age'); 	// table columns
```

## Datatable Options

DataTables and its extensions are extremely configurable libraries and almost every aspect of the enhancements they make to HTML tables can be customised.

You can use `set_options` add the options. 

Note : Second parameter will not produce single quote, wrap option value with double quotes to produce single quotes or use escaping.
```php
$t->datatable('employees_table')
	->set_options('searching', 'false')			// searching : false
	->set_options('pagingType', '\'simple\'')		// pagingType : 'simple'
  //->set_options('pagingType', "'simple'")
    ->set_options('lengthMenu', '[ 10, 25, 50, 75, 100 ]')	 // lengthMenu : [ 10, 25, 50, 75, 100 ]
```

You can use array too ...

```php
->set_options(array(
    array('searching', 'false')
    array('pagingType', "'simple'")
    array('lengthMenu', '[ 10, 25, 50, 75, 100 ]')
));
```

## Styling Tables

You can use `style` to add table tag attributes to styling your table.

```
$t->datatable('employees_table')
	->style(array(
		'class' => 'table table-bordered table-striped',
	))
```

## Changelog

<b>Version 1.0</b>
	
* Initial Release (Development)
