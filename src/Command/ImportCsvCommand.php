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
            ->addOption('separator', 'sep', InputOption::VALUE_REQUIRED, 'CSV separator.', ';');
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
        if (!$data && !is_array($data)) {
            $output->writeln('Your file cannot be opened.');
            return Command::FAILURE;
        }

        $output->writeln('Nb. Data inserted  : ' . $this->report['success']);
        $output->writeln('Nb. Data failure   : ' . $this->report['error']);
        $output->writeln('Rows lines failure : ' . join(',', $this->report['affected_rows']));

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

        $handle = fopen($path, 'r+');
        if ($handle === FALSE) {
            return false;
        }

        $returnData = [];
        $headers = [];
        $lineNumber = 0;

        while ($rowData = fgetcsv($handle, null, $separator)) {

            if ($lineNumber == 0) {
                $headers = $rowData;
                $lineNumber++;
                continue;
            }

            $rowReturnData = [];
            foreach ($rowData as $i => $cellData) {

                $header = $headers[$i];
                if (!$this->isValidHeader($header)) {
                    continue;
                }

                if ($header == 'insee' && !$this->isValidInsee($cellData)) {
                    $this->report['error']++;
                    break;
                }

                if ($header == 'telephone' && !$this->isValidPhone($cellData)) {
                    $this->report['error']++;
                    break;
                }

                $rowReturnData[$header] = $cellData;
            }

            if (count($rowReturnData) == 2) {
                $returnData[] = $rowReturnData;
                $this->report['success']++;
            } else {
                $this->report['affected_rows'][] = $lineNumber;
            }

            $lineNumber++;
        }

        fclose($handle);
        return $returnData;
    }
}
