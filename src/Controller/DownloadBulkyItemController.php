<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Controller;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\UriSigner as HttpFoundationUriSigner;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\UriSigner as HttpKernelUriSigner;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;

class DownloadBulkyItemController
{
    public function __construct(
        private readonly HttpFoundationUriSigner|HttpKernelUriSigner $uriSigner,
        private readonly BulkyItemStorage $bulkyItemStorage,
    ) {
    }

    public function __invoke(Request $request, string $voucher): Response
    {
        if (!$this->uriSigner->checkRequest($request)) {
            throw new NotFoundHttpException();
        }

        if (!$bulkyItem = $this->bulkyItemStorage->retrieve($voucher)) {
            throw new NotFoundHttpException();
        }

        $stream = $bulkyItem->getContents();

        $response = new StreamedResponse(
            static function () use ($stream): void {
                while (!feof($stream)) {
                    echo fread($stream, 8192); // Read in chunks of 8 KB
                    flush();
                }
                fclose($stream);
            },
        );

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $bulkyItem->getName(),
        ));

        return $response;
    }
}
