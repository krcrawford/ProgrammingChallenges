#!/usr/bin/env php
<?php
// combine-csv.php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Command\CsvCombinerCommand;

$app = new Application('app', '1.0.0');
$app->register('app')
    ->addArgument(
        'files',
        InputArgument::IS_ARRAY
    )
    ->setCode(function(InputInterface $input, OutputInterface $output) {
        $files = $input->getArgument('files');
        $command = $this->getApplication()->find('csv-combiner');
        $arguments = [
            'files' => $files,
        ];
        $csvCombinerInput = new ArrayInput($arguments);
        $command->run($csvCombinerInput, $output);
    });
$app->add(new CsvCombinerCommand());
$app->setDefaultCommand('app', true);
$app->run();
