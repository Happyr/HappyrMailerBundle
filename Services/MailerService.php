<?php

namespace HappyR\MailerBundle\Services;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Mailer class to be exteded by your application
 *
 *
 */
class MailerService
{

    protected $mailer;
    protected $templating;
    protected $parameters;

    /**
     * Constructor
     *
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $templating
     * @param array $parameters
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    /**
     * Send a message to $toEmail. Use the $template with the $parameters
     * @param String $toEmail
     * @param String $template
     * @param array $parameters
     *
     * @return integer
     */
    public function send($toEmail, $template, array $parameters=array()){
        //prepare attachments
        $attachments=array();
        if(isset($parameters['attachments']) && is_array($parameters['attachments'])){
            $attachments=$parameters['attachments'];
            unset($parameters['attachments']);
        }

        $renderedTemplate = $this->templating->render($template, $parameters);

        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->parameters['email'],$this->parameters['name'])
            ->setTo($toEmail)
            ->setBody($body,'text/html','utf-8');

        foreach($attachments as $path=>$contentType){
            $message->attach(\Swift_Attachment::fromPath($path,$contentType));
        }

        return $this->mailer->send($message);
    }
}