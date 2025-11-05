<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Enrich the JWT authentication success response with user and company data.
 */
class JWTAuthenticationSuccessListener implements EventSubscriberInterface
{
    public function __construct(
        private JWTEncoderInterface $encoder,
        #[Autowire(param: 'lexik_jwt_authentication.token_ttl')] private int $tokenTtl = 3600,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Triggered when json_login succeeds and Lexik returns the token
            'lexik_jwt_authentication.on_authentication_success' => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData(); // Contains the 'token' key by default
        $user = $event->getUser();

        // Derive expiry from token's exp claim, fallback to now + tokenTtl
        $expiresAtIso = null;
        $expiresIn = null;
        if (isset($data['token'])) {
            try {
                $payload = $this->encoder->decode($data['token']);
                if (isset($payload['exp'])) {
                    $expiresAtIso = (new \DateTimeImmutable())
                        ->setTimestamp((int) $payload['exp'])
                        ->format(\DateTimeInterface::ATOM);
                    $expiresIn = max(0, (int) $payload['exp'] - time());
                }
            } catch (\Throwable $e) {
                // ignore decode failures and use TTL fallback
            }
        }
        if ($expiresAtIso === null) {
            $expiresAtIso = (new \DateTimeImmutable('now'))
                ->add(new \DateInterval('PT' . max(1, (int) $this->tokenTtl) . 'S'))
                ->format(\DateTimeInterface::ATOM);
        }
        $data['expiresAt'] = $expiresAtIso;
        if ($expiresIn === null) {
            $expiresIn = max(1, (int) $this->tokenTtl);
        }
        $data['expiresIn'] = $expiresIn;

        // In some cases, $user can be a string (e.g., when anonymous), guard against it
        if (!$user instanceof User) {
            $event->setData($data);
            return;
        }

        $company = $user->getCompany();

        $data['user'] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'company' => $company ? [
                'id' => $company->getId(),
                'name' => $company->getName(),
            ] : null,
        ];

        $event->setData($data);
    }
}
