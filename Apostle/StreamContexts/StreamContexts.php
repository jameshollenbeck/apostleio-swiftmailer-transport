<?php

namespace Apostle\StreamContexts;

use Apostle\Exceptions\StreamContextException;
use Apostle\Interfaces\StreamContextWrappable;

/**
 * Custom wrapper for stream_context_* functions. This allows for us
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
class StreamContexts implements StreamContextWrappable
{
	/**
	 * {@inheritdoc}
	 * @throws \Apostle\Exceptions\StreamContextException If $context is not set
	 */
	public function stream_context_create(array $parameters)
	{
		$context = stream_context_create($parameters);

		if(!$context)
		{
			throw new StreamContextException('Could not obtain resource during stream_context_create');
		}

		return $context;
	}
}