<?php

namespace Xwero\Codestarter;

use ArrayObject;
use \InvalidArgumentException;

abstract class TypedCollection extends ArrayObject {

    abstract protected function getAllowedType(): string;

    public function __construct(object|array $array = [], int $flags = 0, string $iteratorClass = "ArrayIterator")
    {
        parent::__construct($array, $flags, $iteratorClass);

        if(is_array($array)) {
            foreach ($array as $item) {
                $this->validate($item);
            }
        }
    }

    public function offsetSet($key, $value): void
    {
        $this->validate($value);

        if (is_null($key)) {
            $this->getIterator()->append($value);
        } else {
            $this->getIterator()->offsetSet($key, $value);
        }
    }

    public function append(mixed $value): self
    {
        foreach ($value as $item) {
            $this[] = $item;
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    private function validate($value) {
        $allowedType = $this->getAllowedType();

        if (!($value instanceof $allowedType)) {
            throw new InvalidArgumentException(
                "Value must be an instance of {$allowedType}"
            );
        }
    }
}