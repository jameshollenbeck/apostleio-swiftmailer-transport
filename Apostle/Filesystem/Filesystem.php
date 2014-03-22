<?php

namespace Apostle\Filesystem;

use Apostle\Exceptions\FilesystemException;
use Apostle\Interfaces\FilesystemWrappable;

/**
 * Custom wrapper for filesystem functions. This allows for us
 * to use these functions as we are accustomed in PHP, but also aids in 
 * controlling errors and response information that doesn't otherwise exist.
 *
 * NOTE: This structure is also highly benificial for testing - we can
 * now mock/test php functions that otherwise require actual items to be
 * modified/touched.
 *
 * @author   James Hollenbeck <wrampd@gmail.com>
 * @version  1.0.0
 */
class Filesystem implements FilesystemWrappable
{
	/**
	 * {@inheritdoc}
	 * @throws \Apostle\Exceptions\FilesystemException If $contents is not set.
	 */
	public function file_get_contents($filename, $use_include_path, $context, $offset = -1, $maxlen = 42000)
	{
		$contents = @file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);

		if(!$contents)
		{
			throw new FilesystemException('Could not get contents using file_get_contents from url: ' . $filename);
		}

		return array('response_headers' => $http_response_header, 'contents' => $contents,);
	}
}