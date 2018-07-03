<?php

namespace Happyr\MailerBundle\Services;

use Happyr\MailerBundle\Exceptions\MailException;
use Happyr\MailerBundle\Provider\RequestProviderInterface;
use Swift_Attachment;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This mailer renders a template and send the email.
 */
class MailerService
{
    /**
     * @var \Swift_Mailer mailer
     */
    protected $mailer;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface templating
     */
    protected $templating;

    /**
     * @var array parameters
     */
    protected $parameters;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var \Happyr\MailerBundle\Provider\RequestProviderInterface requestProvider
     */
    protected $requestProvider;

    public function __construct(
        Swift_Mailer $mailer,
        EngineInterface $templating,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        RequestProviderInterface $rpi,
        array $parameters
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->requestProvider = $rpi;
        $this->parameters = $parameters;
    }

    /**
     * Set a parameter value.
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
     * @param string $name
     *
     * @return mixed
     */
    public function getParameters($name)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        return;
    }

    /**
     * Send a message to $toEmail. Use the $template with the $data.
     *
     * @param string $toEmail
     * @param string $template
     * @param array  $data
     *
     * @return int
     */
    public function send($toEmail, $template, array $data = [])
    {
        //prepare attachments
        $attachments = [];
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $attachments = $data['attachments'];
            unset($data['attachments']);
        }

        $headersToAdd = [];
        if (isset($data['message_headers']) && is_array($data['message_headers'])) {
            $headersToAdd = $data['message_headers'];
            unset($data['message_headers']);
        }

        // Fake a request to be able to use assets in the email twigs
        $orgLocale = $this->translator->getLocale();
        if ($this->getParameters('fakeRequest')) {
            if (null === $request = $this->requestStack->getMasterRequest()) {
                /** @var Request $request */
                $request = $this->requestProvider->getRequest($toEmail, array_merge(['_original_locale' => $orgLocale], $data));
                $this->translator->setLocale($request->getLocale());
            }
        }

        //Render the template
        $renderedTemplate = $this->templating->render($template, $data);
        $this->translator->setLocale($orgLocale);

        // Use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        //Create the message
        if (method_exists(\Swift_Message::class, 'newInstance')) {
            $message = \Swift_Message::newInstance();
        } else {
            $message = new \Swift_Message($subject);
        }

        $message->setSubject($subject)
            ->setFrom($this->parameters['email'], $this->parameters['name'])
            ->setTo($toEmail)
            ->setBody($body, 'text/html', 'utf-8');

        $headers = $message->getHeaders();
        foreach ($headersToAdd as $name => $value) {
            if (!is_array($value)) {
                $headers->addTextHeader($name, $value);
            } else {
                foreach ($value as $v) {
                    $headers->addTextHeader($name, $v);
                }
            }
        }

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
                'Could not sent emails to the following Recipeints: '.
                implode(', ', $failedRecipients).'.'
            );
        }

        return $response;
    }

    /**
     * @param Swift_Mailer $mailer
     *
     * @return $this
     */
    public function setMailer(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;

        return $this;
    }

    /**
     * Report errors according to the config.
     *
     * @param string $message
     *
     * @throws \Happyr\MailerBundle\Exceptions\MailException
     */
    protected function handleError($message)
    {
        if ('none' == $this->parameters['errorType']) {
            return;
        }

        if ('exception' == $this->parameters['errorType']) {
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
     * Prepare the attachments and add those to the message.
     *
     * @param \Swift_Message $message
     * @param array          &$attachments
     */
    protected function prepareAttachments(\Swift_Message $message, array &$attachments)
    {
        //prepare an array with defaults
        $defaults = [
            'data' => null,
            'path' => null,
            'contentType' => null,
            'filename' => null,
        ];

        //For each attachment
        foreach ($attachments as $key => $file) {
            $file = array_merge($defaults, $file);
            $attachment = new Swift_Attachment($file['data'], $file['filename'], $file['contentType']);

            if (null == $file['data']) {
                //fetch from path
                $attachment->setFile(
                    new \Swift_ByteStream_FileByteStream($file['path']),
                    $file['contentType']
                );
                if (null !== $file['filename']) {
                    $attachment->setFilename($file['filename']);
                }
            }

            //add it to the mail
            $message->attach($attachment);
        }
    }
}
