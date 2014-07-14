<?php

namespace HappyR\MailerBundle\Services;

use HappyR\MailerBundle\Exceptions\MailException;
use HappyR\MailerBundle\Provider\RequestProviderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;
use Symfony\Component\HttpFoundation\Request;
use Swift_Mailer;
use Swift_Attachment;

/**
 * Class MailerService
 *
 * This mailer renders a template and send the email
 */
class MailerService
{
    /**
     * @var \Swift_Mailer mailer
     *
     *
     */
    protected $mailer;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface templating
     *
     *
     */
    protected $templating;

    /**
     * @var array parameters
     *
     *
     */
    protected $parameters;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface container
     *
     */
    protected $container;

    /**
     * @var \HappyR\MailerBundle\Provider\RequestProviderInterface requestProvider
     *
     */
    protected $requestProvider;

    /**
     * @param Swift_Mailer $mailer
     * @param EngineInterface $templating
     * @param ContainerInterface $container
     * @param RequestProviderInterface $pri
     * @param array $parameters
     */
    public function __construct(
        Swift_Mailer $mailer,
        EngineInterface $templating,
        ContainerInterface $container,
        RequestProviderInterface $rpi,
        array $parameters
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->container = $container;
        $this->parameters = $parameters;
        $this->requestProvider = $rpi;
    }

    /**
     * Set a parameter value
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setParameters($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameters($name)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        return null;
    }

    /**
     * Send a message to $toEmail. Use the $template with the $data
     *
     * @param String $toEmail
     * @param String $template
     * @param array $data
     *
     * @return integer
     */
    public function send($toEmail, $template, array $data = array())
    {
        //prepare attachments
        $attachments = array();
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $attachments = $data['attachments'];
            unset($data['attachments']);
        }

        /*
         * Fake a request to be able to use assets in the email twigs
         */
        try {
            if ($this->getParameters('fakeRequest')) {
                $request=$this->container->get('request');

                // if host = localhost we might want to try with a fake request
                if ('localhost' == $request->getHost()) {
                    throw new InactiveScopeException('foo', 'bar');
                }
            }
            $leaveScope=false;
        } catch(InactiveScopeException $e) {
            $this->container->enterScope('request');
            $this->container->set('request', $this->requestProvider->getRequest($toEmail, $data), 'request');
            $leaveScope=true;
        }

        //Render the template
        $renderedTemplate = $this->templating->render($template, $data);
        if ($leaveScope) {
            $this->container->leaveScope('request');
        }

        /*
         * Use the first line as the subject, and the rest as the body
         */
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        //Create the message
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->parameters['email'], $this->parameters['name'])
            ->setTo($toEmail)
            ->setBody($body, 'text/html', 'utf-8');

        $this->prepareAttachments($message, $attachments);

        //send it
        $failedRecipients = null;
        try {
            $response = $this->mailer->send($message, $failedRecipients);
        } catch (\Exception $e) {
            $response = false;
            $this->handleError($e->getMessage());
        }

        if (!$response && is_array($failedRecipients)) {
            $this->handleError(
                'Could not sent emails to the following Recipeints: ' .
                implode(', ', $failedRecipients) . '.'
            );
        }

        return $response;
    }

    /**
     * Report errors according to the config
     *
     * @param string $message
     *
     * @throws \HappyR\MailerBundle\Exceptions\MailException
     */
    protected function handleError($message)
    {
        if ($this->parameters['errorType'] == 'none') {
            return;
        }

        if ($this->parameters['errorType'] == 'exception') {
            throw new MailException($message);
        }

        //assert: We should trigger an error
        switch ($this->parameters['errorType']) {
            case 'error':
                $errorConstant = E_USER_ERROR;
                break;
            case 'warning':
                $errorConstant = E_USER_WARNING;
                break;
            case 'notice':
                $errorConstant = E_USER_NOTICE;
                break;
        }

        trigger_error($message, $errorConstant);
    }

    /**
     * Prepare the attachments and add those to the message
     *
     * @param Swift_Message &$message
     * @param array &$attachments
     *
     */
    protected function prepareAttachments(&$message, array &$attachments)
    {
        //prepare an array with defaults
        $defaults = array(
            'data' => null,
            'path' => null,
            'contentType' => null,
            'filename' => null,
        );

        //For each attachment
        foreach ($attachments as $key => $file) {
            if (!is_array($file)) {
                trigger_error(
                    'HappyRMailerBundle: The way you add attachments are depricated. ' .
                    'See http://developer.happyr.se how you should add attachments.',
                    E_USER_DEPRECATED
                );

                $message->attach(Swift_Attachment::fromPath($key, $file));
                continue;
            }

            $file = array_merge($defaults, $file);
            $attachment = new Swift_Attachment($file['data'], $file['filename'], $file['contentType']);

            if ($file['data'] == null) {
                //fetch from path
                $attachment->setFile(
                    new \Swift_ByteStream_FileByteStream($file['path']),
                    $file['contentType']
                );
            }

            //add it to the mail
            $message->attach($attachment);
        }
    }

    /**
     *
     * @param \Swift_Mailer $mailer
     *
     * @return $this
     */
    public function setMailer(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;

        return $this;
    }
}