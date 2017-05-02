<?php

namespace AppBundle\Command;

use AppBundle\Entity\Link;
use AppBundle\Entity\LinkClick;
use AppBundle\Managers\SlugManager;
use AppBundle\Managers\WordManager;
use AppBundle\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use DateTime;

class SeedDataCommand extends ContainerAwareCommand {
	/** @var InputInterface */
	protected $input;
	/** @var OutputInterface */
	protected $output;

	protected $linksPerUserMax = 10;
	protected $clicksPerLinkMax = 10;
	protected $maxUserId = 10;
	protected $secondsPerLink = 3600;
	/** @var EntityManager */
	protected $em;
	/** @var WordManager */
	protected $wordManager;

	protected function configure()
	{
		$this
			// the name of the command (the part after "bin/console")
			->setName('seed:data')

			// the short description shown while running "php bin/console list"
			->setDescription('Populates demo data')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('RTFC')
		;

		$this->wordManager = new WordManager();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
		$this->input = $input;
		$this->output = $output;

		$this->purgeData();

		for ($userId = 0; $userId < $this->maxUserId; $userId++) {
			$this->generateUserData($userId);
		}
		$this->output->writeln("Flushing queries...");
		$this->em->flush();
		$this->output->writeln("DONE");
	}

	protected function purgeData()
	{
		$this->output->writeln("Purging old data...");
		/** @var LinkRepository $linkRepository */
		$linkRepository = $this->em->getRepository('AppBundle:Link');
		$linkClickRepository = $this->em->getRepository('AppBundle:LinkClick');

		$linkClickRepository->createQueryBuilder('lc')
			->delete()
			->getQuery()
			->execute();

		$linkRepository->createQueryBuilder('l')
			->delete()
			->getQuery()
			->execute();
	}

	protected function generateUserData(int $userId)
	{
		$userLinkCount = rand(1, $this->linksPerUserMax);
		$this->output->writeln("Generating data for {$userLinkCount} links for user {$userId}...");

		for ($linkId = 0; $linkId < $userLinkCount; $linkId++) {
			$secondOffset = $linkId * $this->secondsPerLink;
			$link = new Link();
			$link->setTargetUrl($this->generateRandomUrl($this->wordManager));
			$link->setShrunkUrlSlug(SlugManager::generateSlug());
			$link->setUserId($userId);
			$link->setCreatedAt(new DateTime("-{$secondOffset} seconds"));
			$this->em->persist($link);

			$this->generateClickData($link);
		}
	}

	protected function generateClickData(Link $link)
	{
		$clickCount = rand(1, $this->clicksPerLinkMax);
		$this->output->writeln("Generating data for {$clickCount} clicks for link {$link->getShrunkUrlSlug()}...");

		for ($clickId = 0; $clickId < $clickCount; $clickId++) {
			$secondOffset = $clickId * $this->secondsPerLink;
			$click = new LinkClick();
			$click->setLink($link);
			$click->setCreatedAt(new DateTime("-{$secondOffset} seconds"));
			$this->em->persist($click);
		}
	}

	protected function generateRandomUrl(WordManager $manager) : string
	{
		return "https://www.google.com/search?q={$manager->getRandomWord()}";
	}
}
