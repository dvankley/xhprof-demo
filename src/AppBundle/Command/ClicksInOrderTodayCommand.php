<?php

namespace AppBundle\Command;

use AppBundle\Entity\Link;
use AppBundle\Entity\LinkClick;
use AppBundle\Managers\ProfilingTools;
use AppBundle\Managers\SlugManager;
use AppBundle\Managers\WordManager;
use AppBundle\Repository\LinkClickRepository;
use AppBundle\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use DateTime;

class ClicksInOrderTodayCommand extends ContainerAwareCommand {
	/** @var InputInterface */
	protected $input;
	/** @var OutputInterface */
	protected $output;
	/** @var EntityManager */
	protected $em;
	/** @var WordManager */
	protected $wordManager;
	/** @var LinkRepository */
	protected $linkRepo;
	/** @var LinkClickRepository */
	protected $linkClickRepo;

	protected function configure()
	{
		$this->setName('demo:clicks_by_user_today');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
		$this->input = $input;
		$this->output = $output;
		$this->linkRepo = $this->em->getRepository('AppBundle:Link');
		$this->linkClickRepo = $this->em->getRepository('AppBundle:LinkClick');

		ProfilingTools::activateXhprof();

//		$results = $this->straightQuery();
//		$results = $this->aggregateInPhp();
		$results = $this->aggregateInPhpScalar();
		$count = count($results);

		$this->output->writeln("Returned {$count} results");

		ProfilingTools::storeXhprof();
	}

	protected function straightQuery()
	{
		return $this->linkClickRepo->createQueryBuilder('lc')
			->where('lc.createdAt > :now')
			->setParameter(':now', new DateTime('-1 day'))
			->orderBy('lc.createdAt', 'DESC')
			->getQuery()
			->getResult();
	}

	protected function aggregateInPhp()
	{
		$results = $this->linkClickRepo->createQueryBuilder('lc')
			->where('lc.createdAt > :now')
			->setParameter(':now', new DateTime('-1 day'))
			->getQuery()
			->getResult();

		usort($results, function($a, $b) {
			/**
			 * @var LinkClick $a
			 * @var LinkClick $b
			 */
			return $a->getCreatedAt() > $b->getCreatedAt();
		});

		return $results;
	}

	protected function aggregateInPhpScalar()
	{
		$results = $this->linkClickRepo->createQueryBuilder('lc')
			->where('lc.createdAt > :now')
			->setParameter(':now', new DateTime('-1 day'))
			->getQuery()
			->getScalarResult();

		usort($results, function($a, $b) {
			/**
			 * @var LinkClick $a
			 * @var LinkClick $b
			 */
			return $a['lc_createdAt'] > $b['lc_createdAt'];
		});

		return $results;
	}
}
