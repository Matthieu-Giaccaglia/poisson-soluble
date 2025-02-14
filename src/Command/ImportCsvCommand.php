<?php

namespace App\Command;

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

    private array $report = [
        'success' => 0,
        'error' => 0,
        'affected_rows' => [],
    ];

    private array $validHeaders = ['insee', 'telephone'];

    private function isValidHeader(string $header): bool
    {
        return in_array($header, $this->validHeaders);
    }

    private function isValidInsee(string $insee): bool
    {
        return preg_match('/^\d{13}(\d{2})?$/', $insee);
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
            ->addOption('separator', 'sep', InputOption::VALUE_REQUIRED, 'CSV separator, by default ";"', ';');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $path = $input->getArgument('path');
        $separator = $input->getOption('separator');

        if (!$this->isValidCsv($path)) {
            $output->writeln('Your file is not a valid CSV.');
            return Command::FAILURE;
        }

        $data = $this->getCsvData($path, $separator);
        if (!$data) {
            $output->writeln('Your file cannot be opened.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function isValidCsv(string $path)
    {
        if (!file_exists($path)) {
            return false;
        }

        if (pathinfo($path, PATHINFO_EXTENSION) !== 'csv') {
            return false;
        }

        return true;
    }

    private function getCsvData(string $path, string $separator = ';'): false|array
    {

        // $handle = fopen($path, "r");
        $fileContents = file_get_contents($path, true);
        $csvData = str_getcsv($fileContents, $separator);

        $returnData = [];

        foreach ($csvData as $i => $rowString) {
            var_dump($rowString);
        }


        // if ($handle === FALSE) {
        //     return false;
        // }

        $csvData = [];
        $headers = [];
        $lineNumber = 0;
        
        // while ($data = fgetcsv($handle, null, $separator)) {
        //     if ($lineNumber == 0) {
        //         $headers = $data;
        //         continue;
        //     }

        //     $rowData = [];
        //     foreach ($data as $i => $cellData) {

        //         $header = $headers[$i];
        //         if (!$this->isValidHeader($header)) {
        //             continue;
        //         }

        //         if ($header == 'insee' && !$this->isValidInsee($cellData)) {
        //             $this->report['error']++;
        //             break;
        //         }

        //         if ($header == 'telephone' && !$this->isValidPhone($cellData)) {
        //             $this->report['error']++;
        //             break;
        //         }

        //         $rowData[$header] = $cellData;
        //     }

        //     if (count($rowData) == 2) {
        //         $csvData[] = $rowData;
        //     } else {
        //         $this->report['affected_rows'][] = $lineNumber;
        //     }

        //     $lineNumber++;
        // }

        // fclose($handle);
        return $csvData;
    }
}
