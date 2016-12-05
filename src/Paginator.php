<?php
/**
 * Paginator.
 *
 * @copyright Copyright (c) 2016 Starweb AB
 * @license   BSD 3-Clause
 */

namespace Starlit\Paginator;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Andreas Nilsson <http://github.com/jandreasn>
 * @author David Ajnered <https://github.com/davidajnered>
 */
class Paginator
{
    /**
     * @var int
     */
    protected $currentPageNo;

    /**
     * @var int
     */
    protected $pages;

    /**
     * @var int
     */
    protected $maxPagesToShow = 9; // Use odd number so that one number can be in the middle

    /**
     * @var callable
     */
    protected $urlGenerator;

    /**
     * @var Request|null
     */
    protected $request;

    /**
     * @var string
     */
    protected $containerCssClass = 'pagination';

    /**
     * @var string
     */
    protected $description;

    /**
     * If description should be shown regardless of number of pages.
     *
     * @var bool
     */
    protected $alwaysShowDescription = false;

    /**
     * Constructor.
     *
     * @param int $currentPageNo
     * @param int $rowsPerPage
     * @param int $totalRowCount
     * @param callable|Request $urlGeneratorOrRequest
     * @param array $options
     */
    public function __construct(
        $currentPageNo,
        $rowsPerPage,
        $totalRowCount,
        $urlGeneratorOrRequest,
        array $options = []
    ) {
        $this->currentPageNo = $currentPageNo;

        $this->pages = ceil($totalRowCount / $rowsPerPage);
        $this->pages = ($this->pages < 1) ? 1 : $this->pages; // Pages should never be less than 1

        if (is_callable($urlGeneratorOrRequest)) {
            $this->urlGenerator = $urlGeneratorOrRequest;
        } elseif ($urlGeneratorOrRequest instanceof Request) {
            $this->request = $urlGeneratorOrRequest;
        } else {
            throw new \InvalidArgumentException(
                'Argument must be either a Request or a callable url generator'
            );
        }

        $this->setOptions($options);
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        if (isset($options['maxPagesToShow'])) {
            $this->maxPagesToShow = (int) $options['maxPagesToShow'];
        }

        if (isset($options['containerCssClass'])) {
            $this->containerCssClass = $options['containerCssClass'];
        }

        if (isset($options['description'])) {
            $this->description = $options['description'];
        }

        if (isset($options['alwaysShowDescription'])) {
            $this->alwaysShowDescription = (bool) $options['alwaysShowDescription'];
        }
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $leftRightSpan = (($this->maxPagesToShow - 1) / 2);
        $showAll = false;

        if ($this->pages > $this->maxPagesToShow) {
            // Default start and end values. This works as long the selected page is not to close to the edges
            $paginationStart = $this->currentPageNo - $leftRightSpan + 1;
            $paginationStart = ($paginationStart < 2) ? 2 : $paginationStart;

            $paginationEnd = $this->currentPageNo + $leftRightSpan - 1;
            $paginationEnd = ($paginationEnd > ($this->pages - 1)) ? $this->pages - 1 : $paginationEnd;

            // If we're closer to the left side
            if ($this->currentPageNo <= $leftRightSpan) {
                $paginationEnd += $leftRightSpan - $this->currentPageNo + 1;
            }

            // If we're closer to the right side
            if ($this->currentPageNo >= $this->pages - $leftRightSpan) {
                $paginationStart -= $leftRightSpan - ($this->pages - $this->currentPageNo);
            }

            // If dots will be shown to the left, show one less list item so that the total list
            // item count won't be more than iTotalPagingPages  (this can probably be included
            //prettier in the above calculations, but it's friday and my brain chose the easy way /Andreas)
            if (($this->currentPageNo - 1) > $leftRightSpan) {
                $paginationStart++;
            }

            // If dots will be shown to the right, show one less list item
            if ($this->currentPageNo < ($this->pages - $leftRightSpan)) {
                $paginationEnd--;
            }
        } else {
            $paginationStart = 2;
            $paginationEnd = $this->pages - 1;
            $showAll = true;
        }


        $html = '<div class="'. $this->containerCssClass . ' ' . ($this->hasMultiplePages()
            ? 'multiple-pages'
            : 'single-page') . '">';

        if ($this->description && ($this->alwaysShowDescription || $this->hasMultiplePages())) {
            $html .= '<p>' . $this->description . '</p>';
        }

        if ($this->hasMultiplePages()) {
            $html .= '<ul>';

            // Prev link
            $html .= $this->getListItemHtml('prev');

            // Permanent first link
            $html .= $this->getListItemHtml(1);

            // If there are more than 4 links to the left of active link, show dots
            if (!$showAll && ($this->currentPageNo - 1) > $leftRightSpan) {
                $html .= '<li class="disabled gap"><span>...</span></li>';
            }

            // Loop and build links
            for ($page = $paginationStart; $page <= $paginationEnd; $page++) {
                $html .= $this->getListItemHtml($page);
            }

            // If there are more than 4 links to the right of active link, show dots
            if (!$showAll && $this->currentPageNo < ($this->pages - $leftRightSpan)) {
                $html .= '<li class="disabled gap"><span>...</span></li>';
            }

            // Permanent last link
            if ($this->pages > 1) {
                $html .= $this->getListItemHtml($this->pages);
            }

            // Next link
            $html .= $this->getListItemHtml('next');

            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @param mixed $listItem Either a page number, or the strings 'prev' or 'next'
     * @return string
     */
    protected function getListItemHtml($listItem)
    {
        $cssClass = '';
        $disabled = false;
        switch ($listItem) {
            case 'prev':
                $linkText = '&laquo;';
                $page = $this->currentPageNo - 1;
                $cssClass = 'previous';

                if ($this->currentPageNo == 1) {
                    $disabled = true;
                }

                break;
            case 'next':
                $linkText = '&raquo;';
                $page = $this->currentPageNo + 1;
                $cssClass = 'next';

                if ($this->currentPageNo == $this->pages) {
                    $disabled = true;
                }

                break;
            default:
                $linkText = $listItem;
                $page = (int) $listItem;

                if ($page == $this->currentPageNo) {
                    $cssClass = 'active';
                }
                break;
        }

        if ($disabled) {
            $cssClass .= ' disabled';
        }

        $html = '<li' . ($cssClass ? ' class="' . $cssClass . '"' : '') . '>';

        if ($disabled) {
            $html .= '<span>' . $linkText . '</span>';
        } else {
            $html .= '<a href="' . $this->getUrl($page) . '">' . $linkText . '</a>';
        }

        $html .= '</li>';


        return $html;
    }

    /**
     * @param int $page
     * @return string
     */
    protected function getUrl($page)
    {
        if ($this->urlGenerator) {
            return call_user_func($this->urlGenerator, $page);
        }

        return $this->getRequestUrlWithNewPage($page);
    }

    /**
     * @param int $page
     * @return string
     */
    protected function getRequestUrlWithNewPage($page)
    {
        // Make up paginated url from current url
        $url = $this->request->getRequestUri();
        $urlWithoutQueryString = (($pos = strpos($url, '?')) !== false) ? substr($url, 0, $pos) : $url;
        $parameters = array_merge($this->request->query->all(), ['page' => $page]);
        $newQueryString = http_build_query($parameters, '', '&amp;');

        return $urlWithoutQueryString . '?' . $newQueryString;
    }

    /**
     * @return bool
     */
    public function hasMultiplePages()
    {
        return ($this->pages > 1);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getHtml();
    }
}
