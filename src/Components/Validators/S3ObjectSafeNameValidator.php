<?php
/**
 *  This file is part of the Simple S3 package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Matecat\SimpleS3\Components\Validators;

/**
 * This class check if a string is a valid S3 object Name
 *
 * Amazon S3 object safe naming Requirements
 * -------------------------------------------------------------------------
 * You can use any UTF-8 character in an object key name. However, using certain characters in key names may cause problems with some applications and protocols.
 * The following guidelines help you maximize compliance with DNS, web-safe characters, XML parsers, and other APIs.
 *
 * Complete reference:
 *
 * https://docs.aws.amazon.com/en_us/AmazonS3/latest/dev/UsingMetadata.html
 *
 * @package SimpleS3
 */
final class S3ObjectSafeNameValidator extends S3NameValidator
{
    /**
     * @param string $string
     *
     * @return array
     */
    public static function validate($string)
    {
        $errors = [];

        if (substr($string, 0, 1) === '.') {
            $errors[] = 'The string cannot starts with .';
        }

        // check for string length
        if(strlen(urlencode($string)) > 221){
            $errors[] = 'The string is too long (max length of urlencoded string is 221 bytes)';
        }

        return $errors;
    }
}
