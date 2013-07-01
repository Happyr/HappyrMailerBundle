HappyR Mailer Bundle
==================================

HappyR Mailer Bundle makes it easier to send HTML emails. 

## Prerequisites

### Swift Mailer
You need to install and configure SwiftMailer before you install this bundle. For more information 
about SwiftMailer, refer to the [Symfony documentation](http://symfony.com/doc/current/cookbook/email/email.html).

## Installation

1. Install HappyRMailerBundle
2. Enable the Bundle
3. Configure the bundle with config.yml
4. Create your Mailer class (optional)


### Step 1: Install HappyRMailerBundle

Use composer to install the bundle.


``` composer
composer require happyr/mailer-bundle:1.0.*
```


### Step 2: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new HappyR\MailerBundle\HappyRMailerBundle(),
    );
}
```


### Step 3: Configure the HappyRMailerBundle

This is the default configuration. Every field is optional but it is recommended that you specify them all.
Add the following configuration to your `config.yml`

``` yaml
# app/config/config.yml
happyr_mailer:
    from:
        email: webmaster@example.com
        name: webmaster

```

You can now use the HappyR Mailer like:
``` php
<?php
// AnyController.php

public function anyAction(){
    $mailer=$this->get('happyr.mailer');
    
    $mailer->send('me@domain.com','AnyBundle:Email:test.html.twig');
}

```

### Step 4: Create your Mailer class (optional)

To make it eaven esier to send mail from your application it is recommended that 
you extend the HappyRMailer class. Refer to this example class:


``` php
<?php
// src/Any/ContentBundle/Util/Mailer.php

namespace Any\ContentBundle\Services;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;


class Mailer
{

	 private $happyrMailer;

	/**
	 * A constructor that takes the HappyR mailer as a parameter
	 */
	public function __construct($happyrMailer)
	{
		$this->happyrMailer=$happyrMailer;
	}


    /**
     * Send a thank you mail to user
     * @param User $user
     */
    public function sendThankYouMail(User $user){
 	    //user is a object in my application
	
	    //you may choose template with $this->locale
        $template='AnyContentBundle:Email:thank_you.html.twig';

        //send the email
        $this->happyrMailer->send($user->getMail(), $template, array('user'=>$user));
    }
}
```

The twig template should inherit HappyRMailerBundle::base.html.twig. There is 3 blocks: subject, head and body.
This is a example template:
Any/ContentBundle/Email/en/thank_you.html.twig
``` twig
{# AnyContentBundle:Email:en/thank_you.html.twig #}
{% extends "HappyRMailerBundle::base.html.twig" %}

{# This is the email subject #}
{% block subject %}Thank you{% endblock subject %}

{% block head %}{% endblock head %}

{% block body %}

<p>Thank you {{user.name}} for doing <b>that thing</b>... </p>

<p>//Webmaster</p>

{% endblock body %}

```

You need to change the services.yml in your bundle.
``` yaml
# Any/ContentBundle/Resources/config/services.yml
services:
  my.mailer:
    class: Any\ContentBundle\Services\Mailer
    arguments: [@happyr.mailer]

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

