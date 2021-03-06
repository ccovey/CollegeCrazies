<?php

namespace SofaChamps\Bundle\BowlPickemBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzeLeagueCommand extends ContainerAwareCommand
{
    protected $analyzer;
    protected $leagues;

    protected function configure()
    {
        $this->setName('bowl-pickem:analyze-picksets')
            ->setDescription('Analyize all picksets in their respective leagues')
            ->addArgument('season', InputArgument::REQUIRED, 'The season to analyze')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->analyzer = $this->getContainer()->get('sofachamps.bp.pickset_analyzer');
        $this->leagues = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('SofaChampsBowlPickemBundle:League')->findBy(array(
            'season' => $input->getArgument('season'),
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->analyzer->deleteAnalysis($input->getArgument('season'));
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, count($this->leagues));
        foreach ($this->leagues as $league) {
            $this->analyzer->analyizeLeaguePickSets($league, $input->getArgument('season'));
            $progress->advance();
        }
        $progress->finish();
    }
}
