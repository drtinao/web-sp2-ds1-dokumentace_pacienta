<?php
/**
 * Tento skript resi obecne strankovani.
 */

// echo "strankovani - page: $page_number, count: $count, total: $total";


// pocet stranek
$pages_number = ceil($total / $count);

echo "<div>";
echo "<ul class=\"pagination\">";


// url predchozi
if ($page_number > 1) {
    $predchozi_cislo = $page_number-1;
    $route_params["page_number"] = $predchozi_cislo;
    $url_predchozi = $this->makeUrlByRoute($route, $route_params);
}
else
    $url_predchozi = "";

// url dalsi
if ($page_number < $pages_number) {
    $dalsi_cislo = $page_number + 1;
    $route_params["page_number"] = $dalsi_cislo;
    $url_dalsi = $this->makeUrlByRoute($route, $route_params);
}
else
    $url_dalsi = "";

// predchozi
if (trim($url_predchozi) != "")
    echo "<li class='page-item'><a href=\"$url_predchozi\" class=\"page-link\">&lt;</a></li>";


// vypsat cisla mezi
for ($i = 1; $i <= $pages_number; $i++) {

    $active_pom = "";
    if ($i == $page_number) $active_pom = "class=\"page-item active\"";

    // url takto to u novinek nejde, nemam tam v route primo en
    $route_params["page_number"] = $i;
    //printr($route_params);
    $url_aktualni = $this->makeUrlByRoute($route, $route_params);

    // vypis jen pokud jsem blizko aktualnimu nebo prvni nebo posledni
    // prvni a posledni vyhodim $i == 1 || || $i == $pages_number
    if (abs($i - $page_number) < 2) {
        echo "<li $active_pom><a href=\"$url_aktualni\" class=\"page-link\">$i</a></li>";
    }
}

// dalsi
if (trim($url_dalsi) != "")
    echo "<li class='page-item'><a href=\"$url_dalsi\" class=\"page-link\">&gt;</a></li>";

echo "</ul>";

echo "</div><br/><br/>";



