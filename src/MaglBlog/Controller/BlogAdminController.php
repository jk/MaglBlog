<?php

/**
 * @author Matthias Glaub <magl@magl.net>
 * @license http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace MaglBlog\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Exception;
use MaglBlog\Entity\BlogPost;
use MaglBlog\Entity\Category;
use MaglBlog\Form\BlogPostForm;
use MaglBlog\Form\CategoryForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

/**
 * Description of BlogAdminController
 *
 * @author matthias
 */
class BlogAdminController extends AbstractActionController implements FactoryInterface
{

	/**
	 *
	 * @var ObjectManager
	 */
	private $objectManager;

	/**
	 *
	 * @var ServiceManager
	 */
	private $sm;

	public function indexAction()
	{
		$blogPostRepo = $this->getObjectManager()->getRepository('\MaglBlog\Entity\BlogPost');
		$blogPosts = $blogPostRepo->findBy(array(), array('createDate' => 'DESC'));

		return new ViewModel(array(
			'blogPosts' => $blogPosts,
		));
	}

	public function editPostAction()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
		if (!$id) {
			$blogPost = new BlogPost();
		} else {
			try {
				$blogPost = $this->getObjectManager()->getRepository('\MaglBlog\Entity\BlogPost')->findOneBy(array('id' => $this->params('id')));
			} catch (Exception $ex) {
				return $this->redirect()->toRoute('zfcadmin/maglblog');
			}
		}

		$form = new BlogPostForm($this->getObjectManager());
		$form->setHydrator(new DoctrineObject($this->getObjectManager(), get_class($blogPost)));
		$form->bind($blogPost);
		$form->get('submit')->setAttribute('value', 'Edit');

		$tagString = $this->sm->get('MaglBlog\TagService')->getTagCollectionAsString($blogPost->getTags());

		$blogPostFieldset = $form->get('blog_post');
		$blogPostFieldset->get('tags-holder')->setValue($tagString);

		$request = $this->getRequest();
		if ($request->isPost()) {
			//$form->setInputFilter($blogPost->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$blogPostFieldset = $form->get('blog_post');
				$tagsString = $blogPostFieldset->get('tags-holder')->getValue();

				$tagCollection = $this->sm->get('MaglBlog\TagService')->getTagCollectionFromString($tagsString);
				$blogPost->setTags($tagCollection);

				$this->getObjectManager()->persist($blogPost);
				$this->getObjectManager()->flush();

				// Redirect to list
				return $this->redirect()->toRoute('zfcadmin/maglblog');
			}
		}

		return array(
			'id' => $id,
			'form' => $form,
		);
	}

	// Add content to this method:
	public function editCategoryAction()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
		if (!$id) {
			$category = new Category();
		} else {
			$category = $this->getObjectManager()->getRepository('\MaglBlog\Entity\Category')->findOneBy(array('id' => $this->params('id')));
		}

		$form = new CategoryForm($this->getObjectManager());
		$form->setHydrator(new DoctrineObject($this->getObjectManager(), get_class($category)));
		$form->bind($category);

		$request = $this->getRequest();
		if ($request->isPost()) {
			//$form->setInputFilter($blogPost->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getObjectManager()->persist($category);
				$this->getObjectManager()->flush();

				// Redirect to list
				return $this->redirect()->toRoute('zfcadmin/maglblog');
			}
		}

		return array(
			'id' => $id,
			'form' => $form,
		);
	}

	public function getBlogPostTable()
	{
		if (!$this->blogPostTable) {
			$sm = $this->getServiceLocator();
			$this->blogPostTable = $sm->get('MaglBlog\Model\BlogPostTable');
		}
		return $this->blogPostTable;
	}

	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$this->objectManager = $serviceLocator->getServiceLocator()
			->get('Doctrine\ORM\EntityManager');
		$this->sm = $serviceLocator->getServiceLocator();
		return $this;
	}

	/**
	 * 
	 * @return ObjectManager
	 */
	private function getObjectManager()
	{
		return $this->objectManager;
	}
}
