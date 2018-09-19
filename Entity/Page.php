<?php

namespace Puzzle\Api\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Puzzle\OAuthServerBundle\Traits\Nameable;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Doctrine\Common\Collections\Collection;
use Puzzle\OAuthServerBundle\Traits\Pictureable;
use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;


/**
 * Template
 *
 * @ORM\Table(name="page")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("page")
 * @Hateoas\Relation(
 * 		name = "self",
 * 		href = @Hateoas\Route(
 * 			"get_page",
 * 			parameters = {"id" = "expr(object.getId())"},
 * 			absolute = true,
 * ))
 * @Hateoas\Relation(
 *     name = "parent",
 *     embedded = "expr(object.getParent())",
 *     exclusion = @Hateoas\Exclusion(excludeIf = "expr(object.getParent() === null)"),
 *     href = @Hateoas\Route(
 * 			"get_page", 
 * 			parameters = {"id" = "expr(object.getParent().getId())"},
 * 			absolute = true,
 * ))
 * @Hateoas\Relation(
 *     name = "childs",
 *     embedded = "expr(object.getChilds())",
 *     exclusion = @Hateoas\Exclusion(excludeIf = "expr(object.getChilds() === null)")
 * ))
 */
class Page
{
    use PrimaryKeyable,
        Nameable,
        Pictureable,
        Sluggable,
        Blameable;

    /**
     * @var string
     * @ORM\Column(name="content", type="text")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $content;
    
    /**
     * @var string
     * @ORM\Column(name="slug", type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $slug;
    
    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="parent")
     */
    private $childs;
    
    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="childs")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;
    
    /**
     * @ORM\ManyToOne(targetEntity="Template")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $template;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getSluggableFields() {
        return [ 'name' ];
    }
    
    public function generateSlugValue($values) {
        return implode('-', $values);
    }
   
    public function setContent($content) : self {
        $this->content = $content;
        return $this;
    }
    
    public function getContent() :? string {
        return $this->content;
    }
    
    public function setParent(Template $parent = null) {
        $this->parent = $parent;
        return $this;
    }
    
    public function getParent() :? self {
        return $this->parent;
    }
    
    public function setTemplate(Template $template) : self {
        $this->template = $template;
        return $this;
    }
    
    public function getTemplate() :? Template {
        return $this->template;
    }
    
    public function addChild(Template $child) : self {
        $this->childs[] = $child;
        return $this;
    }
    
    public function removeChild(Template $child) : self {
        $this->childs->removeElement($child);
        return $this;
    }
    
    public function getChilds() :? Collection {
        return $this->childs;
    }
}
