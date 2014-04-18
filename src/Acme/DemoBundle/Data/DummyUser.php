<?php


namespace Acme\DemoBundle\Data;

use JMS\Serializer\Annotation as Serialize;

/**
 * Class DummyUser
 * @package Acme\DemoBundle\Data
 */
class DummyUser
{
    /**
     * @var int
     * @Serialize\Groups({"user.list", "user.get"})
     */
    private $id;

    /**
     * @var string
     * @Serialize\Groups({"user.list", "user.get"})
     */
    private $name;

    /**
     * @var string
     * @Serialize\Groups({"user.get"})
     */
    private $introduction;

    /**
     * @param int $id
     * @param string $name
     * @param string $introduction
     */
    public function __construct($id, $name, $introduction)
    {
        $this->id = $id;
        $this->name = $name;
        $this->introduction = $introduction;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getintroduction()
    {
        return $this->introduction;
    }
}
