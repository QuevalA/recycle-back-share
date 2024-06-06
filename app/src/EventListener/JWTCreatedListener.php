<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;


class JWTCreatedListener{


    
    /**
     * @var RequestStack
     */
    private $requestStack;

    private Security $security;

        

    public function __construct(RequestStack $requestStack, Security $security)
    {
        $this->security = $security;
    }




        /**
         * @param JWTCreatedEvent $event
         *
         * @return void
         */
        public function onJWTCreated(JWTCreatedEvent $event)
        {

            $payload       = $event->getData();
            $payload['id'] = $this->security->getUser()->getId();
            $payload['pseudo'] = $this->security->getUser()->getPseudo();
            $payload['avatar'] = $this->security->getUser()->getFkAvatar()->getId();

            $event->setData($payload);

            $header        = $event->getHeader();
            $header['cty'] = 'JWT';

            $event->setHeader($header);

                $expiration = new \DateTime('+1 day');
                $payload['exp'] = $expiration->getTimestamp();
                $event->setData($payload);

                $header        = $event->getHeader();
                $header['cty'] = 'JWT';

                $event->setHeader($header);
            }
}
