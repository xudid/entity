<?php


namespace Entity;


use Entity\Model\Model;
use Psr\Http\Message\ServerRequestInterface;

class SearchBinder
{
    private ?Model $model;
    /**
     * @var array
     */
    private array $parsedBody;

    public function __construct(ServerRequestInterface $serverRequest)
    {

        $this->parsedBody = $serverRequest->getParsedBody();
    }

    public function bind(string $class): array
    {
        $bindedParams = [];
        if (class_exists($class) && is_subclass_of($class, Model::class)) {
            $this->model = new $class();
            foreach ($this->model::getColumns() as $column) {
                $paramKey = $column->getName();
                $fieldName = strtolower($this->model::getShortClass() . '_' . $paramKey);
                $paramValue = $this->parsedBody[$fieldName];
                if (strlen($paramValue)) {
                    $bindedParams[$paramKey] = $paramValue;
                }
            }

        } else {
            throw new \Exception('Can not bin this class : ' . $class);
        }
        return $bindedParams;
    }
}
