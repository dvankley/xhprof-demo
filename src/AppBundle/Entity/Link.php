<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\DateTime;
use AppBundle\Repository\LinkRepository;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LinkRepository")
 * @ORM\Table(indexes={@ORM\Index(name="idx_lookup", columns={"shrunk_url_slug"})})
 */
class Link
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", length=512)
	 */
	protected $targetUrl;

	/**
	 * @ORM\Column(type="string", length=64)
	 */
	protected $shrunkUrlSlug;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $userId;

	/**
	 * @ORM\OneToMany(targetEntity="LinkClick", mappedBy="link")
	 * @var Collection
	 */
	protected $clicks;

	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $createdAt;

	/**
	 * Link constructor.
	 */
	public function __construct()
	{
		$this->clicks = new ArrayCollection();
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getTargetUrl() : string
	{
		return $this->targetUrl;
	}

	public function setTargetUrl(string $targetUrl)
	{
		$this->targetUrl = $targetUrl;
	}

	/**
	 * @return string
	 */
	public function getShrunkUrlSlug(): string
	{
		return $this->shrunkUrlSlug;
	}

	/**
	 * @param string $shrunkUrlSlug
	 */
	public function setShrunkUrlSlug(string $shrunkUrlSlug)
	{
		$this->shrunkUrlSlug = $shrunkUrlSlug;
	}

	/**
	 * @return Collection
	 */
	public function getClicks()
	{
		return $this->clicks;
	}

	/**
	 * @return mixed
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @param mixed $userId
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;
	}

	/**
	 * @return mixed
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @param mixed $createdAt
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
	}

}