<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-csv',
    description: 'Import an CSV that must include "insee" and "telephone" columns',
    hidden: false,
    aliases: ['app:import-csv']
)]
class ImportCsvCommand extends Command
{

    /** @var array{success:int,failure:array<int,string>} */
    private array $report = [
        'success' => 0,
        'failure' => [],
    ];

    private Connection $db;

    /** @var string[] */
    private array $validHeaders = ['insee', 'telephone'];

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->db = $connection;
    }

    private function isValidHeader(string $header): bool
    {
        return in_array($header, $this->validHeaders);
    }

    /**
     * Verify if the insee has 5 digit.
     */
    private function isValidInsee(string $insee): bool
    {
        return preg_match('/^\d{5}$/', $insee);
    }

    function isValidPhone(string $phone): bool
    {
        return preg_match('/^0[1-9]\d{8}$/', $phone);
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to import a CSV file that have "insee" and "telephone" columns.')
            ->addArgument('path', InputArgument::REQUIRED, 'The path of the CSV file.')
            ->addOption('separator', 'sep', InputOption::VALUE_REQUIRED, 'CSV separator.', ';')
            ->addOption('error-detail', 'err', InputOption::VALUE_NONE, 'To see wich line is ignored and why');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $path = $input->getArgument('path');
        $separator = $input->getOption('separator');
        $errorDetail = $input->getOption('error-detail');

        if (!$this->isValidCsv($path)) {
            $output->writeln('Your file is not a valid CSV.');
            return Command::FAILURE;
        }

        $data = $this->insertData($path, $separator);
        if (!$data) {
            $output->writeln('Your file cannot be opened.');
            return Command::FAILURE;
        }


        if ($errorDetail) {
            $output->writeln('ERROR DETAIL');
            foreach ($this->report['failure'] as $lineNumber => $error) {
                $output->writeln(' - ' . $lineNumber . ' : ' . $error);
            }
            $output->writeln('------------------------');
        }


        $output->writeln('Nb. Data inserted  : ' . $this->report['success']);
        $output->writeln('Nb. Data failure   : ' . count($this->report['failure']));

        return Command::SUCCESS;
    }

    private function isValidCsv(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        if (pathinfo($path, PATHINFO_EXTENSION) !== 'csv') {
            return false;
        }

        return true;
    }

    /**
     * @param string $path
     * @param string $separator
     */
    private function insertData(string $path, string $separator = ';'): bool
    {

        $builder = $this->db->createQueryBuilder();

        $handle = fopen($path, 'r+');
        if ($handle === FALSE) {
            return false;
        }

        $headers = [];
        $lineNumber = 0;
        $sqlRows = [];

        while ($rowData = fgetcsv($handle, null, $separator)) {

            if ($lineNumber == 0) {
                $headers = $rowData;
                $lineNumber++;
                continue;
            }

            $insertData = [];
            foreach ($rowData as $i => $cellData) {

                $header = $headers[$i];
                if (!$this->isValidHeader($header)) {
                    continue;
                }

                if ($header == 'insee' && !$this->isValidInsee($cellData)) {
                    $this->report['failure'][$lineNumber] = 'Invalid Insee';
                    break;
                }

                if ($header == 'telephone' && !$this->isValidPhone($cellData)) {
                    $this->report['failure'][$lineNumber] = 'Invalid Telephone';
                    break;
                }

                $insertData[$header] = $cellData;
            }

            if (count($insertData) == 2) {
                try {
                    $builder->insert('recipient')
                        ->values(['insee' => '?', 'telephone' => '?'])
                        ->setParameters([0 => $insertData['insee'], 1 => $insertData['telephone']])
                        ->executeStatement();

                    $this->report['success']++;
                } catch (Exception $e) {
                    $code = $e?->getPrevious()?->getPrevious()?->getCode() ?? null;

                    if ($code == '23505') {
                        $this->report['failure'][$lineNumber] = 'Couple insee + telephone already exists';
                    } else {
                        $this->report['failure'][$lineNumber] = 'Server Error';
                    }
                }
            }

            $lineNumber++;
        }

        fclose($handle);
        return true;
    }
}
