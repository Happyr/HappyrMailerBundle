<?php

namespace Happyr\MailerBundle\Tests\Services;

use Happyr\MailerBundle\Services\MailerService;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;


class MailerServiceTest extends TestCase
{
    private $validSwiftMessage;
    private $tmpl;
    private $tmplName;

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

        $this->tmplName = 'myTemplate';

        $this->tmpl = m::mock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->shouldReceive('render')->once()->with($this->tmplName, [])
            ->andReturn("subject\nBody\nmoreBody")
            ->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    /**
     * Test the send function.
     */
    public function testSend()
    {
        $email = 'to@mail.se';
        $serviceParams = ['email' => 'test@from.se', 'name' => 'test', 'errorType' => 'exception'];
        $translator = m::mock(TranslatorInterface::class)
            ->shouldReceive('getLocale')
            ->andReturn('sv')
            ->getMock();
        $translator->shouldReceive('setLocale')->once()->with('sv');

        $requestStack = m::mock(RequestStack::class);
        $requestProvider = m::mock('Happyr\MailerBundle\Provider\RequestProviderInterface');

        $mailer = new MailerService($this->getSwift(), $this->tmpl, $translator, $requestStack, $requestProvider, $serviceParams);
        $result = $mailer->send($email, $this->tmplName, []);
        $this->assertEquals(1, $result);
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
            []
        )
            ->andReturn(true)
            ->getMock();
    }

    /**
     * Return a fail swift mock.
     *
     * @return m\MockInterface
     */
    private function getFailSwift()
    {
        return m::mock('Swift_Mailer')->shouldReceive('send')->once()->with(
            $this->validSwiftMessage,
            []
        )
            ->andThrow('\Exception', 'test')
            ->getMock();
    }
}
