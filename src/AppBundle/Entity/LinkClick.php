<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LinkClickRepository")
 */
class LinkClick
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Link", inversedBy="clicks")
	 * @ORM\JoinColumn(name="link_id", referencedColumnName="id")
	 */
	protected $link;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $createdAt;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * @param mixed $link
	 */
	public function setLink($link)
	{
		$this->link = $link;
	}

	/**
	 * @return DateTime
	 */
	public function getCreatedAt() : DateTime
	{
		return $this->createdAt;
	}

	/**
	 * @param DateTime $clickedAt
	 */
	public function setCreatedAt(DateTime $clickedAt)
	{
		$this->createdAt = $clickedAt;
	}

}
