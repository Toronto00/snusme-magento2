<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.76
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Plugin;

use Mirasvit\Search\Block\Result;
use Magento\Search\Model\QueryFactory;
use Mirasvit\Search\Model\Config;

class HighlightPlugin
{
    /**
     * @var array
     */
    private $conditions = [
        ['(<a[^>]*>)', '(<\/a>)'],
    ];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    public function __construct(
        Config $config,
        QueryFactory $queryFactory
    ) {
        $this->config = $config;
        $this->queryFactory = $queryFactory;
    }

    /**
     * @param Result $block
     * @param string $html
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function afterToHtml(
        Result $block,
        $html
    ) {
        if (!$this->config->isHighlightingEnabled()) {
            return $html;
        }

        $html = $this->highlight(
            $html,
            $this->queryFactory->get()->getQueryText()
        );

        return $html;
    }

    /**
     * @param string $html
     * @param string $query
     *
     * @return string
     */
    public function highlight($html, $query)
    {
        $query = $this->removeSpecialChars($query);
        $replacement = [];
        $pattern = [];

        foreach ($this->conditions as $condition) {
            $matches = $this->getMatches($condition[0], $condition[1], $html);
            $pattern[] = $this->createPattern($condition[0], $condition[1], $matches);
            $replacement[] = $this->createReplacement($query, $matches);
        }

        $html = $this->_highlight($pattern, $replacement, $html);

        return $html;
    }

    /**
     * @param string $open
     * @param string $close
     * @param string $subject
     * @return array
     */
    private function getMatches($open, $close, $subject)
    {
        preg_match_all('/.' . $open . '([^<]*)' . $close . '/i', $subject, $matches);

        return $matches[2];
    }

    /**
     * @param string $open
     * @param string $close
     * @param array  $search
     * @return array
     */
    private function createPattern($open, $close, $search)
    {
        foreach ($search as $i => $match) {
            $match = '/' . $open . '(' . $this->escapeSpecialChars($match) . ')' . $close . '/i';
            $search[$i] = $match;
        }

        return $search;
    }

    /**
     * @param string $pattern
     * @param string $subject
     * @return array
     */
    private function createReplacement($pattern, $subject)
    {
        $replacement = [];
        $arrPattern = explode(' ', $pattern);
        $replace = '${1}<span class="search-result-highlight">${2}</span>${3}';
        foreach ($arrPattern as $pattern) {
            $pattern = trim($pattern);

            if ($pattern) {
                $pattern = $this->escapeSpecialChars($pattern);
                $pattern = '/(.*)(' . $pattern . ')(?![^<>]*[>])(.*)/iU';
                $replacement = preg_replace($pattern, $replace, $subject);
                $subject = $replacement;
            }
        }

        return $replacement;
    }

    /**
     * @param array  $pattern
     * @param array  $replacement
     * @param string $html
     * @return string
     */
    private function _highlight($pattern, $replacement, $html)
    {
        foreach ($replacement as $ind => $match) {
            foreach ($match as $i => $el) {
                $el = '${1}' . $el . '${3}';
                $match[$i] = $el;
            }
            $replacement[$ind] = $match;
        }

        foreach ($pattern as $i => $search) {
            $html = preg_replace($search, $replacement[$i], $html);
        }

        return $html;
    }

    /**
     * Escape special chars in regex.
     *
     * @param string $chars
     *
     * @return string $chars
     */
    public function escapeSpecialChars($chars)
    {
        $search = ['\\', '/', '^', '[', ']', '-', ')', '(', '.', '?', '+', '*'];
        $replace = ['\\\\', '\/', '\^', '\[', '\]', '\-', '\)', '\(', '\.', '\?', '\+', '\*'];

        return str_replace($search, $replace, $chars);
    }

    /**
     * @param array $chars
     * @return string
     */
    public function removeSpecialChars($chars)
    {
        $search = ['&'];
        $replace = [' '];

        return str_replace($search, $replace, $chars);
    }
}
