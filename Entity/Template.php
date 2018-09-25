<?php

namespace Puzzle\Api\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Puzzle\OAuthServerBundle\Traits\Nameable;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;


/**
 * Template
 *
 * @ORM\Table(name="page_template")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("page_template")
 * @Hateoas\Relation(
 * 		name = "self",
 * 		href = @Hateoas\Route(
 * 			"get_page_template",
 * 			parameters = {"id" = "expr(object.getId())"},
 * 			absolute = true,
 * ))
 */
class Template
{
    use PrimaryKeyable,
        Nameable,
        Sluggable,
        Blameable;
    
    /**
     * @var string
     * @ORM\Column(name="slug", type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $slug;
    
    /**
     * @var string
     * @ORM\Column(name="content", type="text")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $content;
    
    public function getSluggableFields() {
        return [ 'name' ];
    }
    
    public function setContent($content) : self {
        $this->content = $content;
        return $this;
    }
    
    public function getContent() :? string {
        return $this->content;
    }
}
