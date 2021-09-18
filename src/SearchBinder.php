<?php


namespace Entity;


use Core\Contracts\ModelInterface;
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
        if (class_exists($class) && is_subclass_of($class, ModelInterface::class)) {
            $this->model = new $class();
            foreach ($this->model::getColumns() as $column) {
                $paramKey = $column->getName();
                $fieldName = strtolower($this->model::getShortClass() . '_' . $paramKey);
                if (in_array($fieldName, $this->parsedBody)) {
                    $paramValue = $this->parsedBody[$fieldName];
                    if (strlen($paramValue)) {
                        $bindedParams[$paramKey] = $paramValue;
                    }
                }
            }

        } else {
            throw new \Exception('Can not bin this class : ' . $class);
        }
        return $bindedParams;
    }
}
