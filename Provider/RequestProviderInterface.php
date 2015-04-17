<?php
namespace Happyr\MailerBundle\Provider;

/**
 * Class RequestProvider.
 *
 * @author Tobias Nyholm
 */
interface RequestProviderInterface
{
    /**
     * Get a new HttpFoundation request. You may implement this request and make sure the request has the
     * correct init values. Like locale.
     *
     * @param $email
     * @param $tmplData
     *
     * @return mixed
     */
    public function getRequest($email, $tmplData);
}
