<?php
// src/Command/IndexFilmsCommand.php

namespace App\Command;

use App\Service\Search\FilmSearchService;
use App\Repository\FilmRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'es:index', description: 'Index entities in Elasticsearch')]
class IndexFilmsCommand extends Command
{
    public function __construct(
        private FilmSearchService $filmSearchService,
        private FilmRepository $filmRepository
    ) {
        parent::__construct();
    }

    // protected function execute(InputInterface $input, OutputInterface $output): int
    // {
    //     $io = new SymfonyStyle($input, $output);

    //     $films = $this->filmRepository->findAll();
    //     $total = count($films);

    //     $io->progressStart($total);

    //     foreach ($films as $film) {
    //         $this->filmSearchService->indexFilm($film);
    //         $io->progressAdvance();
    //     }

    //     $io->progressFinish();
    //     $io->success("Indexed: {$total} films");

    //     return Command::SUCCESS;
    // }
}
