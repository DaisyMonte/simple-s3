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

namespace Matecat\SimpleS3\Commands\Handlers;

use Psr\Http\Message\UriInterface;
use Matecat\SimpleS3\Commands\CommandHandler;
use Matecat\SimpleS3\Helpers\File;

class OpenItem extends CommandHandler
{
    /**
     * Open an item and return the content of it.
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

        try {
            $url = $this->client->getPublicItemLink(['bucket' => $bucketName, 'key' => $keyName]);
            $content = File::loadFile($url, $this->client->hasSslVerify());

            if (false === $content) {
                if (null !== $this->commandHandlerLogger) {
                    $this->commandHandlerLogger->log($this, sprintf('Something went wrong during getting content of \'%s\' item from \'%s\' bucket', $keyName, $bucketName), 'warning');
                }

                return null;
            }

            if (null !== $this->commandHandlerLogger) {
                $this->commandHandlerLogger->log($this, sprintf('Content from \'%s\' item was successfully obtained from \'%s\' bucket', $keyName, $bucketName));
            }

            return $content;
        } catch (\Exception $e) {
            if (null !== $this->commandHandlerLogger) {
                $this->commandHandlerLogger->logExceptionAndReturnFalse($e);
            }

            throw $e;
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
