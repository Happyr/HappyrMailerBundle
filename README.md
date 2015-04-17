Happyr Mailer Bundle
==================================

Happyr Mailer Bundle makes it easier to send HTML emails with your Symfony2 application.
This bundle supports template rendering and sending attachments.



## Installation


### Step 1: Using Composer

Install it with Composer!

```js
// composer.json
{
    // ...
    require: {
        // ...
        "happyr/mailer-bundle": "1.2.*",
    }
}
```

Then, you can install the new dependencies by running Composer's ``update``
command from the directory where your ``composer.json`` file is located:

```bash
$ php composer.phar update
```

### Step 2: Register the bundle

 To register the bundles with your kernel:

```php
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Happyr\MailerBundle\HappyrMailerBundle(),
    // ...
);
```

### Step 3: Configure the bundle

``` yaml
# app/config/config.yml

happyr_mailer:
    // ...
    from:
        email: you@company.com
        name: Your name
```

You find a the full configuration reference [here][3].


## Usage

``` php
<?php
// AnyController.php

public function anyAction(){
    $mailer=$this->get('happyr.mailer');

    $mailer->send('me@domain.com','AnyBundle:Email:test.html.twig');
}

```

**Attachments:**
If you want to send attachments you need to add them the the parameters array.
``` php
    $this->send($mail, $template, array('user'=>$user, 'attachments'=>
	array(
		//two attachments. You must specify either 'data' or 'path'
		array(
			'data'=>$bindaryPdf,
		    'contentType'=>'application/pdf',
		    'filename'=>'Invoice.pdf',
		),
		array(
			'path'=>$pathToPdf,
		    'contentType'=>'application/pdf',
		    'filename'=>'Welcome.pdf',
		),
	)));

```

**Message headers:**
You can add extra headers on the message if you like
``` php
    $this->send($mail, $template, array('user'=>$user, 'message_headers'=>
	array(
		'X-Mailgun-Variables' => json_encode(['foobar'=>'baz'])		
	)));

```

## Send emails from Symfony command

If you want to send emails from a Symfony2 command you are often getting errors like:
 ```You cannot create a service ("request") of an inactive scope ("request").```
 or ```You cannot create a service ("templating.helper.assets") of an inactive scope ("request").```

The error occur because you don't have access to a Request object. This bundle help you to fake a Request object.
You need to change some config:

``` yaml
# app/config/config.yml

happyr_mailer:
    fake_request: true #default value is false
```

If a request object does not exists we will help you to create it.


## Changelog

**1.3.0**
It is not possible to send emails from a console command without getting errors like:
"You cannot create a service ("request") of an inactive scope ("request")."


**1.2.0**
You will no logner get exceptions from switft. If you want to catch exceptions use
Happyr\MailerBundle\Exceptions\MailException.

You may now choose how error are handeled by using the error_tyoe config.

``` yaml
# app/config/config.yml

happyr_mailer:
    error_type: 'exception' #other possible values are 'error', 'warning', 'notice' and 'none'
```


[1]: http://happyrecruiting.se
[2]: http://developer.happyr.se
[3]: http://developer.happyr.se/symfony2-bundles/happyr-mailer-bundle/configuration
