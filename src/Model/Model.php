<?php

namespace Entity\Model;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

class Model
{
    /**
     * [private description]
     * @var int $id
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected int $id;
    public function __construct(array $datas)
    {
        $this->hydrate($datas);
    }

    public function hydrate(array $datas)
    {
        $methods = get_class_methods(__CLASS__);
        $keys = array_keys($datas);
        foreach ($keys as $key) {
            $key = strtolower($key);
            $setter = 'set'.ucfirst($key);
            $value = $datas[$key];
            if (in_array($key, $methods)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}