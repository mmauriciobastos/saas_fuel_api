<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Filter\CompanyFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Event subscriber to automatically enable and configure the company filter
 * based on the authenticated user's company.
 */
class CompanyFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Skip if no token (user not authenticated)
        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser()) {
            return;
        }

        $user = $token->getUser();
        
        // Skip if user is not a User entity instance (e.g., string)
        if (!$user instanceof User) {
            return;
        }

        // Skip if user has no company
        $company = $user->getCompany();
        if (!$company) {
            return;
        }

        // Enable and configure the company filter
        $filter = $this->em->getFilters()->enable('company_filter');
        $filter->setParameter('company_id', $company->getId());
    }
}

