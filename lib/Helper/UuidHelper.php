<?php

namespace DoctrineCr\Helper;

/**
 * Some (or all) of the methods in this class copied from the
 * @link github.com/phpcr/phpcr-utils package.
 */
class UuidHelper
{
    /**
     * Checks if the string could be a UUID.
     *
     * @param string $id Possible uuid
     *
     * @return bool True if the test was passed, else false.
     */
    public static function isUUID($id)
    {
        // UUID is HEX_CHAR{8}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{12}
        if (1 === preg_match('/^[[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12}$/', $id)) {
            return true;
        }

        return false;
    }
}
