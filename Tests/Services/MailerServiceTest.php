<?php

namespace HappyR\MailerBundle\Tests\Services;

use HappyR\MailerBundle\Services\MailerService;
use Mockery as m;

/**
 * Class MailerServiceTest
 *
 *
 */
class MailerServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the send function
     *
     */
    public function testSend()
    {
        $email='to@mail.se';

        $templName='myTemplate';
        $params=array();

        $serviceParams=array('email'=>'test@from.se','name'=>'test');

        $templ=m::mock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templ->shouldReceive('render')->once()->with($templName,$params)
            ->andReturn("subject\nBody\nmoreBody");

        $swift=m::mock('Swift_Mailer');
        $swift->shouldReceive('send')->once()->with(m::on(function($message){
            return $message instanceof \Swift_Message;
        }));

        $mailer=new MailerService($swift,$templ,$serviceParams);
        $mailer->send($email,$templName,$params);
    }
}
