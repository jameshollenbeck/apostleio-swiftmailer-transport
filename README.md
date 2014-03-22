# Apostle.io Swiftmailer Transport

## Installation

### With Composer [Standalone Library]

Add `wrampd/apostleio-swiftmailer-transport` to `composer.json`.

```json
{
    "require": {
        "wrampd/apostleio-swiftmailer-transport": "~1.0"
    }
}
```

####Requirements once installed:

Include the autoload.php file found in the vendor directory after installation
into your project.

##How do I Use it?
```php
//Create the Transport - NOTE - APOSTLEION_ENDPOINT_URL is defaulted to http://deliver.apostle.io
$transport = SwiftApostleioTransport::newInstance( 'APOSTLEIO_ACCOUNT_KEY', 'APOSTLEIO_ENDPOINT_URL' );

//Create the Mailer using your created Transport
$mailer = Swift_Mailer::newInstance($transport);

$mailer->send($message);
```

##Extras

This transport has the option to pass in any a custom filesystem wrapper and stream context wrapper.
View the code in the directories Interfaces, Filesystem, and StreamContexts for these classes.

