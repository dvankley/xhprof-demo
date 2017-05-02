<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Link;
use AppBundle\Managers\SlugManager;
use AppBundle\Repository\LinkRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Managers\ProfilingTools;

class ShrinkController extends Controller
{
	const urlRoot = 'http://localhost:8080';

	/**
	 * @Route("/", name="index")
	 */
	public function index()
	{
		return $this->render('default/index.html.twig');
	}

	/**
	 * @Route("/shrink/generate", name="generate")
	 */
	public function generateShrunkUrl(Request $request)
	{
		ProfilingTools::activateXhprof();
		/** @var FormInterface $form */
		$form = $this->createFormBuilder()
			->add('url', TextType::class)
			->add('save', SubmitType::class, ['label' => 'Shrink Link'])
			->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// $form->getData() holds the submitted values
			// but, the original `$task` variable has also been updated
			$data = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$url = new Link();
			$url->setTargetUrl($data['url']);
			$url->setShrunkUrlSlug(SlugManager::generateSlug());
			$url->setUserId(1);
			$url->setCreatedAt(new \DateTime());
			$em->persist($url);
			$em->flush();

			ProfilingTools::storeXhprof();
			return $this->redirectToRoute('readShrunkUrl', ['slug' => $url->getShrunkUrlSlug()]);
		}

		ProfilingTools::storeXhprof();
		return $this->render('default/generate.html.twig', array(
			'form' => $form->createView(),
		));
//		$em = $this->getDoctrine()->getManager();
//		// replace this example code with whatever you need
//		return $this->render('default/index.html.twig', [
//			'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
//		]);
	}

	/**
	 * @Route("/shrink/read/{slug}", name="readShrunkUrl")
	 */
	public function readShrunkUrl(Request $request, $slug)
	{
		/** @var LinkRepository $linkRepository */
		$linkRepository = $this->getDoctrine()
			->getRepository('AppBundle:Link');

		$link = $linkRepository->getLinkBySlug($slug);

		$clicks = $link->getClicks();

		return $this->render('default/readLink.html.twig', [
			'destination_url' => $link->getTargetUrl(),
			'source_url' => self::urlRoot . '/' . $link->getShrunkUrlSlug(),
			'clicks' => $clicks,
		]);
	}

	/**
	 * @Route("/{slug}", name="followLink")
	 */
	public function followLink($slug)
	{
		/** @var LinkRepository $linkRepository */
		$linkRepository = $this->getDoctrine()
			->getRepository('AppBundle:Link');
		$link = $linkRepository->getLinkBySlug($slug);

		return $this->redirect($link->getTargetUrl());
	}
}
