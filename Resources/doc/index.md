HappyR Mailer Bundle
==================================

HappyR Mailer Bundle makes it easier to send HTML emails. 

## Prerequisites

### Swift Mailer
You need to install and configure SwiftMailer before you install this bundle. For more information 
about SwiftMailer, refer to the [Symfony documentation](http://symfony.com/doc/current/cookbook/email/email.html).

## Installation

1. Download HappyRMailerBundle
2. Configure the Autoloader
3. Enable the Bundle
4. Configure the bundle with config.yml
5. Create your Mailer class (optional)


### Step 1: Download HappyRMailerBundle

Ultimately, the HappyRMailerBundle files should be downloaded to the
`vendor/bundles/HappyR/MalerBundle` directory.

This can be done in several ways, depending on your preference. The first
method is the standard Symfony2 method.

**Using the vendors script**

Add the following lines in your `deps` file:

``` ini
[HappyRMailerBundle]
    git=git://github.com/Nyholm/HappyRMailerBundle.git
    target=bundles/HappyR/MailerBundle

```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

**Using submodules**

If you prefer instead to use git submodules, then run the following:

``` bash
$ git submodule add git://github.com/Nyholm/HappyRMailerBundle.git vendor/bundles/HappyR/MailerBundle
$ git submodule update --init
```

### Step 2: Configure the Autoloader

Add the `HappyR` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'HappyR' => __DIR__.'/../vendor/bundles',
));
```

### Step 3: Enable the bundle

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


### Step 4: Configure the HappyRMailerBundle

This is the default configuration. Every field is optional but it is recommended that you specify them all. Add the following configuration to your `config.yml`

``` yaml
# app/config/config.yml
happyr_mailer:
    class: HappyR\MailerBundle\Util\Mailer
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

### Step 5: Create your Mailer class (optional)

To make it eaven esier to send mail from your application it is recommended that 
you extend the HappyRMailer class. Refer to this example class:

**Warning:**

> If you override the __construct() method in your User class, be sure
> to call parent::__construct(), as the base User class depends on
> this to initialize some fields.


``` php
<?php
// src/Any/ContentBundle/Util/Mailer.php

namespace Any\ContentBundle\Util;

use HappyR\MailerBundle\Util\Mailer as BaseMailer;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;


class Mailer extends BaseMailer
{
	 
	/**
	 * If you need a constructor use this code. Or else you may omit it completly.
	 */
	public function __construct(\Swift_Mailer $mailer, RouterInterface $router, EngineInterface $templating, array $parameters)
	{
		parent::__construct($mailer,$router, $templating, $parameters);
	}


    /**
     * Send a thank you mail to user
     * @param User $user
     */
    public function sendThankYouMail($user){
 	//user is a object in my application
	
	//you may choose template with $this->locale
        $template='AnyContentBundle:Email:'.$this->locale.'/thank_you.html.twig';

        //generate a url to something..
        $specialUrl=$this->router->generate('specual_route',array(),true);

        //send the email
        $this->send($user->getMail(), $template, array('user'=>$user,'special_url'=>$specialUrl));
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

<p>Thank you {{user.name}} for doing <b>that thing</b>.. Please visit <a href="{{special_url}}">this page</a> to get your reward.</p>

<p>//Webmaster</p>

{% endblock body %}

```

You need to change the config.yml to use your class.
``` yaml
# app/config/config.yml
happyr_mailer:
    class: Any\ContentBundle\Util\Mailer
    from:
        email: webmaster@example.com
        name: webmaster

```



**Attachments:**
If you want to send attachments you need to add them the the parameters array.
``` php
$this->send($mail, $template, array('special_url'=>$specialUrl, 'attachments'=>
				array(
						'/absolute/path/to/file'=>'content-type',
						'/absolute/path/to/other/file'=>null, //write null to not specify the content type
				)));

```

