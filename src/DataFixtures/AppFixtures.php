<?php

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\CustomField;
use App\Entity\Doc;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private $baseDateTime;

    public function __construct()
    {
        $this->baseDateTime = \DateTime::createFromFormat('d-m-Y H:i', '01-01-2019 00:01');
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i < 6; $i++) {
            $contact = new Contact();
            $contact->setName("Name$i");
            $contact->setSurname("Surname$i");
            $contact->setCustomFields(['address' => "address $i"]);
            $date = clone $this->baseDateTime;
            $contact->setCreateAt($date->sub(new \DateInterval("P{$i}D")));
            if ($i % 2 === 0) {
                $contact->setDeleteAt($this->baseDateTime);
            }

            $manager->persist($contact);
            $this->createDocs($contact, $manager);
        }
        $this->createCustomFields($manager);

        $manager->flush();
    }

    protected function createDocs(Contact $contact, ObjectManager $manager)
    {
        for ($i = 1; $i < 4; $i++) {
            $doc = new Doc();
            $doc->setNumber("number $i". $contact->getId());
            $doc->setContact($contact);
            if ($i % 2 === 0) {
                $doc->setDeleteAt($this->baseDateTime);
            }
            $date = clone $this->baseDateTime;
            $doc->setCreateAt($date->sub( new \DateInterval("P{$i}D")));

            $manager->persist($doc);
        }

    }

    protected function createCustomFields(ObjectManager $manager)
    {
        $field = new CustomField();
        $field->setType('text');
        $field->setName('address');
        $field->setDefaultValue('');
        $manager->persist($field);
    }

}
