<?php

namespace HappyR\MailerBundle\Services;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;


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

        //Render the template
        $renderedTemplate = $this->templating->render($template, $parameters);

        /*
         * Use the first line as the subject, and the rest as the body
         */
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));


        //Create the message
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->parameters['email'],$this->parameters['name'])
            ->setTo($toEmail)
            ->setBody($body,'text/html','utf-8');

        //Add the attachments
        foreach($attachments as $path=>$contentType){
            $message->attach(\Swift_Attachment::fromPath($path,$contentType));
        }

        //send it
        return $this->mailer->send($message);
    }
}