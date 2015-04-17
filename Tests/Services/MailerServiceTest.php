<?php

namespace Happyr\MailerBundle\Tests\Services;

use Happyr\MailerBundle\Services\MailerService;
use Mockery as m;

/**
 * Class MailerServiceTest.
 */
class MailerServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $validSwiftMessage;
    protected $tmpl;
    protected $tmplName;

    /**
     * Init tuff.
     */
    public function setUp()
    {
        $this->validSwiftMessage = m::on(
            function ($message) {
                return $message instanceof \Swift_Message;
            }
        );

        $this->templName = 'myTemplate';

        $this->templ = m::mock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->shouldReceive('render')->once()->with($this->templName, array())
            ->andReturn("subject\nBody\nmoreBody")
            ->getMock();
    }

    /**
     * Return a swift mock.
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
     * Return a fail swift mock.
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
     * Test the send function.
     */
    public function testSend()
    {
        $email = 'to@mail.se';
        $serviceParams = array('email' => 'test@from.se', 'name' => 'test', 'errorType' => 'exception');
        $container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $requestProvider = m::mock('Happyr\MailerBundle\Provider\RequestProviderInterface');

        $mailer = new MailerService($this->getSwift(), $this->templ, $container, $requestProvider, $serviceParams);
        $mailer->send($email, $this->templName, array());
    }
}
