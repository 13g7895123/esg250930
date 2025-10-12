<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * HTML to Excel RichText Converter
 *
 * 將 HTML 格式文字轉換為 Excel RichText 物件
 */
class HtmlToRichTextConverter
{
    /**
     * 偵測到的背景色（用於整個儲存格）
     */
    private ?string $detectedBackgroundColor = null;

    /**
     * 將 HTML 轉換為 Excel RichText
     *
     * @param string $html HTML 內容
     * @return RichText|string Excel RichText 物件或純文字
     */
    public function convert(string $html): RichText|string
    {
        if (empty($html)) {
            return '';
        }

        // 如果沒有 HTML 標籤，直接返回純文字
        if (strip_tags($html) === $html) {
            return $html;
        }

        // 重置偵測到的背景色
        $this->detectedBackgroundColor = null;

        $richText = new RichText();

        // 解析 HTML 並轉換為 RichText 片段
        $this->parseHtml($html, $richText);

        return $richText;
    }

    /**
     * 取得偵測到的背景色
     *
     * @return string|null 十六進位顏色代碼
     */
    public function getDetectedBackgroundColor(): ?string
    {
        return $this->detectedBackgroundColor;
    }

    /**
     * 解析 HTML 並建立 RichText 片段
     *
     * @param string $html HTML 內容
     * @param RichText $richText RichText 物件
     */
    private function parseHtml(string $html, RichText $richText): void
    {
        // 移除多餘的空白和換行
        $html = trim($html);

        // 建立 DOMDocument 解析 HTML
        $dom = new \DOMDocument();

        // 抑制 HTML5 警告
        libxml_use_internal_errors(true);

        // 載入 HTML，使用 UTF-8 編碼
        // 使用完整的 HTML 包裝以確保多段落能正確解析
        $wrappedHtml = '<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>';
        $dom->loadHTML($wrappedHtml, LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        // 處理 body 節點
        $body = $dom->getElementsByTagName('body')->item(0);

        if ($body) {
            $this->processNode($body, $richText);
        }
    }

    /**
     * 遞迴處理 DOM 節點
     *
     * @param \DOMNode $node DOM 節點
     * @param RichText $richText RichText 物件
     * @param array $inheritedStyle 繼承的樣式
     */
    private function processNode(\DOMNode $node, RichText $richText, array $inheritedStyle = []): void
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                // 文字節點
                $text = $child->nodeValue;

                // 保留文字內容，但移除前後的純空白（不包括有實際內容的空白）
                if (!empty(trim($text))) {
                    // 只處理空白符號，不影響文字內容
                    // 保留文字原樣，僅清理連續空白（但不是換行符）
                    $text = preg_replace('/[ \t]+/', ' ', $text);
                    $this->addTextRun($richText, $text, $inheritedStyle);
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                // 元素節點
                $currentStyle = $this->getStyleFromElement($child, $inheritedStyle);

                // 處理特殊元素
                switch (strtolower($child->nodeName)) {
                    case 'br':
                        $this->addTextRun($richText, "\n", $inheritedStyle);
                        break;

                    case 'p':
                        // 段落元素：如果不是第一個元素，在前面加換行
                        if ($richText->getRichTextElements()) {
                            $this->addTextRun($richText, "\n", $inheritedStyle);
                        }
                        // 處理段落內容
                        $this->processNode($child, $richText, $currentStyle);
                        // 段落結束後不需要額外加換行，因為下一個段落會自己加前導換行
                        break;

                    case 'div':
                        // div 元素：處理方式與 p 類似但可能包含其他塊級元素
                        if ($richText->getRichTextElements()) {
                            // 檢查前一個元素是否需要換行
                            $elements = $richText->getRichTextElements();
                            $lastElement = end($elements);
                            if ($lastElement && $lastElement->getText() && !str_ends_with($lastElement->getText(), "\n")) {
                                $this->addTextRun($richText, "\n", $inheritedStyle);
                            }
                        }
                        $this->processNode($child, $richText, $currentStyle);
                        break;

                    case 'ul':
                    case 'ol':
                        // 列表
                        $this->processNode($child, $richText, $currentStyle);
                        break;

                    case 'li':
                        // 列表項目
                        $this->addTextRun($richText, "• ", $currentStyle);
                        $this->processNode($child, $richText, $currentStyle);
                        $this->addTextRun($richText, "\n", $inheritedStyle);
                        break;

                    default:
                        // 其他元素，遞迴處理
                        $this->processNode($child, $richText, $currentStyle);
                        break;
                }
            }
        }
    }

    /**
     * 從元素取得樣式設定
     *
     * @param \DOMElement $element DOM 元素
     * @param array $inheritedStyle 繼承的樣式
     * @return array 樣式陣列
     */
    private function getStyleFromElement(\DOMElement $element, array $inheritedStyle = []): array
    {
        $style = $inheritedStyle;

        $tagName = strtolower($element->nodeName);

        // 根據標籤設定樣式
        switch ($tagName) {
            case 'strong':
            case 'b':
                $style['bold'] = true;
                break;

            case 'em':
            case 'i':
                $style['italic'] = true;
                break;

            case 'u':
                $style['underline'] = true;
                break;

            case 's':
            case 'strike':
                $style['strikethrough'] = true;
                break;

            case 'sup':
                $style['superscript'] = true;
                break;

            case 'sub':
                $style['subscript'] = true;
                break;
        }

        // 處理 style 屬性
        if ($element->hasAttribute('style')) {
            $styleAttr = $element->getAttribute('style');
            $this->parseStyleAttribute($styleAttr, $style);
        }

        return $style;
    }

    /**
     * 解析 style 屬性
     *
     * @param string $styleAttr style 屬性值
     * @param array &$style 樣式陣列（參照傳遞）
     */
    private function parseStyleAttribute(string $styleAttr, array &$style): void
    {
        $styles = explode(';', $styleAttr);

        foreach ($styles as $s) {
            $parts = explode(':', $s, 2);
            if (count($parts) === 2) {
                $property = trim($parts[0]);
                $value = trim($parts[1]);

                switch ($property) {
                    case 'color':
                        // 解析顏色
                        $color = $this->parseColor($value);
                        if ($color) {
                            $style['color'] = $color;
                        }
                        break;

                    case 'background-color':
                        // 解析背景色
                        $bgColor = $this->parseColor($value);
                        if ($bgColor) {
                            $this->detectedBackgroundColor = $bgColor;
                        }
                        break;

                    case 'font-weight':
                        if ($value === 'bold' || intval($value) >= 600) {
                            $style['bold'] = true;
                        }
                        break;

                    case 'font-style':
                        if ($value === 'italic') {
                            $style['italic'] = true;
                        }
                        break;

                    case 'text-decoration':
                        if (strpos($value, 'underline') !== false) {
                            $style['underline'] = true;
                        }
                        if (strpos($value, 'line-through') !== false) {
                            $style['strikethrough'] = true;
                        }
                        break;
                }
            }
        }
    }

    /**
     * 解析顏色值
     *
     * @param string $colorValue 顏色值
     * @return string|null 十六進位顏色代碼
     */
    private function parseColor(string $colorValue): ?string
    {
        // 移除空白
        $colorValue = trim($colorValue);

        // Hex 格式 (#RGB 或 #RRGGBB)
        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $colorValue, $matches)) {
            $hex = $matches[1];

            // 轉換 #RGB 為 #RRGGBB
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }

            return strtoupper($hex);
        }

        // RGB 格式
        if (preg_match('/rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i', $colorValue, $matches)) {
            $r = str_pad(dechex($matches[1]), 2, '0', STR_PAD_LEFT);
            $g = str_pad(dechex($matches[2]), 2, '0', STR_PAD_LEFT);
            $b = str_pad(dechex($matches[3]), 2, '0', STR_PAD_LEFT);
            return strtoupper($r . $g . $b);
        }

        return null;
    }

    /**
     * 添加文字片段到 RichText
     *
     * @param RichText $richText RichText 物件
     * @param string $text 文字內容
     * @param array $style 樣式設定
     */
    private function addTextRun(RichText $richText, string $text, array $style = []): void
    {
        if (empty($style)) {
            // 無樣式，添加普通文字
            $richText->createText($text);
        } else {
            // 有樣式，建立 TextRun
            $textRun = $richText->createTextRun($text);
            $font = $textRun->getFont();

            // 套用樣式
            if (!empty($style['bold'])) {
                $font->setBold(true);
            }

            if (!empty($style['italic'])) {
                $font->setItalic(true);
            }

            if (!empty($style['underline'])) {
                $font->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
            }

            if (!empty($style['strikethrough'])) {
                $font->setStrikethrough(true);
            }

            if (!empty($style['superscript'])) {
                $font->setSuperscript(true);
            }

            if (!empty($style['subscript'])) {
                $font->setSubscript(true);
            }

            if (!empty($style['color'])) {
                $font->setColor(new Color($style['color']));
            }
        }
    }
}
