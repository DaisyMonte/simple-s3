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

use Aws\ResultInterface;
use Matecat\SimpleS3\Commands\CommandHandler;

class GetBucketSize extends CommandHandler
{
    /**
     * Get the total size of items in a bucket.
     *
     * @param array $params
     *
     * @return int|mixed
     * @throws \Exception
     */
    public function handle($params = [])
    {
        $bucketName = $params['bucket'];
        $size = 0;

        $items = $this->client->getItemsInABucket([
            'bucket' => $bucketName,
            'prefix' => (isset($params['prefix'])) ? $params['prefix'] : null,
            'hydrate' => true
        ]);

        /** @var ResultInterface $item */
        foreach ($items as $key => $item) {
            $size += $item['ContentLength'];
        }

        if (null !== $this->commandHandlerLogger) {
            $this->commandHandlerLogger->log($this, sprintf('Size of \'%s\' bucket was successfully obtained', $bucketName));
        }

        return $size;
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function validateParams($params = [])
    {
        return isset($params['bucket']);
    }
}
