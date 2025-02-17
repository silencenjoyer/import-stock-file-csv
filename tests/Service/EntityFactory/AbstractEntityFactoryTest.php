<?php

namespace App\Tests\Service\EntityFactory;

use App\Service\EntityFactory\AbstractEntityFactory;
use App\Service\Event\InvalidEntityEvent;
use App\ValueObject\FileHeaders;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AbstractEntityFactoryTest extends KernelTestCase
{
    private AbstractEntityFactory $factory;
    private EventDispatcherInterface $dispatcher;

    /**
     * {@inheritDoc}
     *
     * This method creates a test implementation of the {@see AbstractEntityFactory} to verify the abstraction logic.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $container = self::getContainer();

        $validator = $container->get('validator');
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->factory = new class($validator, $this->dispatcher) extends AbstractEntityFactory {
            protected function populate(array $row, FileHeaders $headers): object
            {
                $entity = new class {
                    #[Assert\NotBlank]
                    #[Assert\Type('integer')]
                    #[Assert\Positive]
                    private ?int $id = null;
                    #[Assert\NotBlank]
                    #[Assert\Type('string')]
                    #[Assert\Length(max: 10)]
                    private ?string $name = null;

                    public function getId(): ?int
                    {
                        return $this->id;
                    }

                    public function setId(int $id): static
                    {
                        $this->id = $id;
                        return $this;
                    }

                    public function getName(): ?string
                    {
                        return $this->name;
                    }

                    public function setName(string $name): static
                    {
                        $this->name = $name;
                        return $this;
                    }
                };

                $entity->setId($row['id']);
                $entity->setName($row['name']);

                return $entity;
            }
        };
    }

    /**
     * Ensures successful creation without triggering an invalid entity event.
     *
     * @dataProvider getValidData
     * @param array $row
     * @return void
     */
    public function testCreateFromValidData(array $row): void
    {
        $this->dispatcher->expects($this->never())
            ->method('dispatch')
            ->with($this->callback(fn (InvalidEntityEvent $event) => true));

        $entity = $this->factory->create($row, $this->createMock(FileHeaders::class));

        $this->assertNotNull($entity);
    }

    /**
     * Ensures failed creation with triggering an invalid entity event.
     *
     * @dataProvider getInvalidData
     * @param array $row
     * @return void
     */
    public function testCreateFromInvalidValidData(array $row): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (InvalidEntityEvent $event) => true));

        $entity = $this->factory->create($row, $this->createMock(FileHeaders::class));

        $this->assertNull($entity);
    }

    /**
     * Data provider of correct row examples.
     *
     * @return array[]
     */
    public function getValidData(): array
    {
        return [
            [
                [
                    'id' => 1,
                    'name' => 'Alex',
                ]
            ],
            [
                [
                    'id' => 2,
                    'name' => 'Tester',
                ]
            ]
        ];
    }

    /**
     * Data provider of incorrect row examples.
     *
     * @return array[]
     */
    public function getInvalidData(): array
    {
        return [
            [
                [
                    'id' => 1,
                    'name' => 'AlexAlexAlexAlexAlexAlexAlexAlexAlexAlexAlexAlex',
                ]
            ],
            [
                [
                    'id' => -1,
                    'name' => 'Tester',
                ]
            ]
        ];
    }
}
