<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Libraries\HtmlToRichTextConverter;

/**
 * 架構匯出服務
 *
 * 此服務負責將風險評估架構資料（包含類別、主題、因子）匯出為 Excel 檔案。
 * 支援富文本格式（RichText），包括粗體、斜體、文字顏色等格式。
 *
 * 使用場景：
 * - 範本架構匯出
 * - 題項管理架構匯出
 *
 * @package App\Services
 * @author System
 * @version 1.0.0
 */
class StructureExportService
{
    /**
     * HTML 轉 RichText 轉換器實例
     * 用於將資料庫中的 HTML 格式描述轉換為 Excel 的 RichText 格式
     *
     * @var HtmlToRichTextConverter
     */
    private $htmlToRichText;

    /**
     * 建構函數
     * 初始化 HTML 到 RichText 的轉換器
     */
    public function __construct()
    {
        $this->htmlToRichText = new HtmlToRichTextConverter();
    }

    /**
     * 匯出架構資料為 Excel 檔案
     *
     * 此方法接收架構資料（類別、主題、因子）並生成格式化的 Excel 檔案。
     * 支援兩種架構模式：
     * 1. 三層架構：類別 -> 主題 -> 因子
     * 2. 兩層架構：類別 -> 因子（無主題層）
     *
     * Excel 檔案格式：
     * - 第一行為標題列，使用深藍色背景 (#4F46E5) 和白色文字
     * - 描述欄位支援富文本格式（粗體、斜體、顏色等）
     * - 自動調整欄位寬度以適應內容
     *
     * @param array $categories 風險類別陣列，每個元素包含：
     *                         - id: 類別ID
     *                         - category_name: 類別名稱
     *                         - description: 類別描述（支援 HTML 格式）
     *
     * @param array $topics 風險主題陣列，每個元素包含：
     *                     - id: 主題ID
     *                     - category_id: 所屬類別ID
     *                     - topic_name: 主題名稱
     *                     - description: 主題描述（支援 HTML 格式）
     *
     * @param array $factors 風險因子陣列，每個元素包含：
     *                      - id: 因子ID
     *                      - category_id: 所屬類別ID
     *                      - topic_id: 所屬主題ID（若無主題層則為 null）
     *                      - factor_name: 因子名稱
     *                      - description: 因子描述（支援 HTML 格式）
     *
     * @param bool $hasTopicLayer 是否包含主題層
     *                           - true: 三層架構（類別、主題、因子）
     *                           - false: 兩層架構（類別、因子）
     *
     * @param string $filename 匯出的檔案名稱（包含副檔名 .xlsx）
     *                        例如：「2024風險評估範本_架構_2024-01-01.xlsx」
     *
     * @return array 包含檔案內容和檔案名稱的陣列：
     *              - 'content': Excel 檔案的二進位內容
     *              - 'filename': 檔案名稱
     *
     * @throws \Exception 當 Excel 生成過程發生錯誤時拋出例外
     *
     * @example
     * $service = new StructureExportService();
     * $result = $service->exportToExcel($categories, $topics, $factors, true, '範本_架構_2024-01-01.xlsx');
     * // 回傳：['content' => '...binary data...', 'filename' => '範本_架構_2024-01-01.xlsx']
     */
    public function exportToExcel(array $categories, array $topics, array $factors, bool $hasTopicLayer, string $filename): array
    {
        // 建立新的試算表物件
        // Spreadsheet 是 PhpSpreadsheet 的核心類別，代表一個完整的 Excel 檔案
        $spreadsheet = new Spreadsheet();

        // 取得試算表的第一個工作表（Sheet）
        // 每個 Excel 檔案至少有一個工作表
        $sheet = $spreadsheet->getActiveSheet();

        // 設定工作表名稱為「架構資料」
        // 此名稱會顯示在 Excel 底部的分頁標籤上
        $sheet->setTitle('架構資料');

        // 設定表格標題列
        // 根據是否有主題層來決定欄位配置
        $this->setHeaders($sheet, $hasTopicLayer);

        // 設定欄位寬度
        // 確保每個欄位有足夠空間顯示內容，提升可讀性
        $this->setColumnWidths($sheet, $hasTopicLayer);

        // 填入資料列
        // 遍歷所有因子，並根據關聯性填入對應的類別和主題資訊
        $this->fillDataRows($sheet, $categories, $topics, $factors, $hasTopicLayer);

        // 生成 Excel 檔案內容
        // 將試算表物件轉換為實際的 .xlsx 檔案格式
        $fileContent = $this->generateFileContent($spreadsheet);

        // 回傳檔案內容和檔案名稱
        return [
            'content' => $fileContent,
            'filename' => $filename
        ];
    }

