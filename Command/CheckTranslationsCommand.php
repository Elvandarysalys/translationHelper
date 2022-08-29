<?php

namespace Elvandar\TranslationHelper\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

//#[AsCommand(
//    name: 'check:translations',
//    description: 'Analyse your translation folder and give you informations about translation progress',
//)]
class CheckTranslationsCommand extends Command
{
    protected static $defaultName = 'check:translations';
    protected static $defaultDescription = 'Analyse your translation folder and give you informations about translation progress';

    protected string $translatorFolder;

    public function __construct(string $translatorFolder)
    {
        parent::__construct();
        $this->translatorFolder = $translatorFolder;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $outputStyle = new OutputFormatterStyle('red', '', ['bold', 'blink']);
        $output->getFormatter()->setStyle('fire', $outputStyle);

        $displayFullResults = $input->getOption('verbose');

        $io->writeln("checking for translations in $this->translatorFolder");
        $io->newLine();

        $finder = new Finder();
        $finder->files()->in($this->translatorFolder);

        $results      = [];
        $totalUnits   = 0;
        $invalidUnits = 0;
        if ($finder->hasResults()) {
            $io->writeln($finder->count() . ' result founds');
            $io->newLine();

            $encoders   = [new XmlEncoder()];
            $serializer = new Serializer([], $encoders);

            foreach ($finder as $file) {
                $fileData = $serializer->decode($file->getContents(), 'xml');

                $units     = $fileData['file']['body']['trans-unit'];
                $unitCount = count($units);

                $totalUnits += $unitCount;

                $fileInvalid = 0;
                foreach ($io->progressIterate($units) as $transUnit) {
                    if (array_key_exists('source', $transUnit)) {
                        $theoricalUntranslated = '__' . $transUnit['source'];

                        if ($theoricalUntranslated === $transUnit['target']) {
                            $invalidUnits++;
                            $fileInvalid++;
                        }
                    }
                }

                if ($displayFullResults) {
                    $results[$file->getFilenameWithoutExtension()] = [
                        'percentCompletion' => round((($unitCount - $fileInvalid) / $unitCount) * 100, 2),
                        'invalid'           => $fileInvalid,
                        'total'             => $unitCount
                    ];
                }

                $io->newLine();
            }
        }

        $validUnits        = $totalUnits - $invalidUnits;
        $completionPercent = round(($validUnits / $totalUnits) * 100, 2);

        $io->newLine();
        if ($displayFullResults){
            $io->writeln("<comment>$totalUnits</comment> analysed, <info>$validUnits</info> valid units, <fire>$invalidUnits</fire> invalid units");
        }
        $io->writeln("translation completed at $completionPercent%.");
        $io->newLine();

        if ($displayFullResults) {
            foreach ($results as $fileName => $fileResult) {
//                $io->writeln('<info>' . $fileName . '</info>');
                $io->section($fileName);
                $valid   = $fileResult['total'] - $fileResult['invalid'];
                $total   = $fileResult['total'];
                $invalid = $fileResult['invalid'];
                $completion = $fileResult['percentCompletion'];

                $io->writeln("$total analysed");
                $io->writeln("$valid valid units");
                $io->writeln("$invalid invalid units");
                $io->writeln("translation completed at $completion%");
                $io->newLine();
            }
        }

        return Command::SUCCESS;
    }
}
