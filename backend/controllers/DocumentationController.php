<?php declare(strict_types=1);

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Knp\Snappy\Pdf;

#[OA\Tag('Documentation')]
class DocumentationController
{
    #[OA\Get(path: '/api/docs', tags: ['Documentation'], description: 'Generate User manual')]
    #[OA\Response(response: 200, description: 'Generate API documentation', content: new OA\MediaType(mediaType: 'application/pdf'))]
    public function generateDocs()
    {
        $snappy = new Pdf('/usr/bin/wkhtmltopdf');
        $snappy->setOption('print-media-type', true);
        header('Content-Type: application/pdf');

        echo $snappy->getOutput('https://sympli.io/blog/a-quick-guide-to-css-for-printable-webpages');
        SimpleRouter::response()->httpCode(200);
    }
}