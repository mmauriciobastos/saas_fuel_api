<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\DeliveryTruck;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Seed some Companies
        $names = [
            'Acme Fuel Co',
            'Northstar Petro',
            'BlueRiver Energy',
            'Atlas Oil Group',
            'GreenLeaf Petroleum',
            'Sunrise Fuel Logistics',
            'Vanguard Petro Supply',
            'Coastline Energy Services',
            'PeakFlow Fuel',
            'Unity Petro Partners',
        ];

        $domain = 'local.com';

        foreach ($names as $idx => $name) {
            $companyIndex = $idx + 1;
            $company = new Company();
            $company->setName($name);
            $company->setSlug(self::slugify($name));
            $manager->persist($company);

            // Users per company
            for ($u = 1; $u <= 2; $u++) {
                $user = new User();
                $email = sprintf('user%02d_c%02d@%s', $u, $companyIndex, $domain);
                $user->setEmail($email);
                $user->setCompany($company);
                // Make the first user an admin to help testing
                if ($u === 1) {
                    $user->setRoles(['ROLE_ADMIN']);
                }
                $hashed = $this->passwordHasher->hashPassword($user, 'password');
                $user->setPassword($hashed);
                $manager->persist($user);
                $this->addReference(sprintf('company_%d_user_%d', $companyIndex, $u), $user);
            }

            // Clients per company
            for ($c = 1; $c <= 5; $c++) {
                $client = new Client();
                $client->setName(sprintf('Client %02d C%02d', $c, $companyIndex));
                $client->setEmail(sprintf('client%02d_c%02d@%s', $c, $companyIndex, $domain));
                $client->setPhone(sprintf('+1-555-%04d', $companyIndex * 100 + $c));
                $client->setAddress(sprintf('%d%d%d Main St', $companyIndex, $c, rand(1,9)));
                $client->setCity(['Springfield', 'Riverton', 'Oakdale', 'Lakeside'][array_rand(['Springfield', 'Riverton', 'Oakdale', 'Lakeside'])]);
                $client->setZipCode(str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT));
                $client->setCompany($company);
                $manager->persist($client);
                $this->addReference(sprintf('company_%d_client_%d', $companyIndex, $c), $client);
            }

            // Delivery trucks per company
            for ($t = 1; $t <= 2; $t++) {
                $truck = new DeliveryTruck();
                $truck->setLicensePlate(sprintf('C%02d-T%02d-%03d', $companyIndex, $t, rand(100, 999)));
                $truck->setModel(['Volvo FM', 'MAN TGS', 'Scania R'][array_rand(['Volvo FM', 'MAN TGS', 'Scania R'])]);
                $truck->setDriverName(sprintf('Driver %02d C%02d', $t, $companyIndex));
                $truck->setCurrentFuelLevel((float) rand(100, 900) / 10.0);
                $truck->setStatus('available');
                $truck->setCompany($company);
                $manager->persist($truck);
                $this->addReference(sprintf('company_%d_truck_%d', $companyIndex, $t), $truck);
            }

            // Save a reference for potential use in other fixtures
            $this->addReference('company_' . $companyIndex, $company);
        }

        $manager->flush();
    }

    private static function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        return $text ?: 'n-a';
    }
}
