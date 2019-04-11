# CIgniter-Datatables
CodeIgniter library for Datatables server-side processing / AJAX, easy to use :3

## Important changes
Commit : [870b1caadbf9a2756b513c1e58fe5f153086b399](https://github.com/nacasha/CIgniter-Datatables/commit/a83eeabcfd28fe99f45c942b0795736f9b7c540d)

Change basic API to create Datatables  

- Old : `$this->datatables->new();`
- **New : `$this->datatables->init();`**

Change API to init created datatables config

- Old : `$this->datatables->init();`
- **New : `$this->datatables->create();`**

## Features ##
1. Easy to use.
2. Generates Datatable and JSON for server side processing in just one controller.
3. Multiple Datatables in one page.
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
	
## Basic Example

Controllers

```php
$this->load->library('Datatables');

$dt_authors = $this->datatables->init();

$dt_authors->select('*')->from('authors');

$dt_authors
    ->style(array(
	'class' => 'table table-striped table-bordered',
    ))
    ->column('First Name', 'first_name')
    ->column('Last Name', 'last_name')
    ->column('Email', 'email');

$this->datatables->create('dt_authors', $dt_authors); 
```

Views

```
$this->datatables->generate('dt_authors');

// Add this line after you load jquery from code.jquery.com
$this->datatables->jquery('dt_authors');
```

## Usage

Use CodeIgniter Query Builder Class/Active Record to build SQL query. [Read Query Builder Documentation](https://www.codeigniter.com/userguide3/database/query_builder.html)

Create new variable to create initialize Datatables.

```php
$dt_authors = $this->datatables->init();
```

Select columns and table. NOTE : Don't use `->get()` or other method for executing the query, let the library do for you.

```php
$dt_authors->select('first_name, last_name, email')->from('authors');
```

Use `column()` to add column to datatables.

```php
$dt_authors
    ->column('First Name', 'first_name')
    ->column('Last Name', 'last_name')
    ->column('Email', 'email');
```

Create datatables instance using created configurations ($dt_authors) and provide unique name ('dt_authors')

```
$this->datatables->create('dt_authors', $dt_authors);
```

Generate table in views

```
$this->datatables->generate('dt_authors);
$this->datatables->jquery('dt_authors);
```

## Column Rendering/Formatting


```php
// $dt_authors is an example 
$dt_authors
    ->column('Name', 'name', function($data, $row){
    	return $row['first_name'] .' '. $row['last_name'];
    })
    ->column('Age', 'age', function($data, $row){
		return $data . ' years old';
    })
    ->column('Email', 'salary');

$t->create();
```

## Custom searchable column


```php
// $dt_authors is an example 
$dt_authors
    ->searchable('first_name, age'); 	// table columns
    // -> ... other chain methods
```

## Datatable Options

DataTables and its extensions are extremely configurable libraries and almost every aspect of the enhancements they make to HTML tables can be customised.

You can use `set_options` add the options. 

Note : Second parameter will not produce single quote, wrap option value with double quotes to produce single quotes or use escaping.
```php
// $dt_authors is an example 
$dt_authors
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

Use `set_options('ajax.data', '...')` to override ajax data options


### Show paginatin in top and bottom of Datatables
This is workaround incase you want to show pagination in both top and bottom of Datatables.
I will create new API with the other changes when it ready.

Go to DatatablesBuilder.php and search for '$output' at line 176 and add this lines ([#7](https://github.com/nacasha/CIgniter-Datatables/issues/7))
```
    \"pagingType\": \"full_numbers\",
    \"sDom\": '<\"top\"lfprtip><\"bottom\"><\"clear\">',
```


## Styling Tables

You can use `style` to add table tag attributes to styling your table.

```
// $dt_authors is an example 
$dt_authors
    ->style(array(
        'class' => 'table table-bordered table-striped',
    ))
```

## Changelog

<b>Version 1.5</b>
	
* Add new API to override ajax data
* Support multiple datatables in one page
* Fix unable to search on field contains null

<b>Version 1.1</b>
	
* Fix searching when use alias for columns
* Remove query_builder, use direct `select()` to build query

<b>Version 1.0</b>
	
* Initial Release (Development)
