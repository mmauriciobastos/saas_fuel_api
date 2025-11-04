<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\DeliveryTruck;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_CA'); // Canadian locale
        $totalCompanies = 10;

        for ($ci = 1; $ci <= $totalCompanies; $ci++) {
            // Create Company with realistic name
            $company = new Company();
            $companyName = $faker->company() . ' ' . $faker->randomElement(['Fuel', 'Petro', 'Energy', 'Oil']);
            $company->setName($companyName);
            $company->setSlug(self::slugify($companyName));
            $manager->persist($company);

            // Users per company
            for ($u = 1; $u <= 2; $u++) {
                $user = new User();
                $user->setEmail($faker->unique()->safeEmail());
                $user->setCompany($company);
                $user->setFirstName($faker->firstName());
                $user->setLastName($faker->lastName());
                // Make the first user an admin to help testing
                if ($u === 1) {
                    $user->setRoles(['ROLE_ADMIN']);
                }
                $hashed = $this->passwordHasher->hashPassword($user, 'password');
                $user->setPassword($hashed);
                $manager->persist($user);
                $this->addReference(sprintf('company_%d_user_%d', $ci, $u), $user);
            }

            // Clients per company (up to 100)
            $clientsPerCompany = random_int(80, 100);
            for ($c = 1; $c <= $clientsPerCompany; $c++) {
                $client = new Client();
                $client->setName($faker->company());
                $client->setEmail($faker->unique()->companyEmail());
                $client->setPhone($faker->phoneNumber());
                $client->setAddress($faker->streetAddress());
                $client->setCity($faker->city());
                // Canadian province abbreviations
                $provinces = ['AB','BC','MB','NB','NL','NS','NT','NU','ON','PE','QC','SK','YT'];
                $client->setStateProvince($faker->randomElement($provinces));
                $client->setPostalCode($faker->postcode());
                $client->setCompany($company);
                $manager->persist($client);
                $this->addReference(sprintf('company_%d_client_%d', $ci, $c), $client);
            }

            // Delivery trucks per company
            $truckModels = ['Volvo FM', 'MAN TGS', 'Scania R', 'Mercedes-Benz Actros', 'Iveco Stralis'];
            for ($t = 1; $t <= 2; $t++) {
                $truck = new DeliveryTruck();
                $truck->setLicensePlate($faker->bothify('??-####'));
                $truck->setModel($faker->randomElement($truckModels));
                $truck->setDriverName($faker->name());
                $truck->setCurrentFuelLevel($faker->randomFloat(2, 10, 90));
                $truck->setStatus($faker->randomElement(['available', 'in_use', 'maintenance']));
                $truck->setCompany($company);
                $manager->persist($truck);
                $this->addReference(sprintf('company_%d_truck_%d', $ci, $t), $truck);
            }

            // Save a reference for potential use in other fixtures
            $this->addReference('company_' . $ci, $company);
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
