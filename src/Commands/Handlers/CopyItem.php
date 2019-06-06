<?php

namespace SimpleS3\Commands\Handlers;

use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use SimpleS3\Commands\CommandHandler;

class CopyItem extends CommandHandler
{
    /**
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
                'Key'    => $targetKeyname,
                'CopySource'    => $sourceBucket. DIRECTORY_SEPARATOR .$sourceKeyname,
            ]);

            if (($copied instanceof ResultInterface) and $copied['@metadata']['statusCode'] === 200) {
                $this->loggerWrapper->log(sprintf('File \'%s/%s\' was successfully copied to \'%s/%s\'', $sourceBucket, $sourceKeyname, $targetBucketName, $targetKeyname));
                $this->cacheWrapper->setInCache($targetBucketName, $targetKeyname);

                return true;
            }

            $this->loggerWrapper->log(sprintf('Something went wrong in copying file \'%s/%s\'', $sourceBucket, $sourceKeyname), 'warning');

            return false;
        } catch (S3Exception $e) {
            $this->loggerWrapper->logExceptionOrContinue($e);
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
