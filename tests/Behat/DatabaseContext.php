<?php

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use PHPUnit\Framework\Assert;

class DatabaseContext implements Context
{
    private SchemaTool $schemaTool;

    public function __construct(
        private EntityManagerInterface $manager,
        private SymfonyFixturesLoader $fixturesLoader
    ) {
        $this->schemaTool = new SchemaTool($this->manager);
    }

    /**
     * @BeforeScenario @createSchema
     *
     * @throws ToolsException
     */
    public function createDatabase(): void
    {
        $classes = $this->manager->getMetadataFactory()->getAllMetadata();

        $this->schemaTool->dropSchema($classes);
        $this->schemaTool->createSchema($classes);
        foreach ($this->fixturesLoader->getFixtures() as $fixture) {
            $fixture->load($this->manager);
        }

        $this->manager->clear();
    }

    /**
     * @BeforeScenario @loadFixtures
     */
    public function loadFixtures(): void
    {
        foreach ($this->fixturesLoader->getFixtures() as $fixture) {
            $fixture->load($this->manager);
        }
    }

    /**
     * @Then :className entities should exist in the database with the following data:
     *
     * @param class-string<object> $className
     */
    public function entitiesShouldExistsInTheDatabaseWithTheFollowingData(string $className, TableNode $table): void
    {
        $metadata = $this->manager->getMetadataFactory()->getAllMetadata();
        foreach ($metadata as $classMetadata) {
            if (str_ends_with($classMetadata->getName(), '\\'.$className)) {
                $className = $classMetadata->getName();
            }
        }
        $repository = $this->manager->getRepository($className);

        $parameters = $table->getRow(0);
        foreach ($table->getRows() as $rowNum => $row) {
            if (0 === $rowNum) { // header row
                continue;
            }
            $criteria = [];
            $i = 0;
            foreach ($parameters as $parameter) {
                $criteria[$parameter] = $row[$i];
                ++$i;
            }
            $object = $repository->findOneBy($criteria);
            Assert::assertNotNull($object);
        }
    }
}
