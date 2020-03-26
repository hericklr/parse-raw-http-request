# Parse raw HTTP request

[![PHP version](https://img.shields.io/badge/php-%3E%3D7.1-8892BF.svg)](https://secure.php.net/) [![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://opensource.org/licenses/MIT)

Version 1.0.0

Analyzes the raw data received using the verb PUT and treats it to function as POST (including, especially, the attached files)

### Features

- expansion of field names with square brackets to multi-dimensional array
- extraction of any attached files and writing to the temporary folder until the execution of the script is complete (with automatic removal at the end)
- construction of the same data structure with details about each file received (**name**, **type**, **tmp\_name**, **error** and **size**)
- automatic scope execution and removal
- currently supports the verbs  **get**, **post**, **put** and **delete**
- keeps the usual superglobal variables untouched (**$\_GET**, **$\_POST** and **$\_FILES**)


### Usage

Just import the file at the beginning of the script that should receive the form data

```php
<?php

	require 'parse_raw_http_request.php';

	trigger_error(print_r($_GET,true));
	trigger_error(print_r($_POST,true));
	trigger_error(print_r($_PUT,true));
	trigger_error(print_r($_DELETE,true));
	trigger_error(print_r($_FILES,true));

// ...

```

There is a complete example in the "test" folder

### Contributing

Feel free to submit any contributions

### Author

[Herick Ribeiro](https://www.linkedin.com/in/herick-leite-ribeiro-06355818/?locale=en_US)

### Donations

Donations are more than welcome
If you like my work or encourage development, please, use the link below

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=V4XX5RM87A578&source=url)