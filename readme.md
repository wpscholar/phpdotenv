# PHP dotenv

A `.env` file parsing and loading library for PHP.

Automatically loads variables into a number of contexts:

- `getenv()` (default)
- `$_ENV` (default)
- `$_SERVER` (default)
- `apache_getenv` (optional)
- PHP constants (optional)
- Global variables (optional)
- A custom config array (optional)

## Why?
**You should never store sensitive credentials in your code.** Storing [configuration in the environment](http://www.12factor.net/config) is one of the tenets of a [twelve-factor app](http://www.12factor.net/). Anything that is likely to change between deployment environments – such as database credentials or credentials for 3rd party services – should be extracted from the code into environment variables.

## Requirements

- PHP 5.4

## Installation

Using [Composer](https://getcomposer.org/), run `composer require wpscholar/phpdotenv`.

Make sure you have a line in your code to handle autoloading:

```php
<?php

require __DIR__ . '/vendor/autoload.php';
```

## Usage

Create a new loader and use any of the available methods to help customize your configuration:

```php
<?php

$loader = new wpscholar\phpdotenv\Loader(); // Can also do wpscholar\phpdotenv\Loader::create() 
$loader
    ->config([ // Must be used to customize adapters, can also be used to set defaults or required variables.
        'adapters' => [
            'apache',   // Uses apache_setenv() 
            'array',    // Uses a custom array
            'define',   // Uses define() to set PHP constants
            'env',      // Uses $_ENV
            'global',   // Sets global variables
            'putenv',   // Uses putenv()
            'server'    // Uses $_SERVER
        ], 
        'defaults' => [
            'foo' => 'bar' // Set a default value if not provided in .env  	
        ],
        'required' => [
            'bar', // Require that a variable be defined in the .env file. Throws an exception if not defined.
            'baz',
        ],
    ])
    ->required([ // Another way to define required variables
        'bar',
        'baz',
        'quux',    	
    ])
    ->setDefaults([ // Another way to set defaults
        'foo' => 'bar',	
    ])
    ->parse([ __DIR__ . '/.env', dirname( __DIR__ ) . '/.env' ]) // Array of file paths to check for a .env file. Parses found file and loads vars into memory.
    ->set( 'qux', $loader->get('foo') ); // Override variables after loading, but with access to existing variables before they are loaded into the environment.
    
// Validate variable values after parsing the .env file, but before loading the results into the environment.
$loader->validate('foo')->notEmpty();
$loader->validate('bar')->isBoolean();
$loader->validate('baz')->isInteger();
$loader->validate('qux')->notEmpty()->allowedValues( [ 'bar', 'baz' ] ); // Validations can be chained together.
$loader->validate('quux')->assert(function( $value ) { // Apply your own custom validation assertions.
    return is_int($value) && $value > 0 && $value <= 10;	
});

// Call load() to load variables into the environment without overwriting existing variables.
$loader->load();

// Call overload() to load variables into the environment, overwriting any existing variables.
$loader->overload();
```

It is possible to create multiple instances of the loader, each loading a different .env file and loading variables into different contexts.

### Custom Configuration Array Example Usage

```php
<?php

$loader = wpscholar\phpdotenv\Loader::create();
$loader
    ->config([ 'adapters' => 'array'] ) // All values are self-contained in an array within the loader.
    ->required([ 'bar', 'baz', 'quux', ])
    ->setDefaults([ 'foo' => 'bar' ])
    ->parse( __DIR__ . '/.env' )
    ->set( 'qux', $loader->get('foo') )
    ->load();

$config = $loader->all(); // Get an array containing the final values.

$bar = $loader->get('bar'); // Get a single value.
```

### WordPress `wp-config.php` Example Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use wpscholar\phpdotenv\Loader;

$loader = new Loader();
$loader
	->config( [ 'adapters' => 'define' ] ) // Will only set PHP constants
	->required( [ // Requires these be set in the .env file
		'DB_NAME',
		'DB_USER',
		'DB_PASSWORD',
	] )
	->setDefaults( [ // Defaults to use if not defined in .env file
		'ABSPATH'         => __DIR__ . '/wp',
		'DB_CHARSET'      => 'utf8',
		'DB_COLLATE'      => '',
		'DB_HOST'         => 'localhost',
		'WP_DEBUG'        => false,
		'WP_TABLE_PREFIX' => 'wp_',
	] )
	->parse( __DIR__ . '/.env' ) // Parse the .env file
	->set( 'WP_HOME', 'https://' . $_SERVER['HTTP_HOST'] )
	->set( 'WP_SITEURL', $loader->get( 'WP_HOME' ) . '/wp' ) // Use previously defined values to set other values.
	->set( 'WP_CONTENT_DIR', __DIR__ . '/content' )
	->set( 'WP_CONTENT_URL', $loader->get( 'WP_HOME' ) . '/content' )
	->set( 'DISALLOW_FILE_EDIT', true )
	->load(); // We could use overload() here, but we can't overwrite constants in PHP either way.

$table_prefix = WP_TABLE_PREFIX;

require_once( ABSPATH . 'wp-settings.php' );

```

## Creating a `.env` File

Sample `.env` file for the `wp-config.php` example:

```shell
DB_NAME=local
DB_USER=root
DB_PASSWORD=root
WP_DEBUG=true
SCRIPT_DEBUG=true
```

[Explore all the features of the `.env` file parser.](https://github.com/m1/Env#usage)

## Rules to Follow

When using `phpdotenv`, you should strive to follow these rules:

- Add your `.env` file to a `.gitignore` file to prevent sensitive data from being committed to the project repository.
- Use a `.env.example` to set a default configuration for your project. This allows your development team to override defaults in a method that works for their local environment.
- Always set sane defaults when possible.
- Where necessary, add comments to credentials with information as to what they are, how they are used, and how one might procure new ones.
- As `phpdotenv` uses more lax procedures for defining environment variables, ensure your .env files are compatible with your shell. A good way to test this is to run the following:
```shell
# Source in your .env file
source .env
# Check an environmental variable
foo
```
- When possible, avoid running `phpdotenv` in production settings. Instead, set environment variables in your webserver, process manager or in bash before your app loads.
