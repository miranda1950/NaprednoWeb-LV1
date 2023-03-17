<?php

// stvaranje interfacea iRadovi  koji ima 3 funkcije...create, save, read..create funkcija prima parametar data.
interface iRadovi {
    public function create($data);
    public function save();
    public function read();
}

//klasa DiplomskiRadovi implementira interface iRadovi te isto tako onda mora imati i 3 metode koje se nalaze u interfaceu
class DiplomskiRadovi implements iRadovi {
    private $id = NULL;
    private $naziv_rada = NULL;
   private $tekst_rada = NULL;
    private $link_rada= NULL;
    private $oib_tvrtke = NULL;

    // iznad vidimo inicijalizaciju privatnih varijabli koje su tražene u zadatku


    // konstruktor klase u kojem postavljamo parametre na parametre data
function __construct($data) {
    $this->id = uniqid();
    $this->naziv_rada = $data['naziv_rada'];
    $this->tekst_rada = $data['tekst_rada'];
    $this->link_rada = $data['link_rada'];
    $this->oib_tvrtke = $data['oib_tvrtke'];
}

//prva funkcija iz inerfacea - create u kojoj pozivamo funkciju construct  i predajemo parametar data
     function create($data)
    {
    self::__construct($data);
    }

    //funkcija koja vraća polje parametara
    function readDiplRadData() {
        return array('id' => $this->id, 'naziv_rada' => $this->naziv_rada, 'tekst_rada' => $this->tekst_rada, 'link_rada' => $this->link_rada, 'oib_tvrtke' => $this->oib_tvrtke);
    }

    // druga funkcija iz interfacea- read u kojoj se povezujemo sa MySQL bazom diplradovi koja je napravljena lokalno.
    function read()
    {
        $connection = mysqli_connect('localhost','miran','miran','diplradovi');
        // provjerava konekciju te ukoliko nije uspješna, ispisuje se error
        if(!$connection){
            echo 'Connection error: ' . mysqli_connect_error();
        }

        // u parametar spremamo sve podatke iz tablice diplomski
        $sql = "SELECT * FROM diplomski";

        //funckija mysqli_query obavlja query na traženu bazu
        $result = mysqli_query($connection, $sql);
        //fubkcija mysqli_fetch_all - Fetch all rows and return the result-set as an associative array
        $dipl_radovi = mysqli_fetch_all($result, MYSQLI_ASSOC);

        mysqli_close($connection);

        //Prints human-readable information about a variable - print_r
        print_r($dipl_radovi);


    }
    //3. funkcija iz interfacea - save u kojem isto se spajamo na bazu diplradovi
     function save()
    {
        $connection = mysqli_connect('localhost','miran','miran', 'diplradovi');
        if(!$connection){
            echo 'Connection error: ' . mysqli_connect_error();
        }
        // postavljamo parametre na parametre klase
        $id = $this->id;
        $naziv = $this->naziv_rada;
        $tekst = $this->tekst_rada;
        $link = $this->link_rada;
        $oib = $this->oib_tvrtke;

        // ubacujemo te podatke koje smo dobili u tablicu diplomski
        $sql = "INSERT INTO diplomski (`id`, `naziv_rada`, `tekst_rada`, `link_rada`, `oib_tvrtke`) VALUES ('$id', '$naziv', '$tekst','$link', '$oib')";
        mysqli_query($connection, $sql);
        mysqli_close($connection);

    }


}


$redni_broj = 2;
//curl_init()-initializes a new session and return a cURL handle for use
$ch = curl_init();
//curl_setopt- Set an option for a cURL transfer
curl_setopt($ch, CURLOPT_URL,"https://stup.ferit.hr/index.php/zavrsni-radovi/page/$redni_broj");
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
//curl_exec- obavlja cURL sesiju
$response = curl_exec($ch);
//provjera sesije
if(curl_errno($ch)) {
    echo 'Greška: ' . curl_error($ch);
}
curl_close($ch);
//injecttanje simple_html_dom.php parsera koji je skinut i stavljen u folder
require_once('simple_html_dom.php');

//dom dokument - reprezentira HTML ili XML dokument
$dom = new DOMDocument();

//ucitava html dokument kao string
@ $dom->loadHTML($response);

//stvara novi DOMXpath objekt -
$xpath = new DOMXpath($dom);

//pretrazuje dokument i sprema u parametre koje sam postavio one podatke koji se nalae u expressionu
$allHeaders = $xpath->query("//h2[contains(@class,'blog-shortcode-post-title')]");
$allLinks = $xpath->query("//h2[contains(@class,'blog-shortcode-post-title')]/a");
$allOibs= $xpath->query("//article[contains(@class,'fusion-post-medium')]//img");

//parametar count koji je velicine broja koliko ima headera
$count = $allHeaders->length;

//stvaranje praznih arraya za kasniije upotrebu
$title_array = array();
$tekst_array = array();
$links_array = array();
$oibs_array = array();

//ide po svakom itemu u allheadersu te vraca u title_text kontente texta te sprema ih u title_array koji smo napravili prije
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

//funckija ide po svim oibima te sa getattribute vraća value podataka sa nasttavkom src
foreach($allOibs as $oib){
    $src = $oib->getAttribute("src");
    //basename — Returns trailing name component of path

    $filename = basename($src);
    //pathinfo — Returns information about a file path
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $oib_without_extension = pathinfo($filename, PATHINFO_FILENAME);

    $oibs_array[] = $oib_without_extension;
}
//for petlja ide od 0 do varijable  count i stvara novi rad
for($i = 0; $i < $count; $i++) {
    $rad = array(
        'naziv_rada' => $title_array[$i],
        'tekst_rada' => $tekst_array[$i],
        'link_rada' => $links_array[$i],
        'oib_tvrtke' => $oibs_array[$i]
    );
    //stvaranje novog rada koji je tip DiplomskiRadovi
    $novi_rad = new DiplomskiRadovi($rad);


    $info_rad = $novi_rad->readDiplRadData();

    echo "<p>ID: {$info_rad['id']}.</p>";
    echo "<p>NAZIV RADA: {$info_rad['naziv_rada']}.</p>";
    echo "<p>TEKST RADA: {$info_rad['tekst_rada']}.</p>";
    echo "<p>LINK RADA: {$info_rad['link_rada']}.</p>";
    echo "<p>OIB TVRTKE: {$info_rad['oib_tvrtke']}.</p>";
    //spremanje rada u bazu
    $novi_rad->save();

}

//html dokumentt
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