<?php
function smarty_function_html_pager($rP, &$smarty)
{
    $_X = '{X}';
    $_D = '...';
    $_G = ' ';
    $_R = 2;
    $nPageMax = max(1, (int)$rP['max']);
    $nPage = min($nPageMax, max(1, (int)$rP['page']));
    $sHtmlLink = $rP['link'] ? $rP['link'] : $_X;
    $sHtmlText = $rP['text'] ? $rP['text'] : $_X;
    $nHead = max(1, (int)$rP['head']);
    $nTail = max(1, (int)$rP['tail']);
    $sHtmlDash = $rP['dash'] ? $rP['dash'] : $_D;
    $sGap = $rP['gap'] ? $rP['gap'] : $_G;
    $nRange = 0 < $rP['range'] ? (int)$rP['range'] : $_R;

    $sHtml = '';
    $sLast = false;
    for ($n = 1; $n <= $nPageMax; $n++) {
        if ($sLast) {
            $sHtml .= $sGap;
        }
        /// find the mode ///
        if ($n <= $nHead) {
            if ($n == $nPage) {
                $sHtml .= str_replace($_X, $n, $sHtmlText);
            }
            else {
                $sHtml .= str_replace($_X, $n, $sHtmlLink);
            }
            $sLast = 'HEAD';
        }
        elseif ($n >= $nPageMax - $nTail + 1) {
            if ($n == $nPage) {
                $sHtml .= str_replace($_X, $n, $sHtmlText);
            }
            else {
                $sHtml .= str_replace($_X, $n, $sHtmlLink);
            }
            $sLast = 'TAIL';
        }
        elseif ($nPage - $nRange <= $n && $n <= $nPage + $nRange) {
            if ($n == $nPage) {
                $sHtml .= str_replace($_X, $n, $sHtmlText);
            }
            else {
                $sHtml .= str_replace($_X, $n, $sHtmlLink);
            }
            $sLast = 'RANGE';
        }
        else {
            if ('DASH' != $sLast) {
                $sHtml .= $sHtmlDash;
            }
            $sLast = 'DASH';
        }
    }
    return $sHtml;
}
?>
