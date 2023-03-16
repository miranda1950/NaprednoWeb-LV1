<?php


interface iRadovi {
    public function create($data);
    public function save();
    public function read();
}
class DiplomskiRadovi implements iRadovi {
    private $id = NULL;
    private $naziv_rada = NULL;
   private $tekst_rada = NULL;
    private $link_rada= NULL;
    private $oib_tvrtke = NULL;

function __construct($data) {
    $this->id = uniqid();
    $this->naziv_rada = $data['naziv_rada'];
    $this->tekst_rada = $data['tekst_rada'];
    $this->link_rada = $data['link_rada'];
    $this->oib_tvrtke = $data['oib_tvrtke'];
}
     function create($data)
    {
    self::__construct($data);
    }

    function readDiplRadData() {
        return array('id' => $this->id, 'naziv_rada' => $this->naziv_rada, 'tekst_rada' => $this->tekst_rada, 'link_rada' => $this->link_rada, 'oib_tvrtke' => $this->oib_tvrtke);
    }

    function read()
    {
        $connection = mysqli_connect('localhost','miran','miran','diplradovi');
        if(!$connection){
            echo 'Connection error: ' . mysqli_connect_error();
        }
        $sql = "SELECT * FROM diplomski";


        $result = mysqli_query($connection, $sql);

        $dipl_radovi = mysqli_fetch_all($result, MYSQLI_ASSOC);

        mysqli_close($connection);
        print_r($dipl_radovi);


    }
     function save()
    {
        $connection = mysqli_connect('localhost','miran','miran', 'diplradovi');
        if(!$connection){
            echo 'Connection error: ' . mysqli_connect_error();
        }

        $id = $this->id;
        $naziv = $this->naziv_rada;
        $tekst = $this->tekst_rada;
        $link = $this->link_rada;
        $oib = $this->oib_tvrtke;


        $sql = "INSERT INTO diplomski (`id`, `naziv_rada`, `tekst_rada`, `link_rada`, `oib_tvrtke`) VALUES ('$id', '$naziv', '$tekst','$link', '$oib')";
        mysqli_query($connection, $sql);
        mysqli_close($connection);

    }


}


$redni_broj = 2;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"https://stup.ferit.hr/index.php/zavrsni-radovi/page/$redni_broj");
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
$response = curl_exec($ch);
if(curl_errno($ch)) {
    echo 'Greška: ' . curl_error($ch);
}
curl_close($ch);

require_once('simple_html_dom.php');

$dom = new DOMDocument();
@ $dom->loadHTML($response);

$xpath = new DOMXpath($dom);

$allHeaders = $xpath->query("//h2[contains(@class,'blog-shortcode-post-title')]");
$allLinks = $xpath->query("//h2[contains(@class,'blog-shortcode-post-title')]/a");
$allOibs= $xpath->query("//article[contains(@class,'fusion-post-medium')]//img");

$count = $allHeaders->length;

$title_array = array();
$tekst_array = array();
$links_array = array();
$oibs_array = array();

foreach($allHeaders as $headers){
    $title_text = $headers->textContent;
    $title_array[] = $title_text;
}

foreach($allLinks as $link){
    $href = $link->getAttribute("href");
    $links_array[] = $href;


    $chTekst = curl_init();
    curl_setopt($chTekst, CURLOPT_URL, $href);
    curl_setopt($chTekst, CURLOPT_RETURNTRANSFER, true);

    $htmlTekst = curl_exec($chTekst);

    $domTekst = new DOMDocument();
    @ $domTekst->loadHTML($htmlTekst);

    $tekst = '';

    $paragraphs = $domTekst->getElementsByTagName('p');
    foreach($paragraphs as $paragraph){
        $tekst .= $paragraph->textContent;
    }
    $tekst_array[] = $tekst;

}

foreach($allOibs as $oib){
    $src = $oib->getAttribute("src");
    $filename = basename($src);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $oib_without_extension = pathinfo($filename, PATHINFO_FILENAME);

    $oibs_array[] = $oib_without_extension;
}

for($i = 0; $i < $count; $i++) {
    $rad = array(
        'naziv_rada' => $title_array[$i],
        'tekst_rada' => $tekst_array[$i],
        'link_rada' => $links_array[$i],
        'oib_tvrtke' => $oibs_array[$i]
    );
    $novi_rad = new DiplomskiRadovi($rad);

    $info_rad = $novi_rad->readDiplRadData();

    echo "<p>ID: {$info_rad['id']}.</p>";
    echo "<p>NAZIV RADA: {$info_rad['naziv_rada']}.</p>";
    echo "<p>TEKST RADA: {$info_rad['tekst_rada']}.</p>";
    echo "<p>LINK RADA: {$info_rad['link_rada']}.</p>";
    echo "<p>OIB TVRTKE: {$info_rad['oib_tvrtke']}.</p>";
    $novi_rad->save();

}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Napredno Web Programiranje-LV1</title>
</head>
<body>
<h3>
    <?php
    echo "<br><br>" . "Ispis tablice radova:" . "<br>";
    $novi_rad->read();
    ?>
</h3>
</body>
</html>