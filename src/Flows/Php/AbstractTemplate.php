<?php

namespace Xwero\Codestarter\Flows\Php;

use Xwero\Codestarter\Flows\Php\DTO\AttributeDTO;
use Xwero\Codestarter\Flows\Php\TypedCollection\AttributeDTOCollection;
use Xwero\Codestarter\Templatable;

class AbstractTemplate
{
    use Templatable;
    /**
     * Builds the attribute code for generation
     *
     * @param array $attributes
     * @return array
     */
    public function getAttributesOutput(AttributeDTOCollection $attributes) : string
    {
        $output = '';

        foreach($attributes as $attribute) {
            $out = '#['.$attribute->class;

            if($attribute->arguments) {
                $out .= '('.$attribute->arguments.')';
            }

            $output .= $out.']'."\n";
        }

        return $output;
    }
}