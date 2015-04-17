<?php

namespace Happyr\MailerBundle\Provider;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestProvider.
 *
 * @author Tobias Nyholm
 */
class RequestProvider implements RequestProviderInterface
{
    public function getRequest($email, $tmplData)
    {
        return new Request();
    }
}
