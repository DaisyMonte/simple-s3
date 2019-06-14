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

namespace SimpleS3\Commands\Handlers;

use Psr\Http\Message\UriInterface;
use SimpleS3\Commands\CommandHandler;
use SimpleS3\Helpers\File;

class GetPublicItemLink extends CommandHandler
{
    /**
     * Get the temporary public link of an item.
     * It return a presigned URL.
     *
     * @param array $params
     *
     * @return mixed|UriInterface
     * @throws \Exception
     */
    public function handle($params = [])
    {
        $bucketName = $params['bucket'];
        $keyName = $params['key'];
        $expires = (isset($params['expires'])) ? $params['expires'] : '+1 hour';

        try {
            $cmd = $this->client->getConn()->getCommand('GetObject', [
                'Bucket' => $bucketName,
                'Key'    => File::getFullPathConvertedToHex($keyName)
            ]);

            $link = $this->client->getConn()->createPresignedRequest($cmd, $expires)->getUri();
            $this->commandHandlerLogger->log($this, sprintf('Public link of \'%s\' file was successfully obtained from \'%s\' bucket', $keyName, $bucketName));

            return $link;
        } catch (\InvalidArgumentException $e) {
            $this->commandHandlerLogger->logExceptionAndContinue($e);
        }
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function validateParams($params = [])
    {
        return (
            isset($params['bucket']) and
            isset($params['key'])
        );
    }
}
