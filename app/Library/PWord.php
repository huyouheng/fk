<?php


namespace App\Library;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class PWord
{
    /**
     * 设置标题
     * @param Section $section
     * @param string $title
     */
    private static function setTitle(Section $section, $title = '商贸服务企业补助申请表')
    {
        $header = $section->addText($title);
        $headerFStyle = new \PhpOffice\PhpWord\Style\Font();
        $headerFStyle->setName('黑体')->setSize(18);
        $header->setFontStyle($headerFStyle);

        $headerPStyle = new \PhpOffice\PhpWord\Style\Paragraph();
        $headerPStyle->setAlignment(\PhpOffice\PhpWord\SimpleType\Jc::CENTER);
        $header->setParagraphStyle($headerPStyle);

        $breakLink = new \PhpOffice\PhpWord\Style\Font();
        $breakLink->setName('仿宋_GB2312')->setSize(16)->setColor('white');
        $section->addText("               ")->setFontStyle($breakLink);
        $section->addTextBreak();
    }

    /**
     * @param PhpWord $phpWord
     * @param Section $section
     * @param array $data
     */
    private static function setTableBody(PhpWord $phpWord, Section $section, array $data, $money)
    {
        $fancyTableStyleName = 'Fancy Table';
        $fancyTableStyle = array('borderSize' => 1, 'borderColor' => '000000');
        $fancyTableFirstRowStyle = array('borderBottomSize' => 0, 'borderBottomColor' => '000000', 'bgColor' => '000000');
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle);
        $table = $section->addTable($fancyTableStyleName);

        $center = new \PhpOffice\PhpWord\Style\Paragraph();
        $center->setAlignment(\PhpOffice\PhpWord\SimpleType\Jc::CENTER);
        $center->setAuto(true);
        foreach ($data as $key => $value) {
            $table->addRow(700);
            foreach ($value as $k => $v) {
                $ceil = $table->addCell(2300);
                $ceil->getStyle()->setVAlign('center');
                $font = $ceil->addText(" {$v} ");
                $font->setFontStyle(self::getGb2312_12Font());
                $font->setParagraphStyle($center);
            }
        }
        $signature = $table->addRow(900);
        $signatureCeil = $signature->addCell();

        $signatureCeil->addTextBreak(2);
        $signatureCeil->addText(' 我公司（单位）承诺：')
            ->setFontStyle(self::getGb2312_12Font());
        $signatureCeil->addTextBreak(2);
        $signatureCeil->addText('    本企业以上填报的内容及数据真实有效。')
            ->setFontStyle(self::getGb2312_12Font());
        $signatureCeil->addTextBreak(2);
        $signatureCeil->addText('                法定代表人（签字）：')
            ->setFontStyle(self::getGb2312_12Font());
        $signatureCeil->addTextBreak(2);

        $signatureCeil->getStyle()->setGridSpan(4);

        //拟补助金额
        $fkMoney = $table->addRow(1000);
        $fkCell = $fkMoney->addCell();
        $fkCell->getStyle()->setVAlign('center');
        $fkText = $fkCell->addText("拟补助金额");
        $fkText->setFontStyle(self::getGb2312_12Font());
        $fkText->setParagraphStyle($center);

        $empty = $fkMoney->addCell();
        $empty->getStyle()->setGridSpan(3);
        $fkMoney = $empty->addText($money ??'');
        $fkMoney->setFontStyle(self::getGb2312_12Font());
        $fkMoney->setParagraphStyle($center);


        // fk idea
        $fkidea = $table->addRow(1000);
        $fkIdeaCell = $fkidea->addCell();
        $fkIdeaCell->getStyle()->setVAlign('center');
        $fkIdeaText = $fkIdeaCell->addText("镇街（园区）审核意见");
        $fkIdeaText->setFontStyle(self::getGb2312_12Font());
        $fkIdeaText->setParagraphStyle($center);
        $empty = $fkidea->addCell();
        $empty->getStyle()->setGridSpan(3);
        $empty->addText("");
    }

    /**
     * 标题下面的日期
     * @param Section $section
     * @param string $y
     * @param string $m
     * @param string $d
     */
    private static function setTitleBelowDate(Section $section, $y = '', $m = '', $d = '')
    {
        $title = '填报日期：   '.$y.'年  '.$m.'  月  '.$d.'  日                       单位：万元（小数保留两位）';
        $section->addText($title)->setFontStyle(self::getGb2312_12Font());
    }

    private static function getGb2312_12Font()
    {
        $fStyle = new \PhpOffice\PhpWord\Style\Font();
        $fStyle->setName('仿宋_GB2312')->setSize(12);

        return $fStyle;
    }

    private static $money = '';
    /**
     * @param array $data
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public static function fetchCompanyWord(array  $data)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        self::setTitle($section);
        self::setTitleBelowDate($section, $data['year'], $data['month'],$data['day']);
        self::setTableBody($phpWord, $section, $data['company'], $data['money']);

        // Saving the document as OOXML file...
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($data['path']);
    }
}
