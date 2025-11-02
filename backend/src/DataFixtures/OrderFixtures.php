<?php

namespace App\DataFixtures;

use App\Entity\Order;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        // Ensure companies, users, clients, and trucks exist first
        return [AppFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // We seeded 10 companies in AppFixtures
        $companies = 10;
        $ordersPerCompany = 6; // adjust to taste

        $statuses = ['pending', 'scheduled', 'delivered'];

        for ($ci = 1; $ci <= $companies; $ci++) {
            for ($o = 1; $o <= $ordersPerCompany; $o++) {
                $order = new Order();

                // Link to company
                /** @var \App\Entity\Company $company */
                $company = $this->getReference('company_' . $ci, \App\Entity\Company::class);
                $order->setCompany($company);

                // Link to a random of the two users
                $userIndex = random_int(1, 2);
                /** @var \App\Entity\User $user */
                $user = $this->getReference(sprintf('company_%d_user_%d', $ci, $userIndex), \App\Entity\User::class);
                $order->setUser($user);

                // Link to a random client (1..5)
                $clientIndex = random_int(1, 5);
                /** @var \App\Entity\Client $client */
                $client = $this->getReference(sprintf('company_%d_client_%d', $ci, $clientIndex), \App\Entity\Client::class);
                $order->setClient($client);

                // Optionally link to a truck (half of the time)
                if (random_int(0, 1) === 1) {
                    $truckIndex = random_int(1, 2);
                    /** @var \App\Entity\DeliveryTruck $truck */
                    $truck = $this->getReference(sprintf('company_%d_truck_%d', $ci, $truckIndex), \App\Entity\DeliveryTruck::class);
                    $order->setDeliveryTruck($truck);
                }

                // Data fields
                // Fuel amount in liters (string decimal per entity mapping)
                $liters = number_format(random_int(500, 5000) / 10, 2, '.', ''); // 50.00 - 500.00
                $order->setFuelAmount($liters);

                $order->setDeliveryAddress($client->getAddress());

                $status = $statuses[array_rand($statuses)];
                $order->setStatus($status);
                if ($status === 'delivered') {
                    $order->setDeliveredAt(new \DateTimeImmutable('-' . random_int(0, 10) . ' days'));
                }

                $notes = ['Priority delivery', 'Leave at back gate', 'Call on arrival', null, null];
                $order->setNotes($notes[array_rand($notes)]);

                $manager->persist($order);
            }
        }

        $manager->flush();
    }
}
