<?php declare(strict_types=1);

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;

#[OA\Tag('Documentation')]
class DocumentationController
{
    private BrowserFactory $browserFactory;

    public function __construct()
    {
        $this->browserFactory = new BrowserFactory();
        $this->browserFactory->addOptions(['ignoreCertificateErrors' => true]);
    }

    #[OA\Get(path: '/api/docs', tags: ['Documentation'], description: 'Generate User manual')]
    #[OA\Response(response: 200, description: 'Generate API documentation', content: new OA\MediaType(mediaType: 'application/pdf'))]
    public function generateDocs()
    {
        $browser = $this->browserFactory->createBrowser();

        try {
            $page = $browser->createPage();
            $page->navigate('https://web/tutorial')->waitForNavigation(PAGE::FIRST_CONTENTFUL_PAINT, 10000);

            $page->pdf(['printBackground' => true])->saveToFile("/tmp/webte/user-manual.pdf");

            header('Content-Type: application/pdf');
            readfile("/tmp/webte/user-manual.pdf");
        } finally {
            $browser->close();
        }

        SimpleRouter::response()->httpCode(200);
    }
}