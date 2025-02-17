<?php

namespace App\Command;

use App\Entity\Product;
use App\Enum\StockFileColumnEnum;
use App\Service\Event\BeforePersistInStorageEvent;
use App\Service\EventListener\CancelSaveStorageListener;
use App\Service\Importer\FileImporterInterface;
use App\ValueObject\FileHeaders;
use App\ValueObject\ImportResult;
use Closure;
use League\Csv\SyntaxError;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:import-stock-file',
    description: 'Imports stock data from a CSV file',
    aliases: ['app:import-stock'],
)]
class ImportStockFileCommand extends Command
{
    protected const string FILE_OPTION = 'file';
    protected const string FILE_OPTION_SHORT = 'f';
    protected const string TEST_MODE_OPTION = 'test';
    protected const string TEST_MODE_OPTION_SHORT = 't';

    /**
     * @param FileHeaders $fileHeaders
     * @param FileImporterInterface $importService
     * @param EventDispatcherInterface $dispatcher
     * @param KernelInterface $kernel
     */
    public function __construct(
        #[Autowire(service: 'app.stock.import.file.headers')]
        protected FileHeaders $fileHeaders,
        #[Autowire(service: 'app.stock.import.importer')]
        protected FileImporterInterface $importService,
        protected EventDispatcherInterface $dispatcher,
        protected KernelInterface $kernel
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                self::TEST_MODE_OPTION,
                self::TEST_MODE_OPTION_SHORT,
                InputOption::VALUE_NONE,
                'Test mode without saving.'
            )
            ->addOption(
                self::FILE_OPTION,
                self::FILE_OPTION_SHORT,
                InputOption::VALUE_OPTIONAL,
                'The file path to import.',
                sprintf('%s/tests/files/stock.csv', $this->kernel->getProjectDir())
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws SyntaxError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filePath = $input->getOption(self::FILE_OPTION);

        if ($input->getOption(self::TEST_MODE_OPTION)) {
            $this->dispatcher->addListener(BeforePersistInStorageEvent::class, $this->getTestModeListener());
        }

        $result = $this->importService->import($filePath, $this->fileHeaders);

        $this->printResult($io, $output, $result);

        return Command::SUCCESS;
    }

    /**
     * Provides a listener to the event.
     * Relevant for the test mode.
     *
     * @return Closure
     *
     * @see https://www.php.net/manual/en/functions.first_class_callable_syntax.php
     */
    protected function getTestModeListener(): Closure
    {
        return (new CancelSaveStorageListener())->cancel(...);
    }

    /**
     * A method to output import results to the console.
     *
     * @param SymfonyStyle $io
     * @param OutputInterface $output
     * @param ImportResult $result
     * @return void
     */
    protected function printResult(SymfonyStyle $io, OutputInterface $output, ImportResult $result): void
    {
        if ($result->hasErrors()) {
            $io->writeln('<error>Importing errors:</error>');
            $table = new Table($output);
            $table->setHeaders(['Product', 'Column', 'Error']);

            /**
             * @var array|Product $entity
             * @var array $fields
             */
            foreach ($result->getErrors() as [$entity, $fields]) {
                foreach ($fields as $field => $message) {
                    $code = $entity instanceof Product
                        ? $entity->getProductCode()
                        : $entity[$this->fileHeaders->get(StockFileColumnEnum::CODE)];

                    $table->addRow([$code, $field, $message]);
                }
            }
            $table->render();
        }

        $io->title('Import Results');
        $io->writeln(sprintf('Processed: %d', $result->getProcessed()));
        $io->writeln(sprintf('<info>Success:</info> %d', $result->getSuccess()));
        $io->writeln(sprintf('<comment>Skipped:</comment> %d', $result->getSkipped()));
    }
}
