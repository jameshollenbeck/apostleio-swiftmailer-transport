<?php

namespace Apostle\Plugins

/**
 * Plugin stub for your enjoyment. Register the plugin, write the function bodies
 * and the rest is taken care of for you. You will need to view the swiftmailer
 * event classes to understand how these work - they are not well documented.
 */
class SwiftApostleioEventsSendListener implements \Swift_Events_SendListener
{
	public function beforeSendPerformed(\Swift_Events_SendEvent $event){}

	public function sendPerformed(\Swift_Events_SendEvent $event){}
}