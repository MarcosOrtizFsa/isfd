<?php
declare(strict_types=1);
$id=max(1,min(5,(int)($_GET['id']??1)));
$symbols=[1=>'Aa',2=>'&lt;/&gt;',3=>'↔',4=>'↗',5=>'IA'];
$colors=[1=>'#993333',2=>'#286866',3=>'#334f7d',4=>'#926b20',5=>'#765096'];
header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');
$color=$colors[$id];
echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 180"><circle cx="120" cy="90" r="79" fill="'.$color.'" opacity=".08"/><circle cx="190" cy="40" r="10" fill="#e5b92f"/><path d="M35 120h25M47 108v24" stroke="'.$color.'" opacity=".4" stroke-width="3"/><rect x="73" y="65" width="94" height="86" rx="28" fill="'.$color.'"/>';
if ($id===1) echo '<path d="M78 83L70 35l42 28M162 83l8-48-42 28" fill="'.$color.'"/><circle cx="98" cy="89" r="22" fill="#fff"/><circle cx="142" cy="89" r="22" fill="#fff"/><circle cx="99" cy="90" r="8" fill="'.$color.'"/><circle cx="141" cy="90" r="8" fill="'.$color.'"/><path d="M110 105l10 15 10-15" fill="#e5b92f"/>';
else {
    echo '<rect x="81" y="73" width="78" height="34" rx="14" fill="#fff"/><circle cx="100" cy="90" r="7" fill="'.$color.'"/><circle cx="140" cy="90" r="7" fill="'.$color.'"/>';
    if ($id===2) echo '<path d="M120 38v22M106 38h28M68 99H53v24M172 99h15v24" fill="none" stroke="'.$color.'" stroke-width="6" stroke-linecap="round"/>';
    if ($id===3) echo '<path d="M85 58q35-35 70 0M98 58q22-20 44 0" fill="none" stroke="'.$color.'" stroke-width="5" stroke-linecap="round"/><rect x="43" y="83" width="23" height="59" rx="6" fill="'.$color.'"/><rect x="174" y="83" width="23" height="59" rx="6" fill="'.$color.'"/><path d="M48 95h13m-13 12h13m118-12h13m-13 12h13" stroke="#fff" stroke-width="3"/>';
    if ($id===4) echo '<path d="M79 65h82l-10-20H90z" fill="'.$color.'"/><path d="M90 91h20m20 0h20m-40-1h20" stroke="'.$color.'" stroke-width="3"/><circle cx="100" cy="90" r="13" fill="none" stroke="'.$color.'" stroke-width="2"/><circle cx="140" cy="90" r="13" fill="none" stroke="'.$color.'" stroke-width="2"/><rect x="168" y="119" width="34" height="28" rx="4" fill="#e5b92f"/><path d="M178 119v-7h14v7" fill="none" stroke="'.$color.'" stroke-width="3"/>';
    if ($id===5) echo '<path d="M99 51l21-17 21 17-21 12z" fill="none" stroke="'.$color.'" stroke-width="3"/><circle cx="99" cy="51" r="6" fill="#e5b92f"/><circle cx="120" cy="34" r="6" fill="'.$color.'"/><circle cx="141" cy="51" r="6" fill="#e5b92f"/><path d="M66 92l-14 13 14 13m108-26 14 13-14 13" fill="none" stroke="'.$color.'" stroke-width="5" stroke-linecap="round"/>';
}
echo '<rect x="92" y="123" width="56" height="27" rx="8" fill="#fff"/><text x="120" y="142" text-anchor="middle" font-family="sans-serif" font-weight="bold" font-size="18" fill="'.$color.'">'.$symbols[$id].'</text></svg>';
