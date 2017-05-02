<?php
namespace AppBundle\Repository;

use AppBundle\Entity\Link;
use Doctrine\ORM\EntityRepository;

class LinkRepository extends EntityRepository
{
	public function getLinkBySlug(string $slug) : Link
	{
		return $this->createQueryBuilder('l')
			->where('l.shrunkUrlSlug = :slug')
			->setParameter('slug', $slug)
			->getQuery()
			->getSingleResult();
	}
}