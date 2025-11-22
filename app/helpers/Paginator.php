// FILE: /app/helpers/Paginator.php
<?php

/**
 * Paginator Helper
 * Handles pagination
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Paginator
{
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $totalPages;
    private $offset;

    /**
     * Constructor
     */
    public function __construct($totalItems, $itemsPerPage = 20, $currentPage = 1)
    {
        $this->totalItems = (int)$totalItems;
        $this->itemsPerPage = (int)$itemsPerPage;
        $this->currentPage = max(1, (int)$currentPage);
        $this->totalPages = (int)ceil($this->totalItems / $this->itemsPerPage);
        $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;

        // Ensure current page is valid
        if ($this->currentPage > $this->totalPages && $this->totalPages > 0) {
            $this->currentPage = $this->totalPages;
            $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
        }
    }

    /**
     * Get offset for SQL query
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Get limit for SQL query
     */
    public function getLimit()
    {
        return $this->itemsPerPage;
    }

    /**
     * Get current page
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get total pages
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Get total items
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * Check if has previous page
     */
    public function hasPrevious()
    {
        return $this->currentPage > 1;
    }

    /**
     * Check if has next page
     */
    public function hasNext()
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Get previous page number
     */
    public function getPrevious()
    {
        return $this->hasPrevious() ? $this->currentPage - 1 : null;
    }

    /**
     * Get next page number
     */
    public function getNext()
    {
        return $this->hasNext() ? $this->currentPage + 1 : null;
    }

    /**
     * Get page range
     */
    public function getPageRange($delta = 2)
    {
        $start = max(1, $this->currentPage - $delta);
        $end = min($this->totalPages, $this->currentPage + $delta);

        return range($start, $end);
    }

    /**
     * Render pagination HTML
     */
    public function render($baseUrl = '')
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Pagination"><ul class="pagination">';

        // Previous button
        if ($this->hasPrevious()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $this->getPrevious() . '">Previous</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }

        // Page numbers
        foreach ($this->getPageRange() as $page) {
            if ($page === $this->currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $page . '">' . $page . '</a></li>';
            }
        }

        // Next button
        if ($this->hasNext()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $this->getNext() . '">Next</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }

    /**
     * Get pagination info text
     */
    public function getInfo()
    {
        if ($this->totalItems === 0) {
            return "No items found";
        }

        $from = $this->offset + 1;
        $to = min($this->offset + $this->itemsPerPage, $this->totalItems);

        return "Showing {$from} to {$to} of {$this->totalItems} items";
    }
}
