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
use RuntimeException;

class ArrayAggregationCommand extends ContainerAwareCommand {
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
		$this->setName('demo:array_aggregation');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
		$this->input = $input;
		$this->output = $output;
		$this->linkRepo = $this->em->getRepository('AppBundle:Link');
		$this->linkClickRepo = $this->em->getRepository('AppBundle:LinkClick');

		// Prep data with the profiler off, we're not interested in querying efficiency for this test
		$this->output->writeln("Fetching clicks...");
		$clicks = $this->linkClickRepo->createQueryBuilder('lc')
			->setMaxResults(100000)
//			->setMaxResults(500)
			->getQuery()
			->getResult();

		$this->output->writeln("Fetching links...");
		$links = $this->linkRepo->createQueryBuilder('l')
			->getQuery()
			->getResult();

		$this->output->writeln("Fetching user ids...");
		$userIds = $this->linkRepo->createQueryBuilder('l')
			->select('DISTINCT l.userId')
			->getQuery()
			->getScalarResult();
		$userIds = array_map(function($userIdArray) {
			return $userIdArray['userId'];
		}, $userIds);

		$this->output->writeln("Profiling main function...");
		ProfilingTools::activateXhprof();

//		$results = $this->getClicksPerUserNaive($links, $clicks, $userIds);
//		$results = $this->getClicksPerUserHashLinkUserIds($links, $clicks, $userIds);
		$results = $this->getClicksPerUserHashLinkUserIds($links, $clicks, $userIds);

		ProfilingTools::storeXhprof();

		$count = count($results);
		$this->output->writeln("Returned {$count} results");
	}

	/**
	 * @param Link[] $links
	 * @param LinkClick[] $clicks
	 * @param int[] $userIds
	 * @return mixed[]
	 */
	protected function getClicksPerUserNaive($links, $clicks, $userIds) : array
	{
		$clicksPerUser = [];
		foreach ($clicks as $click) {
			$userId = $this->getClickUserId($click, $links);
			if (in_array($userId, $userIds)) {
				if (!isset($clicksPerUser[$userId])) {
					$clicksPerUser[$userId] = [];
				}
				$clicksPerUser[$userId][] = $click;
			}
		}
		return $clicksPerUser;
	}

	/**
	 * @param LinkClick $click
	 * @param Link[] $links
	 * @return int
	 */
	protected function getClickUserId($click, $links)
	{
		foreach ($links as $link) {
			if ($link->getId() == $click->getLink()->getId()) {
				return $link->getUserId();
			}
		}
		throw new RuntimeException("Couldn't find user id for click {$click->getId()}");
	}

	/**
	 * @param Link[] $links
	 * @param LinkClick[] $clicks
	 * @param int[] $userIds
	 * @return mixed[]
	 */
	protected function getClicksPerUserHashLinkUserIds($links, $clicks, $userIds) : array
	{
		$clicksPerUser = [];
		$userIdsByLink = [];
		foreach ($links as $link) {
			$userIdsByLink[$link->getId()] = $link->getUserId();
		}
		foreach ($clicks as $click) {
			$linkId = $click->getLink()->getId();
			$userId = $userIdsByLink[$linkId] ?? null;
			if ($userId === null) {
				throw new RuntimeException("Couldn't find userId mapping for link {$linkId}");
			}
			if (in_array($userId, $userIds)) {
				if (!isset($clicksPerUser[$userId])) {
					$clicksPerUser[$userId] = [];
				}
				$clicksPerUser[$userId][] = $click;
			}
		}
		return $clicksPerUser;
	}

	/**
	 * @param Link[] $links
	 * @param LinkClick[] $clicks
	 * @param int[] $userIds
	 * @return mixed[]
	 */
	protected function getClicksPerUserHashAvoidInArray($links, $clicks, $userIds) : array
	{
		$clicksPerUser = [];
		$userIdsByLink = [];
		foreach ($links as $link) {
			$userIdsByLink[$link->getId()] = $link->getUserId();
		}
		$userIdSet = array_flip($userIds);
		foreach ($clicks as $click) {
			$linkId = $click->getLink()->getId();
			$userId = $userIdsByLink[$linkId] ?? null;
			if ($userId === null) {
				throw new RuntimeException("Couldn't find userId mapping for link {$linkId}");
			}
			if (isset($userIdSet[$userId])) {
				if (!isset($clicksPerUser[$userId])) {
					$clicksPerUser[$userId] = [];
				}
				$clicksPerUser[$userId][] = $click;
			}
		}
		return $clicksPerUser;
	}
}
