<?php

namespace Apostle;

/**
* This file declare the ApostleTransport class.
*
* @author James Hollenbeck <wrampd@gmail.com>
*/

/**
* the base class for ApostleIO transport
*/
abstract class SwiftApostleioTransportType implements \Swift_Transport
{
	/**
	* Swiftmailer Event Dispatcher
	* @var \Swift_Events_SimpleEventDispatcher
	*/
	protected $eventDispatcher;

	public function __construct(\Swift_Events_SimpleEventDispatcher $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
	}
}