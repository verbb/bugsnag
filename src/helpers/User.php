<?php
namespace verbb\bugsnag\helpers;

use Craft;
use craft\helpers\App;

class User
{
    // Static Methods
    // =========================================================================

    public static function resolve(mixed $config = true): array
    {
        $currentUser = self::getCurrent();

        if (is_callable($config)) {
            return self::_normalizeUserData($config($currentUser));
        }

        if ($config === false || (is_string($config) && !App::parseBooleanEnv($config))) {
            return [];
        }

        if (is_array($config)) {
            return self::_resolveUserConfig($config, $currentUser);
        }

        if (!$currentUser) {
            return [];
        }

        return self::_filterUserData([
            'id' => self::_readUserValue($currentUser, 'id'),
            'name' => self::_readUserValue($currentUser, 'fullName') ?: self::_readUserValue($currentUser, 'name'),
            'email' => self::_readUserValue($currentUser, 'email'),
        ]);
    }

    public static function getCurrent(): mixed
    {
        $userComponent = Craft::$app->has('user', true) ? Craft::$app->get('user') : null;

        if ($userComponent && method_exists($userComponent, 'getIdentity')) {
            return $userComponent->getIdentity();
        }

        return null;
    }


    // Private Static Methods
    // =========================================================================

    private static function _resolveUserConfig(array $config, mixed $currentUser): array
    {
        if (!$currentUser) {
            return [];
        }

        $data = [];

        foreach ($config as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $data[$value] = self::_readUserValue($currentUser, $value);

                continue;
            }

            $data[$key] = self::_resolveUserValue($value, $currentUser);
        }

        return self::_filterUserData($data);
    }

    private static function _resolveUserValue(mixed $value, mixed $currentUser): mixed
    {
        if (is_callable($value)) {
            return $value($currentUser);
        }

        if (is_string($value)) {
            return self::_readUserValue($currentUser, $value);
        }

        return $value;
    }

    private static function _readUserValue(mixed $user, string $name): mixed
    {
        if (!is_object($user)) {
            return null;
        }

        $getter = 'get' . ucfirst($name);

        if (method_exists($user, $getter)) {
            return $user->{$getter}();
        }

        if (method_exists($user, 'canGetProperty') && $user->canGetProperty($name)) {
            return $user->{$name};
        }

        if (property_exists($user, $name)) {
            return $user->{$name};
        }

        return null;
    }

    private static function _normalizeUserData(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        return self::_filterUserData($data);
    }

    private static function _filterUserData(array $data): array
    {
        return array_filter($data, fn($value) => $value !== null && $value !== '');
    }
}
