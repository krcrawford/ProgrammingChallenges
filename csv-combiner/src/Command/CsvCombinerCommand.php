<?php

declare(strict_types=1);

namespace App\Command;

use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\InvalidArgumentException;

class CsvCombinerCommand extends Command
{
    private const ALLOWED_FILE_TYPES = ['csv'];

    protected function configure()
    {
        $this
            ->setName('csv-combiner')
            ->addArgument(
                'files',
                InputArgument::IS_ARRAY
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $args = $input->getArgument('files');

        // let's do some validation before we start the real work
        try {
            foreach ($args as $current) {
                $file = $current;
                if (file_exists($file)) {
                    // validate file, check for symlinks, etc...
                    if (is_link($file)) {
                        $file = readlink($file);
                        if (!file_exists($file)) {
                            throw new FileNotFoundException(null, 0, null, $file);
                        }
                    }
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                    if (!in_array($extension, self::ALLOWED_FILE_TYPES)) {
                        throw new InvalidArgumentException($file.' is not a file with a csv extension');
                    }
                } else {
                    throw new FileNotFoundException(null, 0, null, $file);
                }
            }
        } catch (InvalidArgumentException | FileNotFoundException $fnfe) {
            // let's provide some feedback
            $output->writeln($fnfe->getMessage());
            // and then exit with an error code
            exit(1);
        }

        $isHeaderSet = false;

        foreach ($args as $current) {
            $file = $current;
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            // the choice here is for a mature solution for stream reading
            // (preventing running out of memory)
            $csv = Reader::createFromPath($file, 'r');
            $csv->setHeaderOffset(0);
            // assuming same columns for all inputs, we only output the header
            // once
            if (!$isHeaderSet) {
                $isHeaderSet = true;
                $output->writeln(implode(',', $csv->getHeader()).',filename');
            }
            // get an iterator and plow through the records
            foreach ($csv->getRecords() as $record) {
                $line = implode(',', $record).','.$fileName.'.csv';
                $output->writeln($line);
            }
        }
    }
}
