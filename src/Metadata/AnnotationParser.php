<?php


namespace Entity\Metadata;


class AnnotationParser
{
    /**
     * @param string $attributesString
     * @return array
     *
     */
    public static function parseAttributes(string $attributesString) : array
    {
        $attributes = [];
        $pattern = '#([\w]+)="([\w\\\\]+)"#';
        if(preg_match_all($pattern,$attributesString, $attributes)) {
            array_shift($attributes);
            $attributes = array_combine($attributes[0], $attributes[1]);
        }
        return $attributes;
    }

    /**
     * @param $annotationType
     * @param $comment
     * @return string
     */
    public static function extractAttributeString($annotationType, $comment) :string
    {
        $pattern = '#@' . $annotationType . '\((.*)\)#';
        if (preg_match(
            $pattern,
            $comment,
            $attributes)) {
            return $attributes[1];
        } else {
            return '';
        }
    }
}