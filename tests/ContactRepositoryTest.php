<?php

namespace App\Tests;

use App\Entity\Contact;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ContactRepositoryTest extends KernelTestCase
{
    private $entityManager;
    protected function setUp()
    {
        $kernel  = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

    }

    public function testFindAllContactsWithLatestDoc()
    {
        $contracts = $this->entityManager->getRepository(Contact::class)->getAllWithLatestDoc();
        $this->assertEquals(count($contracts), 3);
        $this->assertEquals($contracts[0]['contact_id'], '1');
        $this->assertEquals($contracts[1]['contact_id'], '3');
        $this->assertEquals($contracts[2]['contact_id'], '5');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
