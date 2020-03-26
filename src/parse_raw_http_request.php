<?php

/*
 * Parse raw HTTP request
 *
 * Copyright 2020, Herick Ribeiro
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

declare (strict_types = 1);

$parse_raw_http_request =
function () {

    function path2array(array &$array, string $path, $value)
    {

        if (($bracket_position = strpos($path, '[')) === false) {
            $array[$path] = $value;
        } else {
            preg_match_all('/\[(.*?)\]/', $path, $matches);
            $array = &$array[substr($path, 0, $bracket_position)];
            foreach ($matches[1] as $key) {
                if ($key === '') {
                    $array = &$array[];
                } else {
                    $array = &$array[$key];
                }
            }
            $array = $value;
        }

    }

    function raw2array(string $input, array &$array, int $upload_max_filesize)
    {

        list(, $boundary) = explode('boundary=', $_SERVER['CONTENT_TYPE']);
        $content = explode($boundary, $input);
        array_shift($content);
        array_pop($content);
        foreach ($content as $value) {

            list($key, $data) = explode("\r\n\r\n", $value, 2);

            $data = substr($data, 0, -4);

            if (strpos($key, '; filename=') === false) {

                $key =
                    trim(
                    substr(
                        $key,
                        strpos(
                            $key,
                            '"'
                        )
                    ),
                    '"'
                );
                path2array($array, $key, $data);

            } else {

                while (1) {

                    $tmp = explode("\r\n", $key);
                    $file_type =
                        trim(
                        substr(
                            $tmp[2],
                            strpos(
                                $tmp[2],
                                ': '
                            ) + 2
                        )
                    );
                    list($key, $file_name) =
                        array_map(
                        function (string $cell) {
                            return trim($cell, '"');
                        },
                        explode(
                            '; filename=',
                            substr(
                                $tmp[1],
                                strpos(
                                    $tmp[1],
                                    '; name='
                                ) + 7
                            )
                        )
                    );

                    if (trim($data) === '') {
                        $file_structure =
                            [
                            'name' => '',
                            'tmp_name' => '',
                            'size' => 0,
                            'type' => '',
                            'error' => UPLOAD_ERR_NO_FILE,
                        ];
                        break;
                    }

                    $file_structure =
                        [
                        'name' => $file_name,
                        'tmp_name' => '',
                        'size' => strlen($data),
                        'type' => $file_type,
                        'error' => UPLOAD_ERR_OK,
                    ];

                    if (strlen($data) > $upload_max_filesize) {
                        $file_structure['error'] = UPLOAD_ERR_INI_SIZE;
                        break;
                    }

                    $GLOBALS['_parse_raw_http_request_tmpfile'][] = tmpfile();
                    $last_index = count($GLOBALS['_parse_raw_http_request_tmpfile']) - 1;
                    if ($GLOBALS['_parse_raw_http_request_tmpfile'][$last_index] === false) {
                        $file_structure['error'] = UPLOAD_ERR_NO_TMP_DIR;
                        break;
                    }

                    if (fwrite($GLOBALS['_parse_raw_http_request_tmpfile'][$last_index], $data) === false) {
                        $file_structure['error'] = UPLOAD_ERR_CANT_WRITE;
                        break;
                    }

                    $file_structure['tmp_name'] = stream_get_meta_data($GLOBALS['_parse_raw_http_request_tmpfile'][$last_index])['uri'];

                    break;
                }

                path2array($_FILES, $key, $file_structure);

            }

        }

    }

    $upload_max_filesize =
        trim(
        ini_get('upload_max_filesize')
    );
    while (1) {
        if ($upload_max_filesize === '') {
            $upload_max_filesize = 0;
            break;
        }
        $size =
            round(
            floatval(
                preg_replace(
                    '/[^0-9\.]/',
                    '',
                    $upload_max_filesize
                )
            )
        );
        if ($size === '') {
            $upload_max_filesize = 0;
            break;
        }
        $unit =
            strtoupper(
            substr(
                preg_replace(
                    '/[^BKMGTPEZY]/i',
                    '',
                    $upload_max_filesize
                ),
                -1
            )
        );
        if ($unit === '') {
            break;
        }
        $upload_max_filesize =
            intval(
            $size *
            pow(
                1024,
                strpos(
                    'BKMGTPEZY',
                    $unit
                )
            )
        );
        break;
    }

    $GLOBALS['_GET'] = $_GET ?? [];
    $GLOBALS['_POST'] = $_POST ?? [];
    $GLOBALS['_PUT'] = [];
    $GLOBALS['_DELETE'] = [];
    $GLOBALS['_FILES'] = $_FILES ?? [];
    $_SERVER['REQUEST_METHOD'] = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] !== '') {
        list($content_type) = explode(';', strtolower($_SERVER['CONTENT_TYPE']));
        $delimited = strpos($_SERVER['CONTENT_TYPE'], 'boundary=') !== false;
    } else {
        $_SERVER['CONTENT_TYPE'] = '';
        $content_type = '';
        $delimited = false;
    }
    $php_input = file_get_contents('php://input');
    switch ($content_type) {

        case '':
            break;

        case 'application/json':
            $GLOBALS['_' . $_SERVER['REQUEST_METHOD']] = $php_input === '' ? '' : json_decode($php_input, true);
            break;

        case 'application/xml':
            $GLOBALS['_' . $_SERVER['REQUEST_METHOD']] = $php_input === '' ? '' : new SimpleXMLElement($php_input);
            break;

        case 'application/x-www-form-urlencoded':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'DELETE':
                case 'GET':
                    $GLOBALS['_' . $_SERVER['REQUEST_METHOD']] = $php_input;
                    break;
                case 'POST':
                    if ($delimited) {
                        raw2array($php_input, $GLOBALS['_' . $_SERVER['REQUEST_METHOD']], $upload_max_filesize);
                    } else {
                        $GLOBALS['_' . $_SERVER['REQUEST_METHOD']] = $_POST;
                    }
                    break;
                case 'PUT':
                    if ($delimited) {
                        raw2array($php_input, $GLOBALS['_' . $_SERVER['REQUEST_METHOD']], $upload_max_filesize);
                    } else {
                        if ($php_input !== '') {
                            mb_parse_str($php_input, $GLOBALS['_' . $_SERVER['REQUEST_METHOD']]);
                        }
                    }
                    break;
                default:
                    trigger_error('Method unavailable for content type');
            }
            break;

        case 'multipart/form-data':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    if ($delimited) {
                        $GLOBALS['_' . $_SERVER['REQUEST_METHOD']] = $_POST;
                    } else {
                        if ($php_input !== '') {
                            mb_parse_str($php_input, $GLOBALS['_' . $_SERVER['REQUEST_METHOD']]);
                        }
                    }
                    break;
                case 'PUT':
                    if ($delimited) {
                        raw2array($php_input, $GLOBALS['_' . $_SERVER['REQUEST_METHOD']], $upload_max_filesize);
                    } else {
                        if ($php_input !== '') {
                            mb_parse_str($php_input, $GLOBALS['_' . $_SERVER['REQUEST_METHOD']]);
                        }
                    }
                    break;
                default:
                    trigger_error('Method unavailable for content type');
            }
            break;

        default:
            trigger_error('Content type not implemented or unknown (' . $content_type . ')');
            return;

    }

};

$parse_raw_http_request();
unset($parse_raw_http_request);
