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

    protected $validSwiftMessage;
    protected $tmpl;
    protected $tmplName;

    /**
     * Init tuff
     */
    public function setUp()
    {
        $this->validSwiftMessage=m::on(
            function($message){
                return $message instanceof \Swift_Message;
            });

        $this->templName='myTemplate';

        $this->templ=m::mock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->shouldReceive('render')->once()->with($this->templName,array())
            ->andReturn("subject\nBody\nmoreBody")
            ->getMock();
    }

    /**
     * Return a swift mock
     *
     *
     * @return m\MockInterface
     */
    private function getSwift()
    {
        return m::mock('Swift_Mailer')->shouldReceive('send')->once()->with(
            $this->validSwiftMessage,
            array()
        )
            ->andReturn(true)
            ->getMock();
    }

    /**
     * Return a fail swift mock
     *
     *
     * @return m\MockInterface
     */
    private function getFailSwift()
    {
        return m::mock('Swift_Mailer')->shouldReceive('send')->once()->with(
            $this->validSwiftMessage,
            array()
        )
            ->andThrow('\Exception', 'test')
            ->getMock();
    }

    /**
     * Test the send function
     *
     * @return void
     */
    public function testSend()
    {
        $email='to@mail.se';
        $serviceParams=array('email'=>'test@from.se','name'=>'test', 'errorType'=>'exception');

        $mailer=new MailerService($this->getSwift(), $this->templ, $serviceParams);
        $mailer->send($email, $this->templName, array());
    }

    /**
     * Test the send function when swift fails and exeptions is chosen
     *
     * @expectedException \HappyR\MailerBundle\Exceptions\MailException
     *
     * @return void
     */
    public function testFailToSendException()
    {
        $email='to@mail.se';
        $serviceParams=array('email'=>'test@from.se','name'=>'test', 'errorType'=>'exception');

        $mailer=new MailerService($this->getFailSwift(), $this->templ, $serviceParams);
        $mailer->send($email, $this->templName, array());

    }

    /**
     * Test the send function when swift fails and error is chosen
     *
     * @expectedException PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testFailToSendExceptionError()
    {
        $email='to@mail.se';
        $serviceParams=array('email'=>'test@from.se','name'=>'test', 'errorType'=>'error');

        $mailer=new MailerService($this->getFailSwift(), $this->templ, $serviceParams);
        $mailer->send($email, $this->templName, array());

    }

    /**
     * Test the send function when swift fails and warning is chosen
     *
     * @expectedException PHPUnit_Framework_Error_Warning
     *
     * @return void
     */
    public function testFailToSendExceptionWarning()
    {
        $email='to@mail.se';
        $serviceParams=array('email'=>'test@from.se','name'=>'test', 'errorType'=>'warning');

        $mailer=new MailerService($this->getFailSwift(), $this->templ, $serviceParams);
        $mailer->send($email, $this->templName, array());

    }

    /**
     * Test the send function when swift fails and error is chosen
     *
     * @expectedException PHPUnit_Framework_Error_Notice
     *
     * @return void
     */
    public function testFailToSendExceptionNotice()
    {
        $email='to@mail.se';
        $serviceParams=array('email'=>'test@from.se','name'=>'test', 'errorType'=>'notice');

        $mailer=new MailerService($this->getFailSwift(), $this->templ, $serviceParams);
        $mailer->send($email, $this->templName, array());

    }

    /**
     * Test the send function when swift fails and we supress erors
     *
     * @return void
     */
    public function testFailToSendNoErrors()
    {
        $email='to@mail.se';
        $serviceParams=array('email'=>'test@from.se','name'=>'test', 'errorType'=>'none');

        $mailer=new MailerService($this->getFailSwift(), $this->templ, $serviceParams);
        $mailer->send($email, $this->templName, array());

    }
}
