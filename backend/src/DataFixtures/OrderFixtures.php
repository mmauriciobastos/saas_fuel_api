<?php

namespace App\DataFixtures;

use App\Entity\Order;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        // Ensure companies, users, clients, and trucks exist first
        return [AppFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_CA'); // Canadian locale
        
        // We seeded 10 companies in AppFixtures
        $companies = 10;
    // Orders per company (up to 100)
    $ordersPerCompany = random_int(80, 100);

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

                // Optionally link to a truck (70% of the time)
                if ($faker->boolean(70)) {
                    $truckIndex = random_int(1, 2);
                    /** @var \App\Entity\DeliveryTruck $truck */
                    $truck = $this->getReference(sprintf('company_%d_truck_%d', $ci, $truckIndex), \App\Entity\DeliveryTruck::class);
                    $order->setDeliveryTruck($truck);
                }

                // Data fields
                // Fuel amount in liters (string decimal per entity mapping)
                $liters = $faker->randomFloat(2, 50, 500);
                $order->setFuelAmount((string) $liters);

                $order->setDeliveryAddress(
                    trim(
                        $client->getAddress() . ', ' . $client->getCity() . ' ' . ($client->getStateProvince() ?? '') . ' ' . ($client->getPostalCode() ?? '')
                    )
                );

                $status = $faker->randomElement($statuses);
                $order->setStatus($status);
                
                if ($status === 'delivered') {
                    $deliveredDateTime = $faker->dateTimeBetween('-30 days', 'now');
                    $order->setDeliveredAt(\DateTimeImmutable::createFromMutable($deliveredDateTime));
                } elseif ($status === 'scheduled') {
                    // Scheduled orders might have a future or recent past creation
                    $createdDateTime = $faker->dateTimeBetween('-7 days', 'now');
                    $order->setCreatedAt(\DateTimeImmutable::createFromMutable($createdDateTime));
                }

                // More varied notes
                if ($faker->boolean(40)) {
                    $noteOptions = [
                        'Priority delivery requested',
                        'Leave at back gate',
                        'Call on arrival',
                        'Contact security first',
                        'Deliver between 8-10 AM',
                        'Weekend delivery preferred',
                        'Urgent order',
                    ];
                    $order->setNotes($faker->randomElement($noteOptions));
                }

                $manager->persist($order);
            }
        }

        $manager->flush();
    }
}
