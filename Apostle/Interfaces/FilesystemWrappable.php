<?php

namespace Apostle\Interfaces;

/**
 * Wrapper for PHP filesystem.
 *
 * @author  James Hollenbeck <wrampd@gmail.com>
 * @version  1.0.0
 */
interface FilesystemWrappable
{
	/**
	 * Wrapper for PHP file_get_contents in order to control responses and
	 * exceptions.
	 * 
	 * @param  string  $filename         File or URL to open
	 * @param  bool    $use_include_path Flag to use include path
	 * @param  resource  $context        Context data from stream_context_create
	 * @param  integer $offset           Where to start reading from the content
	 * @param  integer $maxlen           how many characters to read from the bugger
	 * @return array                     http response header array and contents
	 */
	public function file_get_contents($filename, $use_include_path, $context, $offset = -1, $maxlen = 42000);
}