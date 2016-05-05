<?php

namespace DTL\DoctrineCR\Helper;



class PathHelper
{
    /**
     * Get the parent path of a valid absolute path.
     *
     * @param string $path the path to get the parent from
     *
     * @return string the path with the last segment removed
     */
    public static function getParentPath($path)
    {
        if ('/' === $path) {
            return '/';
        }

        $pos = strrpos($path, '/');

        if (0 === $pos) {
            return '/';
        }

        return substr($path, 0, $pos);
    }

    public function isSelfOrDescendant($selfPath, $candidatePath)
    {
        return $candidatePath === $selfPath || 0 === strpos($candidatePath, $selfPath . '/');
    }

    public static function join($segments)
    {
        $segments = self::segmentsToNames($segments);

        $segments = array_filter($segments, function ($element) {
            if ($element === '/') {
                return false;
            }

            return true;
        });

        return '/' . implode('/', $segments);
    }

    public static function getDepth($path)
    {
        return count(self::segmentToNames($path));
    }

    private static function segmentsToNames(array $segments)
    {
        $names = [];
        foreach ($segments as $segIndex => $segment) {
            foreach (self::segmentToNames($segment, $segIndex) as $index => $name) {
                $names[] = $name;

            }
        }

        return $names;
    }

    private static function segmentToNames($segment, $index = 0)
    {
        $originalSegment = $segment;

        if ($segment === '/') {
            return [];
        }

        if (false === strpos($segment, '/')) {
            return [ $segment ];
        }

        if (substr($segment, 0, 1) == '/') {
            if ($index > 0) {
                throw new \InvalidArgumentException(sprintf(
                    'Only the first segment can be absolute. Got element "%s" at position %d',
                    $segment, $index
                ));
            }

            $segment = substr($segment, 1);
        }

        $elements = explode('/', $segment);

        foreach ($elements as $element) {
            if (empty($element)) {
                throw new \InvalidArgumentException(sprintf(
                    'Found an empty element in segment "%s"', $originalSegment
                ));
            }
        }

        return $elements;
    }
}
