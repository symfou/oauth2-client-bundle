<?php

namespace OAuth2\ClientBundle\Security\Firewall;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use OAuth2\ClientBundle\Security\Authentication\Token\OAuth2Token;
use Symfony\Component\HttpFoundation\Response;

class OAuth2AccessTokenListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Look for an access token
        $authHeader = preg_split('/[\s]+/', $request->headers->get('Authorization'));
        $access_token = isset($authHeader[1]) ? $authHeader[1] : $request->get('access_token');

        if (!empty($access_token)) {
            $token = new OAuth2Token();
            $token->setAccessToken($access_token);

            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);
            return;
        }

        // By default deny authorization
        $response = new Response(null, 403);
        $event->setResponse($response);
    }
}