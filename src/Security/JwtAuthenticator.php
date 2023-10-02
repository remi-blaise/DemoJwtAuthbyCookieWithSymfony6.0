<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JwtAuthenticator extends AbstractAuthenticator
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private JwtManager $jwtManager)
    {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->cookies->has('JWT') && $request->attributes->get('_route') !== 'app_login';
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->cookies->get('JWT');

        try {
            $data = $this->jwtManager->verifyJwt($token);
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException($e->message);
        }

        // 2. Authenticate WITHOUT requesting the database (stateless)
        $username = $data['username'];

        return new SelfValidatingPassport(new UserBadge($username));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login'));

        // $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        // return new Response($message, Response::HTTP_UNAUTHORIZED);
    }
}
