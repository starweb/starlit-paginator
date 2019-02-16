<?php

use PHPUnit\Framework\TestCase;
use Starlit\Paginator\Paginator;
use Symfony\Component\HttpFoundation\Request;

class PaginatorTest extends TestCase
{
    /**
     * @var Request
     */
    private $request;

    protected function setUp(): void
    {
        $this->request = Request::create('http://www.example.org', 'GET');
    }


    public function testInvalidConstruction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Paginator(1, 10, 10, null);
    }

    public function testGetHtmlSinglePage(): void
    {
        $paginator = new Paginator(1, 10, 10, $this->request, [
            'description' => 'Showing 1 result of 10',
            'containerCssClass' => 'test-css-class',
            'alwaysShowDescription' => true,
        ]);

        $html = $paginator->getHtml();

        $this->assertContains('<div', $html);
        $this->assertEquals(0, substr_count($html, '<li'));
        $this->assertContains('</div>', $html);
        $this->assertContains('Showing 1', $html);
        $this->assertContains('test-css-class', $html);
        $this->assertFalse($paginator->hasMultiplePages());
    }

    public function testToString(): void
    {
        $paginator = new Paginator(1, 10, 20, $this->request);

        $this->assertContains('<div', (string) $paginator);
    }

    public function testGetHtmlListItemsCount(): void
    {
        $paginator = new Paginator(1, 10, 100, $this->request);
        $html = $paginator->getHtml();

        $this->assertEquals(11, substr_count($html, '<li'));
    }

    public function testGetHtmlMaxPages(): void
    {
        $paginator = new Paginator(1, 10, 100, $this->request, [
            'maxPagesToShow' => 5,
        ]);
        $html = $paginator->getHtml();

        $this->assertEquals(7, substr_count($html, '<li'));
    }

    public function testGetHtmlListItemsCountWithLastPage(): void
    {
        $paginator = new Paginator(9, 10, 90, $this->request);
        $html = $paginator->getHtml();

        $this->assertEquals(11, substr_count($html, '<li'));
    }

    public function testGetHtmlListItemsCountWithManyRows(): void
    {
        $paginator = new Paginator(9, 10, 200, $this->request);
        $html = $paginator->getHtml();

        $this->assertEquals(11, substr_count($html, '<li'));
    }

    public function testGetHtmlListItemsCountWithMaxPage(): void
    {
        $paginator = new Paginator(17, 10, 200, $this->request);
        $html = $paginator->getHtml();

        $this->assertEquals(11, substr_count($html, '<li'));
    }

    public function testGetHtmlUrl(): void
    {
        $paginator = new Paginator(1, 10, 20, $this->request);
        $html = $paginator->getHtml();

        $this->assertContains('page=1"', $html);
        $this->assertContains('page=2"', $html);
    }

    public function testGetHtmlUrlGenerator(): void
    {
        $paginator = new Paginator(1, 10, 20, function($page) {
            return 'http://example.org/some-page.html?page=' . $page;
        });
        $html = $paginator->getHtml();

        $this->assertContains('href="http://example.org/some-page.html?page=1"', $html);
        $this->assertContains('href="http://example.org/some-page.html?page=2"', $html);
    }
}
