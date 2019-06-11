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

class GetItem extends CommandHandler
{
    /**
     * @param array $params
     *
     * @return ResultInterface|mixed
     * @throws \Exception
     */
    public function handle($params = [])
    {
        $bucketName = $params['bucket'];
        $keyName = $params['key'];

        if ($this->client->hasCache()) {
            return $this->returnItemFromCache($bucketName, $keyName);
        }

        return $this->returnItemFromS3($bucketName, $keyName);
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

    /**
     * @param string $bucketName
     * @param string $keyName
     *
     * @return \Aws\Result
     * @throws \Exception
     */
    private function returnItemFromCache($bucketName, $keyName)
    {
        if(null === $file = $this->client->getCache()->get($bucketName, $keyName)){
            return  $this->returnItemFromS3($bucketName, $keyName);
        }

        return $file;
    }

    /**
     * @param string $bucketName
     * @param string $keyName
     *
     * @return \Aws\Result
     * @throws \Exception
     */
    private function returnItemFromS3($bucketName, $keyName)
    {
        try {
            $file = $this->client->getConn()->getObject([
                'Bucket' => $bucketName,
                'Key'    => $keyName
            ]);

            $this->loggerWrapper->log(sprintf('File \'%s\' was successfully obtained from \'%s\' bucket', $keyName, $bucketName));

            return $file;
        } catch (S3Exception $e) {
            $this->loggerWrapper->logExceptionAndContinue($e);
        }
    }
}
