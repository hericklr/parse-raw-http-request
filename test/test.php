<?php

	declare(strict_types=1);

	require '../src/parse_raw_http_request.php';

	if($_FILES['file1']['error']===UPLOAD_ERR_OK)
	{
		if(copy($_FILES['file1']['tmp_name'],'tmp/'.$_FILES['file1']['name'])===false)
		{
			trigger_error('Error on copy file1');
		}
	}

	if($_FILES['file2']['error']===UPLOAD_ERR_OK)
	{
		if(copy($_FILES['file2']['tmp_name'],'tmp/'.$_FILES['file2']['name'])===false)
		{
			trigger_error('Error on copy file2');
		}
	}

	echo '$_PUT',PHP_EOL,print_r($_PUT,true),PHP_EOL,'$_FILES',PHP_EOL,print_r($_FILES,true);
