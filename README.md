HappyR Mailer Bundle
==================================

HappyR Mailer Bundle makes it easier to send HTML emails with your Symfony2 application.
This bundle supports template rendering and sending attachments.


What is HappyR?
---------------
The HappyR namespace is developed by [HappyRecruiting][1]. We put some of our bundles here because we love to share.
Since we use a lot of open source libraries and bundles in our application it feels natural to give back something.
You will find all our Symfony2 bundles that we've created for the open source world at [developer.happyr.se][2]. You
will also find more documentation about each bundle and our API clients, WordPress plugins and more.




Installation
------------

### Step 1: Using Composer

Install it with Composer!

```js
// composer.json
{
    // ...
    require: {
        // ...
        "happyr/google-analytics-bundle": "1.0.*",
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
    new HappyR\MailerBundle\HappyRMailerBundle(),
    // ...
);
```

### Step 3: Configure the bundle

``` yaml
# app/config/config.yml

happy_r_mailer:
    // ...
    from:
        email: you@company.com
        name: Your name
```

You find a the full configuration reference [here][3].


Usage
-----
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
		'/absolute/path/to/file'=>'content-type',
		'/absolute/path/to/other/file'=>null, //write null to not specify the content type
	)));

```




[1]: http://happyrecruiting.se
[2]: http://developer.happyr.se
[3]: http://developer.happyr.se/symfony2-bundles/happyr-mailer-bundle/configuration
