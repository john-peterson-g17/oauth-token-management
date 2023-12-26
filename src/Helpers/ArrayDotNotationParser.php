<?php

namespace JohnPetersonG17\JwtAuthentication\Helpers;

class ArrayDotNotationParser {

    /**
     * Parses an array using dot notation and returns a value at the specified location in the array
     * If the key does not exist in the array at any depth then null is returned
     * @param array $array
     * @param string $key
     * @return mixed
     */
    public static function parse(array $array, string $dotKey): mixed {
        $keys = explode('.', $dotKey);

        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i]; // Get the key at this depth of the array

            if (!isset($array[$key])) { // If the key does not exist in this depth of the array
                return null; // Value is not set so return null
            }

            if ($i == count($keys) - 1) { // If it is the last part of the dot notation
                return $array[$key]; // Return the value at this postion
            }

            if (!is_array($array[$key])) { // We have been asked to go deeper into the array but the value is not an array
                return null; // Value is considered not set at this point so return null
            }

            // Finally we can assume that the value is an array and we can go deeper into the array by recursing
            $array = $array[$key]; // Set the new array to be one level lower
            $newKey = implode(".", array_slice($keys, 1)); // Remove the first part of the keys array and remake the dot notation key without it
            self::parse($array, $newKey); // Recurse
        }
    }

}