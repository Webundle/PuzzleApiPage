<?php

namespace Puzzle\Api\PageBundle\Controller;

use JMS\Serializer\SerializerInterface;
use Puzzle\Api\MediaBundle\PuzzleApiMediaEvents;
use Puzzle\Api\MediaBundle\Event\FileEvent;
use Puzzle\Api\MediaBundle\Util\MediaUtil;
use Puzzle\Api\PageBundle\Entity\Page;
use Puzzle\Api\PageBundle\Entity\Template;
use Puzzle\OAuthServerBundle\Controller\BaseFOSRestController;
use Puzzle\OAuthServerBundle\Service\ErrorFactory;
use Puzzle\OAuthServerBundle\Service\Repository;
use Puzzle\OAuthServerBundle\Service\Utils;
use Puzzle\OAuthServerBundle\Util\FormatUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 * 
 */
class PageController extends BaseFOSRestController
{
    /**
     * @param RegistryInterface         $doctrine
     * @param Repository                $repository
     * @param SerializerInterface       $serializer
     * @param EventDispatcherInterface  $dispatcher
     * @param ErrorFactory              $errorFactory
     */
    public function __construct(
        RegistryInterface $doctrine,
        Repository $repository,
        SerializerInterface $serializer,
        EventDispatcherInterface $dispatcher,
        ErrorFactory $errorFactory
    ){
        parent::__construct($doctrine, $repository, $serializer, $dispatcher, $errorFactory);
        $this->fields = ['name', 'content', 'parent', 'template'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/pages")
	 */
	public function getPagesAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    $response = $this->repository->filter($query, Page::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/pages/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("page", class="PuzzleApiPageBundle:Page")
	 */
	public function getPageAction(Request $request, Page $page) {
	    if ($page->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $page]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/pages")
	 */
	public function postPageAction(Request $request) {
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['parent'] = isset($data['parent']) && $data['parent'] ? $em->getRepository(Page::class)->find($data['parent']) : null;
	    $data['template'] = isset($data['template']) && $data['template'] ? $em->getRepository(Template::class)->find($data['template']) : null;
	    
	    /** @var Page $page */
	    $page = Utils::setter(new Page(), $this->fields, $data);
	    
	    $em->persist($page);
	    $em->flush();
	    
	    /* Page picture listener */
	    if (isset($data['picture']) && $data['picture']) {
	        $this->dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Page::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($page){$page->setPicture($filename);}
	        ]));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $page]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/pages/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("page", class="PuzzleApiPageBundle:Page")
	 */
	public function putPageAction(Request $request, Page $page) {
	    $user = $this->getUser();
	    
	    if ($page->getCreatedBy()->getId() !== $user->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    
	    $data = $request->request->all();
	    if (isset($data['parent']) && $data['parent'] !== null) {
	        $data['parent'] = $em->getRepository(Page::class)->find($data['parent']);
	    }
	    
	    /** @var Page $page */
	    $page = Utils::setter($page, $this->fields, $data);
	    
	    /* Article picture listener */
	    if (isset($data['picture']) && $data['picture'] !== $page->getPicture()) {
	        $this->dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Page::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($page){$page->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/pages/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("page", class="PuzzleApiPageBundle:Page")
	 */
	public function deletePageAction(Request $request, Page $page) {
	    if ($page->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    $em = $this->doctrine->getManager($this->connection);
	    $em->remove($page);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
}