    /**
     * 設定 Excel 表格的標題列
     *
     * 標題列是 Excel 的第一行，用於標示每個欄位的名稱。
     * 根據架構類型（是否有主題層）決定欄位數量和名稱。
     *
     * 標題列樣式設定：
     * - 背景顏色：深藍色 (#4F46E5)
     * - 文字顏色：白色 (#FFFFFF)
     * - 文字粗體：是
     * - 文字大小：12pt
     * - 對齊方式：水平和垂直置中
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Excel 工作表物件
     * @param bool $hasTopicLayer 是否包含主題層
     *                           - true: 顯示 6 個欄位（類別名稱、類別描述、主題名稱、主題描述、因子名稱、因子描述）
     *                           - false: 顯示 4 個欄位（類別名稱、類別描述、因子名稱、因子描述）
     *
     * @return void
     */
    private function setHeaders($sheet, bool $hasTopicLayer): void
    {
        // 定義基本標題（類別相關欄位）
        $headers = ['風險類別名稱', '風險類別描述'];

        // 如果有主題層，增加主題相關欄位
        if ($hasTopicLayer) {
            $headers[] = '風險主題名稱';
            $headers[] = '風險主題描述';
        }

        // 增加因子相關欄位（所有架構都需要）
        $headers[] = '風險因子名稱';
        $headers[] = '風險因子描述';

        // 從 A 欄開始，逐一設定每個標題欄位
        $col = 'A';
        foreach ($headers as $header) {
            // 取得儲存格物件（例如：A1, B1, C1...）
            $cell = $sheet->getCell($col . '1');

            // 設定儲存格的值（標題文字）
            $cell->setValue($header);

            // 套用樣式陣列到儲存格
            // applyFromArray 方法接受一個關聯陣列，定義各種樣式屬性
            $cell->getStyle()->applyFromArray([
                // 填充設定（背景顏色）
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,  // 實心填充
                    'startColor' => ['rgb' => '4F46E5']  // 深藍色
                ],
                // 字型設定
                'font' => [
                    'bold' => true,  // 粗體
                    'color' => ['rgb' => 'FFFFFF'],  // 白色文字
                    'size' => 12  // 字體大小 12pt
                ],
                // 對齊設定
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,  // 水平置中
                    'vertical' => Alignment::VERTICAL_CENTER  // 垂直置中
                ]
            ]);

