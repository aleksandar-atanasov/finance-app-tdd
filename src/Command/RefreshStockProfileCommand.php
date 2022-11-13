<?php

namespace App\Command;

use App\Entity\Stock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\Table;
use App\Contracts\FinanceApiClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[AsCommand(
    name: 'app:refresh-stock-profile',
    description: 'Retrieve a stock profile from the Yahoo Finance API. Update the record in the DB',
)]
class RefreshStockProfileCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FinanceApiClientInterface $financeApiClient,
        private SerializerInterface $serializer,
        private LoggerInterface $logger
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock symbol e.g. AMZN for Amazon')
            ->addArgument('region', InputArgument::REQUIRED, 'The region of the company e.g. US for United States');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {
            // Ping the Yahoo Finance API and grab the response (a stock profile)
            $stockProfile = $this->financeApiClient->fetchStockProfile($input->getArgument('symbol'),$input->getArgument('region'));

            if($stockProfile->getStatusCode() !== 200){

                $output->writeln("<error>{$stockProfile->getContent()}<error>");

                return Command::FAILURE;
            }

            $symbol = json_decode($stockProfile->getContent())->symbol ?? null;

            if($stock = $this->entityManager->getRepository(Stock::class)->findOneBy(['symbol' => $symbol])){
                //update the existing stock profile in the DB
                $this->serializer->deserialize(
                    $stockProfile->getContent(),
                    Stock::class,
                    'json',
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $stock]
                );

            }else{

                /**
                 * @var Stock $stock
                 */
                $stock = $this->serializer->deserialize($stockProfile->getContent(), Stock::class, 'json');
            }

            // Use the stock profile from the response to create a record if it does not exist

            $this->entityManager->persist($stock);

            $this->entityManager->flush();

            $output->writeln("<info>{$stock->getShortName()} has been saved/updated<info>");

            $table = new Table($output);

            $table
                ->setHeaders(['Short Name', 'Previous Close', 'Price', 'Price Change'])
                ->setRows([
                    [
                        $stock->getShortName(),
                        $stock->getPreviousClose(),
                        $stock->getPrice(),
                        $stock->getPriceChange()
                    ]
                ]);

            $table->render();

            return Command::SUCCESS;

        } catch (\Exception $exception) {
            // Log everything and learn
            $this->logger->warning(get_class($exception) . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine()
            . ' using [symbol/region] ' . '[' . $input->getArgument('symbol') . '/' . $input->getArgument('region') . ']');

            return Command::FAILURE;
        }
    }
}