<?php
declare(strict_types=1);
// SVG propios, sin archivos subidos ni contenido controlado por el visitante.
$id=max(1,min(512,(int)($_GET['id']??1)))-1;
$colors=['#993333','#286866','#334f7d','#765096','#9b6431','#467346','#8a4565','#405769'];
$color=$colors[$id%8]; $shape=(int)floor($id/8)%8; $accent=(int)floor($id/64)%8;
$marks=['✦','+','○','△','◇','≡','×','•'];
header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');
echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120"><rect width="120" height="120" rx="30" fill="'.$color.'"/><circle cx="95" cy="16" r="40" fill="#fff" opacity=".08"/><path d="M60 22v12M51 22h18" stroke="#f0c64a" stroke-width="5" stroke-linecap="round"/><rect x="25" y="35" width="70" height="60" rx="'.(8+$shape*3).'" fill="#f7f0e6"/><rect x="35" y="48" width="50" height="25" rx="10" fill="'.$colors[$accent].'"/><circle cx="47" cy="60" r="5" fill="white"/><circle cx="73" cy="60" r="5" fill="white"/><path d="M49 82q11 8 22 0" stroke="'.$color.'" fill="none" stroke-width="3" stroke-linecap="round"/><text x="104" y="108" text-anchor="middle" font-family="sans-serif" font-size="22" fill="#fff">'.$marks[$shape].'</text><text x="12" y="111" font-family="sans-serif" font-size="11" fill="#fff">'.($id+1).'</text></svg>';
