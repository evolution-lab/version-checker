<?php

namespace Evo\Version;

/**
 * Facilitates checking the required software versions.
 */
class VersionChecker
{
    /**
     * Returns true when the given version are compatible based on the given
     * operator.
     *
     * @param $version1
     * @param $version2
     * @param $operator
     * @return mixed
     */
    public static function check($version1, $version2, $operator)
    {
        return version_compare($version1, $version2, $operator);
    }

    /**
     * Like `check` but throws exception if the check fails.
     *
     * @see check
     * @param $version1
     * @param $version2
     * @param $operator
     * @throws InvalidVersionException
     */
    public static function assert(
        $version1,
        $version2,
        $operator,
        $message = null
    ) {
        if (empty($message)) {
            $message = 'Invalid version.';
        }

        if (!self::check($version1, $version2, $operator)) {
            throw new InvalidVersionException($message);
        }
    }

    /**
     * Helper for comparing with current environment php version.
     *
     * @param $version
     * @param $operator
     * @return mixed
     */
    public static function checkPhp($version, $operator)
    {
        return self::check(phpversion(), $version, $operator);
    }

    /**
     * Throws exception when PHP version is not valid.
     *
     * @param $version
     * @param $operator
     * @throws InvalidVersionException
     */
    public static function assertPhp($version, $operator, $message = null)
    {
        if (empty($message)) {
            $message = 'Invalid PHP version.';
        }

        if (self::checkPhp($version, $operator)) {
            throw new InvalidVersionException($message);
        }
    }

    /**
     * Throws exception when the current version is not greater than or equal
     * to the required version.
     *
     * The **`$context`** parameter can be used both to set the current version or
     * a string representing the software we want to take version from.
     *
     * The following strings are supported (not case sensitive):
     * - PHP
     * - WordPress
     *
     * @param $context
     * @param $requiredVersion
     * @throws InvalidVersionException
     */
    public function requireAtLeast($context, $requiredVersion)
    {
        $currentVersion = self::resolveCurrentVersion($context);

        if (is_null($currentVersion)) {
            throw new \InvalidArgumentException("Invalid context given. '$context' is not a valid context.");
        }

        $message = self::resolveMessage($context, $requiredVersion);

        self::assert($currentVersion, $requiredVersion, '>=', $message);
    }

    /**
     * Resolves the version of the software identified by the given context.
     *
     * @param $context
     * @return string|null
     */
    private function resolveCurrentVersion($context)
    {
        $context = strtolower($context);

        switch ($context) {
            case 'php':
                return phpversion();

            case 'wordpress':
                return get_bloginfo('version');
        }

        return null;
    }

    /**
     * Resolves an error message based on the given context.
     *
     * @param $context
     * @return string|null
     */
    private function resolveMessage($context, $requiredVersion)
    {
        $context = strtolower($context);
        $template = "%s version must be at least '$requiredVersion'.";

        $softwareName = '';

        switch ($context) {
            case 'php':
                $softwareName = 'PHP';
                break;

            case 'wordpress':
                $softwareName = 'WordPress';
                break;
        }

        return sprintf($template, $softwareName);
    }
}