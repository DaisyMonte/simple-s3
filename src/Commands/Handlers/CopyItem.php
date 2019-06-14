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

use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use SimpleS3\Commands\CommandHandler;
use SimpleS3\Helpers\File;

class CopyItem extends CommandHandler
{
    /**
     * Copy an item from a bucket to another one.
     * For a complete reference:
     * https://docs.aws.amazon.com/cli/latest/reference/s3api/copy-object.html?highlight=copy
     *
     * @param mixed $params
     *
     * @return bool
     * @throws \Exception
     */
    public function handle($params = [])
    {
        $targetBucketName = $params['target_bucket'];
        $targetKeyname = $params['target'];
        $sourceBucket = $params['source_bucket'];
        $sourceKeyname = $params['source'];

        $this->client->createBucketIfItDoesNotExist(['bucket' => $targetBucketName]);

        try {
            $copied = $this->client->getConn()->copyObject([
                'Bucket' => $targetBucketName,
                'Key'    => File::getFullPathConvertedToHex($targetKeyname),
                'CopySource'    => $sourceBucket. DIRECTORY_SEPARATOR .File::getFullPathConvertedToHex($sourceKeyname),
            ]);

            if (($copied instanceof ResultInterface) and $copied['@metadata']['statusCode'] === 200) {
                $this->commandHandlerLogger->log($this, sprintf('File \'%s/%s\' was successfully copied to \'%s/%s\'', $sourceBucket, $sourceKeyname, $targetBucketName, $targetKeyname));

                if ($this->client->hasCache()) {
                    $this->client->getCache()->set($targetBucketName, $targetKeyname, '');
                }

                return true;
            }

            $this->commandHandlerLogger->log($this, sprintf('Something went wrong in copying file \'%s/%s\'', $sourceBucket, $sourceKeyname), 'warning');

            return false;
        } catch (S3Exception $e) {
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
            isset($params['target_bucket']) and
            isset($params['target']) and
            isset($params['source_bucket']) and
            isset($params['source'])
        );
    }
}
