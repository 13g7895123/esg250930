<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;

/**
 * Excel RichText to HTML Converter
 *
 * 將 Excel RichText 物件轉換為 HTML 格式文字
 */
class RichTextToHtmlConverter
{
    /**
     * 將 Excel RichText 轉換為 HTML
     *
     * @param RichText|string $richText Excel RichText 物件或純文字
     * @return string HTML 內容
     */
    public function convert(RichText|string $richText): string
    {
        // 如果是純文字，直接返回（包裹在 <p> 標籤中）
        if (is_string($richText)) {
            return $this->wrapInParagraph(htmlspecialchars($richText));
        }

        $html = '';

        // 取得所有 RichText 元素
        $elements = $richText->getRichTextElements();

        foreach ($elements as $element) {
            if ($element instanceof Run) {
                // TextRun - 有樣式的文字
                $text = $element->getText();
                $font = $element->getFont();

                $html .= $this->formatText($text, $font);
            } else {
                // 普通文字
                $text = $element->getText();
                $html .= htmlspecialchars($text);
            }
        }

        // 處理換行符號為 <br> 標籤
        $html = $this->processLineBreaks($html);

        // 包裹在段落標籤中
        return $this->wrapInParagraph($html);
    }

    /**
     * 格式化文字並套用樣式
     *
     * @param string $text 文字內容
     * @param \PhpOffice\PhpSpreadsheet\Style\Font $font 字體樣式
     * @return string 格式化的 HTML
     */
    private function formatText(string $text, \PhpOffice\PhpSpreadsheet\Style\Font $font): string
    {
        // HTML 編碼
        $text = htmlspecialchars($text);

        $tags = [];

        // 粗體
        if ($font->getBold()) {
            $tags[] = 'strong';
        }

        // 斜體
        if ($font->getItalic()) {
            $tags[] = 'em';
        }

        // 底線
        if ($font->getUnderline() && $font->getUnderline() !== \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_NONE) {
            $tags[] = 'u';
        }

        // 刪除線
        if ($font->getStrikethrough()) {
            $tags[] = 's';
        }

        // 上標
        if ($font->getSuperscript()) {
            $tags[] = 'sup';
        }

        // 下標
        if ($font->getSubscript()) {
            $tags[] = 'sub';
        }

        // 顏色（忽略預設黑色）
        $color = null;
        if ($font->getColor() && $font->getColor()->getRGB()) {
            $rgb = $font->getColor()->getRGB();
            // 忽略黑色（000000）和預設顏色，因為這些是預設值
            if ($rgb && strtoupper($rgb) !== '000000' && strtoupper($rgb) !== 'FF000000') {
                $color = '#' . $rgb;
            }
        }

        // 套用標籤
        $html = $text;

        // 先套用顏色（使用 span）
        if ($color) {
            $html = '<span style="color: ' . $color . '">' . $html . '</span>';
        }

        // 套用其他標籤（由內而外）
        foreach (array_reverse($tags) as $tag) {
            $html = '<' . $tag . '>' . $html . '</' . $tag . '>';
        }

        return $html;
    }

    /**
     * 處理換行符號
     *
     * @param string $html HTML 內容
     * @return string 處理後的 HTML
     */
    private function processLineBreaks(string $html): string
    {
        // 將換行符號轉換為 <br> 標籤
        return nl2br($html, false);
    }

    /**
     * 將內容包裹在段落標籤中
     *
     * @param string $html HTML 內容
     * @return string 包裹後的 HTML
     */
    private function wrapInParagraph(string $html): string
    {
        // 如果內容為空，返回空字串
        if (empty(trim(strip_tags($html)))) {
            return '';
        }

        // 分割成段落（根據連續的 <br> 標籤）
        $paragraphs = preg_split('/(<br\s*\/?>){2,}/i', $html);

        $result = [];
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                $result[] = '<p>' . $paragraph . '</p>';
            }
        }

        return implode('', $result);
    }
}
