<?php

namespace SimpleS3\Commands\Handlers;

use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use SimpleS3\Commands\CommandHandler;
use SimpleS3\Helpers\File;

class CreateFolder extends CommandHandler
{
    /**
     * @param mixed $params
     *
     * @return bool
     * @throws \Exception
     */
    public function handle($params = [])
    {
        $bucketName = $params['bucket'];
        $keyName = $params['key'];

        if (false === File::endsWithSlash($keyName)) {
            $keyName .= DIRECTORY_SEPARATOR;
        }

        try {
            $folder = $this->client->getConn()->putObject([
                'Bucket' => $bucketName,
                'Key'    => $keyName,
                'Body'   => '',
                'ACL'    => 'public-read'
            ]);

            if (($folder instanceof ResultInterface) and $folder['@metadata']['statusCode'] === 200) {
                $this->log(sprintf('Folder \'%s\' was successfully created in \'%s\' bucket', $keyName, $bucketName));
                $this->setInCache($bucketName, $keyName);

                return true;
            }

            $this->log(sprintf('Something went wrong during creation of \'%s\' folder inside \'%s\' bucket', $keyName, $bucketName), 'warning');

            return false;
        } catch (S3Exception $e) {
            $this->logExceptionOrContinue($e);
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
