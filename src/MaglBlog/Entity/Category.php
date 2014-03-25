<?php

/**
 * @author Matthias Glaub <magl@magl.net>
 * @license http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace MaglBlog\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="MaglBlog\Repository\Category")
 * @ORM\Table(name="maglblog_category")
 * 
 */
class Category
{

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="text")
	 * 
	 */
	protected $name;
	
	/**
	 * @ORM\OneToMany(targetEntity="MaglBlog\Entity\BlogPost", mappedBy="category")
	 * @ORM\OrderBy({"createDate" = "DESC"})
	 */
	protected $blogPosts;

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function setName($name)
	{
		$this->name = $name;
	}
	
	public function getBlogPosts()
	{
		return $this->blogPosts;
	}

	public function setBlogPosts($blogPosts)
	{
		$this->blogPosts = $blogPosts;
	}


}