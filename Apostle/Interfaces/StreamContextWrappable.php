<?php

namespace Apostle\Interfaces;

/**
 * Wrapper for PHP stream context functions.
 *
 * @author  James Hollenbeck <wrampd@gmail.com>
 * @version  1.0.0.
 */
interface StreamContextWrappable
{
	/**
	 * Wrapper for PHP stream_context_create
	 * @param  array  $parameters stream_context parameters
	 * @return resource           stream context data
	 */
	public function stream_context_create(array $parameters);
}