            // 移動到下一個欄位（A -> B -> C...）
            $col++;
        }
    }

    /**
     * 設定 Excel 表格的欄位寬度
     *
     * 為了確保內容能完整顯示且易於閱讀，需要為每個欄位設定適當的寬度。
     * 欄位寬度的單位是「字元寬度」，大約等於一個字元的寬度。
     *
     * 寬度配置原則：
     * - 名稱欄位（類別、主題、因子名稱）：20 個字元寬度
     * - 描述欄位（類別、主題、因子描述）：40 個字元寬度（因為描述通常較長）
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Excel 工作表物件
     * @param bool $hasTopicLayer 是否包含主題層
     *                           - true: 設定 6 個欄位的寬度（A-F）
     *                           - false: 設定 4 個欄位的寬度（A-D）
     *
     * @return void
     */
    private function setColumnWidths($sheet, bool $hasTopicLayer): void
    {
        // 類別名稱欄位（A 欄）- 寬度 20
        $sheet->getColumnDimension('A')->setWidth(20);

        // 類別描述欄位（B 欄）- 寬度 40
        $sheet->getColumnDimension('B')->setWidth(40);

        if ($hasTopicLayer) {
            // 三層架構：類別、主題、因子

            // 主題名稱欄位（C 欄）- 寬度 20
            $sheet->getColumnDimension('C')->setWidth(20);

            // 主題描述欄位（D 欄）- 寬度 40
            $sheet->getColumnDimension('D')->setWidth(40);

            // 因子名稱欄位（E 欄）- 寬度 20
            $sheet->getColumnDimension('E')->setWidth(20);

            // 因子描述欄位（F 欄）- 寬度 40
            $sheet->getColumnDimension('F')->setWidth(40);
        } else {
            // 兩層架構：類別、因子（無主題層）

            // 因子名稱欄位（C 欄）- 寬度 20
            $sheet->getColumnDimension('C')->setWidth(20);

            // 因子描述欄位（D 欄）- 寬度 40
            $sheet->getColumnDimension('D')->setWidth(40);
        }
    }

    /**
     * 填入 Excel 表格的資料列
     *
     * 此方法遍歷所有風險因子，並為每個因子建立一列資料。
     * 每一列包含：因子資訊 + 所屬主題資訊（如有）+ 所屬類別資訊
     *
     * 資料填入邏輯：
     * 1. 遍歷每個因子
     * 2. 根據 category_id 找到對應的類別
     * 3. 如果有主題層，根據 topic_id 找到對應的主題
     * 4. 將名稱和描述填入對應的欄位
     * 5. 描述欄位支援 HTML 轉 RichText，保留格式
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Excel 工作表物件
     * @param array $categories 風險類別陣列
     * @param array $topics 風險主題陣列
     * @param array $factors 風險因子陣列
     * @param bool $hasTopicLayer 是否包含主題層
     *
     * @return void
     */
    private function fillDataRows($sheet, array $categories, array $topics, array $factors, bool $hasTopicLayer): void
    {
        // 從第 2 列開始填入資料（第 1 列是標題列）
        $row = 2;

        // 遍歷每個風險因子
        foreach ($factors as $factor) {
            // 尋找此因子所屬的類別
            // 使用 category_id 比對，找到對應的類別資料
            $category = $this->findItemById($categories, 'id', $factor['category_id']);

            // 重置欄位指標為 A 欄（每一列都從 A 欄開始）
            $col = 'A';

            // === 填入類別資訊 ===

            // 類別名稱（A 欄）
            $sheet->setCellValue($col++ . $row, $category['category_name'] ?? '');

            // 類別描述（B 欄）
            // 如果描述不為空，將 HTML 格式轉換為 RichText
            if (!empty($category['description'])) {
                $richText = $this->htmlToRichText->convert($category['description']);
                $sheet->setCellValue($col . $row, $richText);
            }
            $col++;  // 移動到下一欄

            // === 填入主題資訊（如果有主題層）===
            if ($hasTopicLayer) {
                // 尋找此因子所屬的主題
                $topic = $this->findItemById($topics, 'id', $factor['topic_id']);

                // 主題名稱（C 欄）
                $sheet->setCellValue($col++ . $row, $topic['topic_name'] ?? '');

                // 主題描述（D 欄）
                // 如果描述不為空，將 HTML 格式轉換為 RichText
                if (!empty($topic['description'])) {
                    $richText = $this->htmlToRichText->convert($topic['description']);
                    $sheet->setCellValue($col . $row, $richText);
                }
                $col++;  // 移動到下一欄
            }

            // === 填入因子資訊 ===

            // 因子名稱（E 欄或 C 欄，取決於是否有主題層）
            $sheet->setCellValue($col++ . $row, $factor['factor_name'] ?? '');

            // 因子描述（F 欄或 D 欄，取決於是否有主題層）
            // 如果描述不為空，將 HTML 格式轉換為 RichText
            if (!empty($factor['description'])) {
                $richText = $this->htmlToRichText->convert($factor['description']);
                $sheet->setCellValue($col . $row, $richText);
            }

            // 移動到下一列，處理下一個因子
            $row++;
        }
    }

    /**
     * 根據指定欄位值在陣列中尋找項目
     *
     * 這是一個輔助方法，用於在陣列中搜尋特定的項目。
     * 類似於 SQL 的 WHERE 子句，根據欄位名稱和值進行比對。
     *
     * @param array $items 要搜尋的陣列
     * @param string $field 要比對的欄位名稱
     * @param mixed $value 要比對的值
     *
     * @return array|null 找到的項目（關聯陣列），若找不到則回傳 null
     *
     * @example
     * $categories = [
     *     ['id' => 1, 'name' => '財務風險'],
     *     ['id' => 2, 'name' => '營運風險']
     * ];
     * $result = $this->findItemById($categories, 'id', 2);
     * // 回傳：['id' => 2, 'name' => '營運風險']
     */
    private function findItemById(array $items, string $field, $value): ?array
    {
        // 遍歷陣列中的每個項目
        foreach ($items as $item) {
            // 檢查此項目的指定欄位值是否與目標值相符
            // 使用 == 而非 === 以允許型別轉換（例如：字串 "1" 與整數 1 視為相同）
            if (isset($item[$field]) && $item[$field] == $value) {
                // 找到符合的項目，立即回傳
                return $item;
            }
        }

        // 遍歷完成仍未找到，回傳 null
        return null;
    }

    /**
     * 生成 Excel 檔案內容
     *
     * 此方法將 Spreadsheet 物件轉換為實際的 .xlsx 檔案格式（二進位資料）。
     * 過程中會使用暫存檔案系統，確保檔案能正確生成後才回傳內容。
     *
     * 處理流程：
     * 1. 建立 Xlsx Writer 物件（負責將 Spreadsheet 轉換為 .xlsx 格式）
     * 2. 生成唯一的暫存檔案路徑
     * 3. 將 Spreadsheet 寫入暫存檔案
     * 4. 讀取暫存檔案的二進位內容
     * 5. 刪除暫存檔案（清理資源）
     * 6. 回傳檔案內容
     *
     * @param Spreadsheet $spreadsheet 要轉換的試算表物件
     *
     * @return string Excel 檔案的二進位內容
     *
     * @throws \Exception 當檔案寫入或讀取失敗時拋出例外
     */
    private function generateFileContent(Spreadsheet $spreadsheet): string
    {
        // 建立 Xlsx Writer 物件
        // Writer 負責將 Spreadsheet 物件序列化為 .xlsx 檔案格式
        $writer = new Xlsx($spreadsheet);

        // 生成暫存檔案路徑
        // WRITEPATH 是 CodeIgniter 定義的可寫入目錄常數
        // uniqid() 生成唯一識別碼，避免檔案名稱衝突
        // 例如：/var/www/writable/uploads/temp_63f8b2a1c45d3.xlsx
        $tempFile = WRITEPATH . 'uploads/temp_' . uniqid() . '.xlsx';

        // 將試算表寫入暫存檔案
        // 此操作會在伺服器檔案系統中建立實體檔案
        $writer->save($tempFile);

        // 讀取暫存檔案的完整內容（二進位資料）
        // file_get_contents() 回傳檔案的完整內容作為字串
        $fileContent = file_get_contents($tempFile);

        // 刪除暫存檔案
        // 讀取完成後立即刪除，避免占用磁碟空間
        // unlink() 是 PHP 的檔案刪除函數
        unlink($tempFile);

        // 回傳 Excel 檔案的二進位內容
        // 此內容可直接用於 HTTP 回應，讓使用者下載檔案
        return $fileContent;
    }
}
