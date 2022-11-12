<?php

namespace App\Command;

use App\Entity\Stock;
use Doctrine\ORM\EntityManagerInterface;
use App\Contracts\FinanceApiClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:refresh-stock-profile',
    description: 'Retrieve a stock profile from the Yahoo Finance API. Update the record in the DB',
)]
class RefreshStockProfileCommand extends Command
{

    public function __construct(private EntityManagerInterface $entityManager, private FinanceApiClientInterface $financeApiClient)
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

        // Ping the Yahoo Finance API and grab the response (a stock profile)
        $stockProfile = $this->financeApiClient->fetchStockProfile($input->getArgument('symbol'),$input->getArgument('region'));

        if($stockProfile->getStatusCode() !== 200){

            return Command::FAILURE;
        }

        $stock = $this->serializer->deserialize($stockProfile->getContent(), Stock::class, 'json');

        // Use the stock profile from the response to create a record if it does not exist

        $this->entityManager->persist($stock);

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}