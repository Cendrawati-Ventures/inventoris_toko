<?php

class SimplePDF {
    private $pages = [];
    private $currentPage = -1;
    private $x = 10;
    private $y = 10;
    private $fontSize = 12;
    private $fontFamily = 'Arial';
    private $fontStyle = '';
    private $textColor = [0, 0, 0];
    private $fillColor = [255, 255, 255];
    
    public function addPage() {
        $this->currentPage++;
        $this->pages[$this->currentPage] = [];
        $this->y = 10;
        $this->x = 10;
    }
    
    public function setFont($family, $style = '', $size = 12) {
        $this->fontFamily = $family;
        $this->fontStyle = $style;
        $this->fontSize = $size;
    }
    
    public function setFillColor($r, $g, $b) {
        $this->fillColor = [$r, $g, $b];
    }
    
    public function setTextColor($r, $g, $b) {
        $this->textColor = [$r, $g, $b];
    }
    
    public function cell($width, $height, $text = '', $border = 0, $ln = 0, $align = 'L', $fill = false) {
        $this->pages[$this->currentPage][] = [
            'type' => 'cell',
            'x' => $this->x,
            'y' => $this->y,
            'width' => $width,
            'height' => $height,
            'text' => $text,
            'border' => $border,
            'align' => $align,
            'fill' => $fill,
            'fontSize' => $this->fontSize,
            'fontStyle' => $this->fontStyle,
            'textColor' => $this->textColor,
            'fillColor' => $this->fillColor
        ];
        
        if ($ln == 0) {
            $this->x += $width;
        } else {
            $this->x = 10;
            $this->y += $height;
        }
    }
    
    public function ln($height = 5) {
        $this->x = 10;
        $this->y += $height;
    }
    
    public function output($mode = 'I', $filename = '') {
        $html = $this->generateHTML();
        
        if ($mode == 'D') {
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . str_replace('.pdf', '.html', $filename) . '"');
            echo $html;
        } else {
            echo $html;
        }
    }
    
    private function generateHTML() {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Laporan</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; background: white; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin: 10px 0; }';
        $html .= 'td { border: 1px solid #000; padding: 5px; font-size: 11px; }';
        $html .= 'th { border: 1px solid #000; padding: 5px; background: #ccc; font-weight: bold; }';
        $html .= '@media print { body { margin: 0; padding: 10mm; } }';
        $html .= '</style></head><body>';
        
        $html .= '<table>';
        foreach ($this->pages as $page) {
            foreach ($page as $element) {
                if ($element['type'] == 'cell') {
                    $style = 'border: 1px solid #000; padding: 5px;';
                    if ($element['fill']) {
                        $c = $element['fillColor'];
                        $style .= 'background-color: rgb(' . $c[0] . ',' . $c[1] . ',' . $c[2] . ');';
                    }
                    $tc = $element['textColor'];
                    $style .= 'color: rgb(' . $tc[0] . ',' . $tc[1] . ',' . $tc[2] . ');';
                    $style .= 'text-align: ' . strtolower($element['align']) . ';';
                    if (strpos($element['fontStyle'], 'B') !== false) $style .= 'font-weight: bold;';
                    if (strpos($element['fontStyle'], 'I') !== false) $style .= 'font-style: italic;';
                    
                    $html .= '<td style="' . $style . '" width="' . ($element['width'] * 2) . 'px">' . htmlspecialchars($element['text']) . '</td>';
                }
            }
        }
        $html .= '</table>';
        $html .= '</body></html>';
        
        return $html;
    }
}
