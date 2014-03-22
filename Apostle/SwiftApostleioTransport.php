<?php

namespace Apostle;

use Apostle\SwiftApostleioTransportType;
use Apostle\StreamContexts\StreamContexts;
use Apostle\Filesystem\Filesystem;

use Apostle\Interfaces\FilesystemWrappable;
use Apostle\Interfaces\StreamContextWrappable;

use Apostle\Exceptions\FilesystemException;

/**
 * This class is a custon Symfony Swiftmailer Transport paradigm
 * that hooks into ApostleIO mail servuce (http://apostle.io).
 *
 * @author  James Hollenbeck <wrampd@gmail.com>
 * @version 1.0.0
 */
class SwiftApostleioTransport extends SwiftApostleioTransportType
{
    /**
     * When we create a new Transport Instance, we get back an array
     * from the dependencyContainer - of which 0 index contains our
     * event dispatcher.
     */
    const EVENT_DISPATCHER_FROM_DEPENDENCY_CONTAINER = 0;

    /**
     * Swiftmailer sendPerformed event trigger.
     */
    const EVENT_SEND_PERFORMED                       = 'sendPerformed';
    
    /**
     * Swiftmailer beforeSendPerformed event trigger.
     */
    const EVENT_BEFORE_SEND                          = 'beforeSendPerformed';

    /**
     * ApostleIO Account Key (Created on account)
     * @var string
     */
	private $accountAccessKey;

    /**
     * The ReST URl for your apostle account
     * @var string
     */
	private $apostleCommunicationUrl;

    /**
     * Wrapper for PHP file functions.
     * @var \Apostle\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * Wrapper for PHP stream_context functions.
     * @var \Apostle\StreamContexts\StreamContexts
     */
    private $streamContexts;

    /**
     * Non-static access point to crate a new ApostleIO transport type.
     * @param  string $accountAccessKey                                ApostleIO Account Key
     * @param  string $apostleCommunicationUrl                         The ReST URl for your apostle account
     * @param  \Apostle\Interfaces\FilesystemWrappable $filesystem     PHP filesystem wrapper
     * @param  \Apostle\Interfaces\StreamContextWrappable $context     PHP stream_context wrappper  
     */
	public function __construct($accountAccessKey, $apostleCommunicationUrl = 'http://deliver.apostle.io', FilesystemWrappable $filesystem = null, StreamContextWrappable $context = null)
	{
        // now register dependancies
        $params = \Swift_DependencyContainer::getInstance()
                                                ->register('transport.apostleio')
                                                ->withDependencies(array('transport.eventdispatcher'))
                                                ->createDependenciesFor('transport.apostleio');
        
        parent::__construct($params[self::EVENT_DISPATCHER_FROM_DEPENDENCY_CONTAINER]);
        
		$this->accountAccessKey 		= $accountAccessKey;
		$this->apostleCommunicationUrl  = $apostleCommunicationUrl;

        $this->filesystem               = ($filesystem)? $filesystem : new Filesystem();
        $this->streamContexts           = ($context)   ? $context    : new StreamContexts();
	}

    /**
     * Static way to get access to a new instance of an ApostleIO transport type.
     * @param  string $accountAccessKey                                ApostleIO Account Key
     * @param  string $apostleCommunicationUrl                         The ReST URl for your apostle account
     * @param  \Apostle\Interfaces\FilesystemWrappable $filesystem     PHP filesystem wrapper
     * @param  \Apostle\Interfaces\StreamContextWrappable $context     PHP stream_context wrappper 
     */
	public static function newInstance($accountAccessKey, $apostleCommunicationUrl = 'http://deliver.apostle.io', FilesystemWrappable $filesystem = null, StreamContextWrappable $context = null) 
	{
		return new self($accountAccessKey, $apostleCommunicationUrl, $filesystem, $context);
	}

    //////////////////////////////////////////////////////
    //           START INTERFACE REQUIREMENTS           //
    //////////////////////////////////////////////////////
    
    /**
     * Takes a swift mime message and sends it.
     * {@see https://gist.github.com/snikch/7606139} for message body format
     * @param  Swift_Mime_Message $message          JSON encoded array of parameters
     * @param  string             $apostleResponse  A container to hold response text and exception
     * @return int                                  1 if apostle queued the request, 0 otherwise
     */
	public function send(\Swift_Mime_Message $message, &$apostleResponse = null)
	{
        $success            = false;
        $context            = null;
        $event              = null;
        $apostleResponse    = array('response' => null, 'exception' => null);
        
        try
        {
            $event = $this->eventDispatcher->createSendEvent($this, $message);
            $this->handleSendEvents($event, self::EVENT_BEFORE_SEND);

            $context                     = $this->prepareStreamContext($message);
            $apostleResponse['response'] = $this->filesystem->file_get_contents($this->apostleCommunicationUrl, false, $context);

            $success                     = (strpos($apostleResponse['response']['response_headers'][0], '202 Accepted') !== FALSE);

            $event->setResult(($success? \Swift_Events_SendEvent::RESULT_SUCCESS : \Swift_Events_SendEvent::RESULT_FAILED));
            $this->handleSendEvents($event, self::EVENT_SEND_PERFORMED);

       }
       catch(\Exception $exception)
       {
            $apostleResponse['exception'] = $exception; 
       }

       return (int)$success;
	}

    /**
     * Test if this Transport mechanism has started.
     *
     * @return boolean
     */
    public function isStarted(){}

    /**
     * Start this Transport mechanism.
     */
    public function start(){}

    /**
     * Stop this Transport mechanism.
     */
    public function stop(){}

    ///////////////////////////////////////////////////
    //          END INTERFACE REQUIREMENTS           //
    ///////////////////////////////////////////////////
    
    /**
     * Register a plugin in the Transport.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }

    /**
     * Helper for creating a new \Swift_SendEvent
     * @param  Swift_Mime_Message $message message to send
     * @return \Swift_SendEvent   Newly created send event.
     */
    private function createSendEvent(\Swift_Mime_Message $message)
    {
        $event = $this->eventDispatcher->createSendEvent($this, $message);

        if(!$event)
        {
            throw new \RuntimeException('Could not create send event from event dispatcher.');
        }

        return $event;
    }

    /**
     * Used to break part the event creation and
     * @param  Swift_Mime_Message $message [description]
     */
    private function handleSendEvents(\Swift_Events_SendEvent &$event, $eventToDispatch)
    {
        switch(true)
        {
            case($event && ($eventToDispatch === self::EVENT_BEFORE_SEND)):
            case($event && ($eventToDispatch === self::EVENT_SEND_PERFORMED)):
                $this->eventDispatcher->dispatchEvent($event, $eventToDispatch);
                break;
            default:
                throw new \InvalidArgumentException('Unknown value received in handleSendEvents - event: ' . $eventToDispatch);
                break;
        }
    }

    /**
     * Helper function to create a new stream context.
     * @param  Swift_Mime_Message $message message to send
     * @return resource                    stream context resource
     */
    private function prepareStreamContext(\Swift_Mime_Message $message)
    {
        $parameters = 
                array(
                        'http' => 
                        array(
                                'method'  => 'POST',
                                'header'  =>"Content-Type: application/json\r\n" 
                                            . "Authorization: Bearer {$this->accountAccessKey}",
                                'content' => $message->getBody(),
                            ),
                );

        return $this->streamContexts->stream_context_create($parameters);
    }
}